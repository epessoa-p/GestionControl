<?php

namespace App\Http\Controllers;

use App\Models\OverheadAllocation;
use App\Models\OverheadPeriod;
use App\Models\Product;
use App\Models\Production;
use App\Models\Recipe;
use App\Models\ProductionCost;
use App\Models\ProductionMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductionController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Production::with(['product', 'createdBy'])->latest();

        if (!$user->is_super_admin) {
            $query->where('company_id', $user->getCurrentCompany()?->id);
        }

        if ($request->filled('status')) { $query->where('status', $request->status); }
        if ($request->filled('from')) { $query->whereDate('production_date', '>=', $request->from); }
        if ($request->filled('to')) { $query->whereDate('production_date', '<=', $request->to); }

        return view('productions.index', [
            'productions' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only(['status', 'from', 'to']),
        ]);
    }

    public function create()
    {
        $companyId = $this->getCompanyId();
        $user      = auth()->user();
        $company   = $user->getCurrentCompany();

        $overheadPeriod = OverheadPeriod::where('company_id', $companyId)
            ->where('status', 'abierto')
            ->latest('period_start')
            ->first();

        return view('productions.create', [
            'production'         => null,
            'products'           => Product::where('company_id', $companyId)->where('active', true)->where('category', 'PRODUCTO FINAL')->orderBy('name')->get(),
            'rawMaterials'       => Product::where('company_id', $companyId)->where('active', true)->where('category', 'MATERIA PRIMA')->orderBy('name')->get(),
            'recipes'            => Recipe::where('company_id', $companyId)->where('status', 'activa')->orderBy('name')->get(),
            'batchNumber'        => Production::generateBatchNumber($companyId),
            'action'             => route('productions.store'),
            'method'             => 'POST',
            'overheadPeriod'     => $overheadPeriod,
            'distributionMethod' => $company?->overhead_distribution_method ?? 'manual',
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'product_id'                => 'required|exists:products,id',
                'quantity_produced'         => 'required|numeric|min:0.01',
                'production_date'           => 'required|date',
                'notes'                     => 'nullable|string',
                'overhead_amount'           => 'nullable|numeric|min:0',
                'overhead_period_id'        => 'nullable|integer',
                'overhead_method'           => 'nullable|in:por_unidades,por_orden,tasa_fija,manual',
                'costs'                     => 'nullable|array',
                'costs.*.concept'           => 'nullable|string|max:255',
                'costs.*.type'              => 'nullable|in:direct,indirect',
                'costs.*.amount'            => 'nullable|numeric|min:0',
                'materials'                 => 'nullable|array',
                'materials.*.product_id'    => 'nullable|exists:products,id',
                'materials.*.quantity_used' => 'nullable|numeric|min:0.0001',
                'materials.*.unit_cost'     => 'nullable|numeric|min:0',
            ]);

            $companyId = $this->getCompanyId();

            // Filtrar filas vacías: ignorar filas donde el campo clave es null/vacío
            $costs = collect($validated['costs'] ?? [])
                ->filter(fn($c) => !empty($c['concept']))
                ->values()->all();

            $materials = collect($validated['materials'] ?? [])
                ->filter(fn($m) => !empty($m['product_id']))
                ->values()->all();

            DB::transaction(function () use ($validated, $companyId, $materials, $costs) {
                $production = Production::create([
                    'company_id'        => $companyId,
                    'product_id'        => $validated['product_id'],
                    'batch_number'      => Production::generateBatchNumber($companyId),
                    'quantity_produced' => $validated['quantity_produced'],
                    'production_date'   => $validated['production_date'],
                    'status'            => 'planned',
                    'notes'             => $validated['notes'] ?? null,
                    'created_by'        => auth()->id(),
                ]);

                foreach ($costs as $cost) {
                    ProductionCost::create(['production_id' => $production->id, ...$cost]);
                }

                foreach ($materials as $material) {
                    ProductionMaterial::create([
                        'production_id' => $production->id,
                        'product_id'    => $material['product_id'],
                        'quantity_used' => $material['quantity_used'],
                        'unit_cost'     => $material['unit_cost'],
                        'total_cost'    => $material['quantity_used'] * $material['unit_cost'],
                    ]);
                }

                // Registrar asignación de overhead si se indicó un monto > 0
                $overheadAmount = (float) ($validated['overhead_amount'] ?? 0);
                if ($overheadAmount > 0) {
                    OverheadAllocation::create([
                        'production_id'      => $production->id,
                        'overhead_period_id' => $validated['overhead_period_id'] ?: null,
                        'amount'             => $overheadAmount,
                        'method'             => $validated['overhead_method'] ?? 'manual',
                    ]);
                }

                $production->recalculateTotalCost();
            });

            return redirect()->route('productions.index')->with('success', 'Producción registrada exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Error al crear producción', [
                'user_id'   => auth()->id(),
                'input'     => $request->except(['_token']),
                'message'   => $e->getMessage(),
                'file'      => $e->getFile() . ':' . $e->getLine(),
                'trace'     => $e->getTraceAsString(),
            ]);
            return back()->withInput()->with('error', 'No fue posible registrar la producción: ' . $e->getMessage());
        }
    }

    public function show(Production $production)
    {
        $this->authorizeRecord($production);
        $production->load(['product', 'createdBy', 'costs', 'materials.product', 'company', 'overheadAllocations.period']);

        $openPeriod = OverheadPeriod::with('company')
            ->where('company_id', $production->company_id)
            ->where('status', 'abierto')
            ->latest('period_start')
            ->first();

        $distributionMethod = $production->company?->overhead_distribution_method ?? 'manual';

        return view('productions.show', compact('production', 'openPeriod', 'distributionMethod'));
    }

    public function suggestOverhead(Request $request)
    {
        $periodId = $request->integer('period_id');
        $qty      = (float) $request->input('production_qty', 0);

        $period = OverheadPeriod::with('company')->find($periodId);
        if (!$period) {
            return response()->json(['suggested' => 0, 'pending' => 0]);
        }

        $user = auth()->user();
        if (!$user->is_super_admin && $period->company_id !== $user->getCurrentCompany()?->id) {
            abort(403);
        }

        $method = $period->company?->overhead_distribution_method ?? 'manual';

        $tempProduction = new Production([
            'company_id'        => $period->company_id,
            'quantity_produced' => $qty,
            'production_date'   => now()->toDateString(),
            'status'            => 'in_progress',
        ]);

        return response()->json([
            'suggested' => $period->suggestedAllocation($tempProduction, $method),
            'pending'   => $period->pendingAmount(),
            'method'    => $method,
        ]);
    }

    public function addOverhead(Request $request, Production $production)
    {
        $this->authorizeRecord($production);

        $validated = $request->validate([
            'overhead_period_id' => 'nullable|exists:overhead_periods,id',
            'amount'             => 'required|numeric|min:0.01',
            'method'             => 'nullable|in:por_unidades,por_orden,tasa_fija,manual',
            'notes'              => 'nullable|string|max:500',
        ]);

        try {
            OverheadAllocation::create([
                'production_id'      => $production->id,
                'overhead_period_id' => $validated['overhead_period_id'] ?: null,
                'amount'             => $validated['amount'],
                'method'             => $validated['method'] ?? 'manual',
                'notes'              => $validated['notes'] ?? null,
            ]);

            $production->recalculateTotalCost();

            return back()->with('success', 'Overhead aplicado exitosamente.');
        } catch (\Throwable $e) {
            Log::error('Error al agregar overhead a producción', [
                'production_id' => $production->id,
                'message'       => $e->getMessage(),
            ]);
            return back()->with('error', 'No fue posible agregar el overhead.');
        }
    }

    public function deleteOverhead(Production $production, OverheadAllocation $allocation)
    {
        $this->authorizeRecord($production);

        if ($allocation->production_id !== $production->id) {
            abort(404);
        }

        try {
            $allocation->delete();
            $production->recalculateTotalCost();
            return back()->with('success', 'Overhead eliminado.');
        } catch (\Throwable $e) {
            Log::error('Error al eliminar overhead de producción', [
                'production_id'  => $production->id,
                'allocation_id'  => $allocation->id,
                'message'        => $e->getMessage(),
            ]);
            return back()->with('error', 'No fue posible eliminar el overhead.');
        }
    }

    public function updateStatus(Request $request, Production $production)
    {
        $this->authorizeRecord($production);
        $validated = $request->validate(['status' => 'required|in:planned,in_progress,completed,cancelled']);

        try {
            DB::transaction(function () use ($production, $validated) {
                // If completing, consume raw materials and add finished product to stock
                if ($validated['status'] === 'completed' && $production->status !== 'completed') {
                    foreach ($production->materials as $material) {
                        $product = Product::find($material->product_id);
                        if ($product && $product->current_stock < $material->quantity_used) {
                            throw new \Exception("Stock insuficiente de materia prima: {$product->name}");
                        }
                        Product::where('id', $material->product_id)->decrement('current_stock', $material->quantity_used);
                    }
                    Product::where('id', $production->product_id)->increment('current_stock', $production->quantity_produced);
                }

                $production->update(['status' => $validated['status']]);
            });

            return back()->with('success', 'Estado de producción actualizado.');
        } catch (\Throwable $e) {
            Log::error('Error al actualizar estado producción', [
                'user_id'     => auth()->id(),
                'production_id' => $production->id,
                'new_status'  => $validated['status'] ?? null,
                'message'     => $e->getMessage(),
                'file'        => $e->getFile() . ':' . $e->getLine(),
            ]);
            return back()->with('error', $e->getMessage());
        }
    }

    public function addCost(Request $request, Production $production)
    {
        $this->authorizeRecord($production);

        $validated = $request->validate([
            'concept' => 'required|string|max:255',
            'type'    => 'required|in:direct,indirect',
            'amount'  => 'required|numeric|min:0.01',
        ]);

        try {
            ProductionCost::create([
                'production_id' => $production->id,
                'concept'       => $validated['concept'],
                'type'          => $validated['type'],
                'amount'        => $validated['amount'],
            ]);

            $production->recalculateTotalCost();

            return back()->with('success', 'Gasto agregado exitosamente.');
        } catch (\Throwable $e) {
            Log::error('Error al agregar gasto a producción', [
                'production_id' => $production->id,
                'message'       => $e->getMessage(),
            ]);
            return back()->with('error', 'No fue posible agregar el gasto.');
        }
    }

    public function deleteCost(Production $production, ProductionCost $cost)
    {
        $this->authorizeRecord($production);

        if ($cost->production_id !== $production->id) {
            abort(404);
        }

        try {
            $cost->delete();
            $production->recalculateTotalCost();
            return back()->with('success', 'Gasto eliminado.');
        } catch (\Throwable $e) {
            Log::error('Error al eliminar gasto de producción', [
                'production_id' => $production->id,
                'cost_id'       => $cost->id,
                'message'       => $e->getMessage(),
            ]);
            return back()->with('error', 'No fue posible eliminar el gasto.');
        }
    }

    public function destroy(Production $production)
    {
        $this->authorizeRecord($production);
        if ($production->status === 'completed') {
            return back()->with('error', 'No se puede eliminar una producción completada.');
        }
        try {
            $production->costs()->delete();
            $production->materials()->delete();
            $production->overheadAllocations()->delete();
            $production->delete();
            return redirect()->route('productions.index')->with('success', 'Producción eliminada exitosamente.');
        } catch (\Throwable $e) {
            Log::error('Error al eliminar producción', [
                'user_id'       => auth()->id(),
                'production_id' => $production->id,
                'message'       => $e->getMessage(),
                'file'          => $e->getFile() . ':' . $e->getLine(),
            ]);
            return back()->with('error', 'No fue posible eliminar la producción.');
        }
    }

    private function getCompanyId(): ?int
    {
        $user = auth()->user();
        return $user->is_super_admin ? ($user->getCurrentCompany()?->id ?? request('company_id')) : $user->getCurrentCompany()?->id;
    }

    private function authorizeRecord($record): void
    {
        if (!auth()->user()->is_super_admin && $record->company_id !== auth()->user()->getCurrentCompany()?->id) {
            abort(403);
        }
    }
}
