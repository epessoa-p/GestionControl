<?php

namespace App\Http\Controllers;

use App\Models\Machinery;
use App\Models\OverheadAllocation;
use App\Models\OverheadItem;
use App\Models\OverheadPeriod;
use App\Models\Production;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Controlador de Períodos de Gastos Indirectos (Overhead)
 *
 * Gestiona los períodos que agrupan los costos indirectos de producción:
 * servicios, mano de obra, transporte y depreciación de maquinaria.
 * Incluye funcionalidades para auto-cargar depreciaciones y cerrar períodos.
 */
class OverheadPeriodController extends Controller
{
    /**
     * Lista los períodos de overhead de la empresa activa con filtro por estado.
     */
    public function index(Request $request)
    {
        $companyId    = $this->getCompanyId();
        $activeStatus = $request->get('status', 'abierto');

        $base = fn() => OverheadPeriod::query()
            ->when($companyId, fn($q) => $q->where('company_id', $companyId));

        $periods = $base()
            ->where('status', $activeStatus)
            ->with(['createdBy', 'items', 'allocations'])
            ->latest('period_start')
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'abierto'  => $base()->where('status', 'abierto')->count(),
            'cerrado'  => $base()->where('status', 'cerrado')->count(),
        ];

        return view('overhead-periods.index', compact('periods', 'activeStatus', 'counts'));
    }

    /**
     * Muestra el formulario para crear un nuevo período de overhead.
     */
    public function create()
    {
        return view('overhead-periods.create', [
            'period' => null,
            'action' => route('overhead-periods.store'),
            'method' => 'POST',
        ]);
    }

    /**
     * Almacena un nuevo período de overhead con sus ítems iniciales.
     * Usa transacción para garantizar consistencia entre período e ítems.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'         => 'required|string|max:100',
                'period_start' => 'required|date',
                'period_end'   => 'required|date|after_or_equal:period_start',
                'items'        => 'nullable|array',
                'items.*.concept'  => 'required_with:items|string|max:255',
                'items.*.category' => 'required_with:items|in:' . implode(',', OverheadItem::CATEGORIES),
                'items.*.amount'   => 'required_with:items|numeric|min:0',
            ]);

            $companyId = $this->getCompanyId();

            DB::transaction(function () use ($validated, $companyId) {
                $period = OverheadPeriod::create([
                    'company_id'   => $companyId,
                    'name'         => $validated['name'],
                    'period_start' => $validated['period_start'],
                    'period_end'   => $validated['period_end'],
                    'status'       => 'abierto',
                    'total_amount' => 0,
                    'created_by'   => auth()->id(),
                ]);

                foreach ($validated['items'] ?? [] as $item) {
                    OverheadItem::create([
                        'overhead_period_id' => $period->id,
                        'concept'            => $item['concept'],
                        'category'           => $item['category'],
                        'amount'             => $item['amount'],
                    ]);
                }

                $period->recalculateTotal();
            });

            return redirect()->route('overhead-periods.index')->with('success', 'Período de gastos registrado exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Error al crear período overhead', ['user_id' => auth()->id(), 'input' => $request->except(['_token']), 'message' => $e->getMessage(), 'file' => $e->getFile() . ':' . $e->getLine()]);
            return back()->withInput()->with('error', 'No fue posible registrar el período.');
        }
    }

    /**
     * Muestra el detalle de un período: KPIs, ítems agrupados por categoría y asignaciones.
     */
    public function show(OverheadPeriod $period)
    {
        $this->authorizeRecord($period);
        $period->load(['items.machinery', 'allocations.production', 'createdBy']);

        // Agrupar ítems por categoría para la vista
        $itemsByCategory = $period->items->groupBy('category');

        // Producciones del período para mostrar en KPI
        $productionsCount = Production::where('company_id', $period->company_id)
            ->whereBetween('production_date', [$period->period_start, $period->period_end])
            ->count();

        return view('overhead-periods.show', compact('period', 'itemsByCategory', 'productionsCount'));
    }

    /**
     * Muestra el formulario de edición. Solo disponible si el período está abierto.
     */
    public function edit(OverheadPeriod $period)
    {
        $this->authorizeRecord($period);

        if ($period->status === 'cerrado') {
            return back()->with('error', 'No se puede editar un período cerrado.');
        }

        $period->load('items');

        return view('overhead-periods.edit', [
            'period' => $period,
            'action' => route('overhead-periods.update', $period),
            'method' => 'PUT',
        ]);
    }

    /**
     * Actualiza el período y sincroniza sus ítems (elimina anteriores + crea nuevos).
     */
    public function update(Request $request, OverheadPeriod $period)
    {
        $this->authorizeRecord($period);

        if ($period->status === 'cerrado') {
            return back()->with('error', 'No se puede editar un período cerrado.');
        }

        try {
            $validated = $request->validate([
                'name'         => 'required|string|max:100',
                'period_start' => 'required|date',
                'period_end'   => 'required|date|after_or_equal:period_start',
                'items'        => 'nullable|array',
                'items.*.concept'  => 'required_with:items|string|max:255',
                'items.*.category' => 'required_with:items|in:' . implode(',', OverheadItem::CATEGORIES),
                'items.*.amount'   => 'required_with:items|numeric|min:0',
            ]);

            DB::transaction(function () use ($validated, $period) {
                $period->update([
                    'name'         => $validated['name'],
                    'period_start' => $validated['period_start'],
                    'period_end'   => $validated['period_end'],
                ]);

                // Sincronizar ítems: eliminar y recrear
                $period->items()->delete();

                foreach ($validated['items'] ?? [] as $item) {
                    OverheadItem::create([
                        'overhead_period_id' => $period->id,
                        'concept'            => $item['concept'],
                        'category'           => $item['category'],
                        'amount'             => $item['amount'],
                    ]);
                }

                $period->recalculateTotal();
            });

            return redirect()->route('overhead-periods.show', $period)->with('success', 'Período actualizado exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Error al actualizar período overhead', ['user_id' => auth()->id(), 'period_id' => $period->id, 'message' => $e->getMessage(), 'file' => $e->getFile() . ':' . $e->getLine()]);
            return back()->withInput()->with('error', 'No fue posible actualizar el período.');
        }
    }

    /**
     * Elimina (soft delete) un período. No se permite si ya tiene asignaciones.
     */
    public function destroy(OverheadPeriod $period)
    {
        $this->authorizeRecord($period);

        if ($period->allocations()->count() > 0) {
            return back()->with('error', 'No se puede eliminar un período con asignaciones registradas.');
        }

        try {
            $period->items()->delete();
            $period->delete();
            return redirect()->route('overhead-periods.index')->with('success', 'Período eliminado exitosamente.');
        } catch (\Throwable $e) {
            Log::error('Error al eliminar período overhead', ['user_id' => auth()->id(), 'period_id' => $period->id, 'message' => $e->getMessage(), 'file' => $e->getFile() . ':' . $e->getLine()]);
            return back()->with('error', 'No fue posible eliminar el período.');
        }
    }

    /**
     * Cierra el período: congela el total y bloquea ediciones futuras.
     * Solo se puede cerrar un período en estado 'abierto'.
     */
    public function close(OverheadPeriod $period)
    {
        $this->authorizeRecord($period);

        if ($period->status === 'cerrado') {
            return back()->with('error', 'El período ya está cerrado.');
        }

        try {
            $period->recalculateTotal();
            $period->update(['status' => 'cerrado']);
            return back()->with('success', 'Período cerrado exitosamente. El total ha quedado congelado.');
        } catch (\Throwable $e) {
            Log::error('Error al cerrar período overhead', ['user_id' => auth()->id(), 'period_id' => $period->id, 'message' => $e->getMessage(), 'file' => $e->getFile() . ':' . $e->getLine()]);
            return back()->with('error', 'No fue posible cerrar el período.');
        }
    }

    /**
     * Auto-carga depreciaciones de todas las máquinas activas de la empresa como ítems del período.
     * Crea un ítem por máquina con la depreciación mensual calculada.
     * No crea duplicados: omite máquinas que ya tienen ítem de depreciación en el período.
     */
    public function autoFillDepreciation(OverheadPeriod $period)
    {
        $this->authorizeRecord($period);

        if ($period->status === 'cerrado') {
            return back()->with('error', 'No se puede modificar un período cerrado.');
        }

        try {
            $machines = Machinery::where('company_id', $period->company_id)
                ->where('active', true)
                ->get();

            // IDs de máquinas que ya tienen depreciación en este período
            $existing = $period->items()
                ->whereNotNull('machinery_id')
                ->pluck('machinery_id')
                ->toArray();

            $added = 0;
            foreach ($machines as $machine) {
                if (in_array($machine->id, $existing)) {
                    continue;
                }
                OverheadItem::create([
                    'overhead_period_id' => $period->id,
                    'machinery_id'       => $machine->id,
                    'concept'            => "Depreciación: {$machine->name}",
                    'category'           => 'depreciacion',
                    'amount'             => $machine->monthlyDepreciation(),
                ]);
                $added++;
            }

            $period->recalculateTotal();

            $msg = $added > 0
                ? "{$added} depreciación(es) agregada(s) automáticamente."
                : 'Todas las máquinas activas ya tienen depreciación registrada en este período.';

            return back()->with('success', $msg);
        } catch (\Throwable $e) {
            Log::error('Error al auto-cargar depreciaciones', ['user_id' => auth()->id(), 'period_id' => $period->id, 'message' => $e->getMessage(), 'file' => $e->getFile() . ':' . $e->getLine()]);
            return back()->with('error', 'No fue posible cargar las depreciaciones.');
        }
    }

    // ─── Métodos privados ─────────────────────────────────────────

    /**
     * Obtiene el ID de la empresa activa según el tipo de usuario.
     */
    private function getCompanyId(): ?int
    {
        $user = auth()->user();
        return $user->is_super_admin
            ? ($user->getCurrentCompany()?->id ?? request('company_id'))
            : $user->getCurrentCompany()?->id;
    }

    /**
     * Verifica que el período pertenece a la empresa activa del usuario.
     * Lanza 403 si no coincide para evitar acceso entre empresas.
     */
    private function authorizeRecord(OverheadPeriod $period): void
    {
        if (!auth()->user()->is_super_admin && $period->company_id !== auth()->user()->getCurrentCompany()?->id) {
            abort(403);
        }
    }
}
