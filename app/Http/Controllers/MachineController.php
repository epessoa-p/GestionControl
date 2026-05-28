<?php

namespace App\Http\Controllers;

use App\Models\Machinery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Controlador de Maquinaria y Activos Productivos
 *
 * Gestiona el CRUD completo de maquinaria. Cada máquina tiene un costo
 * y una vida útil en meses que permite calcular la depreciación mensual
 * automáticamente para incluirla en los períodos de gastos indirectos.
 */
class MachineController extends Controller
{
    /**
     * Lista la maquinaria de la empresa activa con filtro por estado activo/inactivo.
     */
    public function index(Request $request)
    {
        $companyId = $this->getCompanyId();
        $activeFilter = $request->get('active', '1');

        $base = fn() => Machinery::query()
            ->when($companyId, fn($q) => $q->where('company_id', $companyId));

        $machines = $base()
            ->where('active', (bool) $activeFilter)
            ->with('createdBy')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'activas'   => $base()->where('active', true)->count(),
            'inactivas' => $base()->where('active', false)->count(),
        ];

        return view('machinery.index', compact('machines', 'activeFilter', 'counts'));
    }

    /**
     * Muestra el formulario para registrar una nueva máquina.
     */
    public function create()
    {
        return view('machinery.create', [
            'machine' => null,
            'action'  => route('machinery.store'),
            'method'  => 'POST',
        ]);
    }

    /**
     * Almacena una nueva máquina en la base de datos.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'               => 'required|string|max:255',
                'description'        => 'nullable|string',
                'cost'               => 'required|numeric|min:0',
                'useful_life_months' => 'required|integer|min:1',
                'purchase_date'      => 'required|date',
                'active'             => 'boolean',
            ]);

            $companyId = $this->getCompanyId();

            Machinery::create([
                ...$validated,
                'company_id' => $companyId,
                'active'     => $request->boolean('active', true),
                'created_by' => auth()->id(),
            ]);

            return redirect()->route('machinery.index')->with('success', 'Maquinaria registrada exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Error al crear maquinaria', ['user_id' => auth()->id(), 'message' => $e->getMessage(), 'file' => $e->getFile() . ':' . $e->getLine()]);
            return back()->withInput()->with('error', 'No fue posible registrar la maquinaria.');
        }
    }

    /**
     * Muestra el detalle de una máquina: KPIs y tabla de depreciación proyectada.
     */
    public function show(Machinery $machine)
    {
        $this->authorizeRecord($machine);
        return view('machinery.show', compact('machine'));
    }

    /**
     * Muestra el formulario de edición de una máquina.
     */
    public function edit(Machinery $machine)
    {
        $this->authorizeRecord($machine);
        return view('machinery.edit', [
            'machine' => $machine,
            'action'  => route('machinery.update', $machine),
            'method'  => 'PUT',
        ]);
    }

    /**
     * Actualiza los datos de una máquina existente.
     */
    public function update(Request $request, Machinery $machine)
    {
        $this->authorizeRecord($machine);

        try {
            $validated = $request->validate([
                'name'               => 'required|string|max:255',
                'description'        => 'nullable|string',
                'cost'               => 'required|numeric|min:0',
                'useful_life_months' => 'required|integer|min:1',
                'purchase_date'      => 'required|date',
                'active'             => 'boolean',
            ]);

            $machine->update([
                ...$validated,
                'active' => $request->boolean('active', true),
            ]);

            return redirect()->route('machinery.show', $machine)->with('success', 'Maquinaria actualizada exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Error al actualizar maquinaria', ['user_id' => auth()->id(), 'machine_id' => $machine->id, 'message' => $e->getMessage(), 'file' => $e->getFile() . ':' . $e->getLine()]);
            return back()->withInput()->with('error', 'No fue posible actualizar la maquinaria.');
        }
    }

    /**
     * Elimina (soft delete) una máquina.
     */
    public function destroy(Machinery $machine)
    {
        $this->authorizeRecord($machine);

        try {
            $machine->delete();
            return redirect()->route('machinery.index')->with('success', 'Maquinaria eliminada exitosamente.');
        } catch (\Throwable $e) {
            Log::error('Error al eliminar maquinaria', ['user_id' => auth()->id(), 'machine_id' => $machine->id, 'message' => $e->getMessage(), 'file' => $e->getFile() . ':' . $e->getLine()]);
            return back()->with('error', 'No fue posible eliminar la maquinaria.');
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
     * Verifica que la maquinaria pertenece a la empresa activa del usuario.
     * Lanza 403 si no coincide para evitar acceso entre empresas.
     */
    private function authorizeRecord(Machinery $machine): void
    {
        if (!auth()->user()->is_super_admin && $machine->company_id !== auth()->user()->getCurrentCompany()?->id) {
            abort(403);
        }
    }
}
