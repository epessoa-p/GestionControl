<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Production;
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
        return view('productions.create', [
            'production' => null,
            'products' => Product::where('company_id', $companyId)->where('active', true)->orderBy('name')->get(),
            'rawMaterials' => Product::where('company_id', $companyId)->where('active', true)->where('category', 'raw_material')->orderBy('name')->get(),
            'batchNumber' => Production::generateBatchNumber($companyId),
            'action' => route('productions.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity_produced' => 'required|numeric|min:0.01',
                'production_date' => 'required|date',
                'notes' => 'nullable|string',
                'costs' => 'nullable|array',
                'costs.*.concept' => 'required_with:costs|string|max:255',
                'costs.*.type' => 'required_with:costs|in:direct,indirect',
                'costs.*.amount' => 'required_with:costs|numeric|min:0',
                'materials' => 'nullable|array',
                'materials.*.product_id' => 'required_with:materials|exists:products,id',
                'materials.*.quantity_used' => 'required_with:materials|numeric|min:0.01',
                'materials.*.unit_cost' => 'required_with:materials|numeric|min:0',
            ]);

            $companyId = $this->getCompanyId();

            DB::transaction(function () use ($validated, $companyId) {
                $production = Production::create([
                    'company_id' => $companyId,
                    'product_id' => $validated['product_id'],
                    'batch_number' => Production::generateBatchNumber($companyId),
                    'quantity_produced' => $validated['quantity_produced'],
                    'production_date' => $validated['production_date'],
                    'status' => 'planned',
                    'notes' => $validated['notes'] ?? null,
                    'created_by' => auth()->id(),
                ]);

                if (!empty($validated['costs'])) {
                    foreach ($validated['costs'] as $cost) {
                        ProductionCost::create(['production_id' => $production->id, ...$cost]);
                    }
                }

                if (!empty($validated['materials'])) {
                    foreach ($validated['materials'] as $material) {
                        ProductionMaterial::create([
                            'production_id' => $production->id,
                            'product_id' => $material['product_id'],
                            'quantity_used' => $material['quantity_used'],
                            'unit_cost' => $material['unit_cost'],
                            'total_cost' => $material['quantity_used'] * $material['unit_cost'],
                        ]);
                    }
                }

                $production->recalculateTotalCost();
            });

            return redirect()->route('productions.index')->with('success', 'Producción registrada exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Error al crear producción', ['message' => $e->getMessage()]);
            return back()->withInput()->with('error', 'No fue posible registrar la producción.');
        }
    }

    public function show(Production $production)
    {
        $this->authorizeRecord($production);
        $production->load(['product', 'createdBy', 'costs', 'materials.product', 'company']);
        return view('productions.show', compact('production'));
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
            Log::error('Error al actualizar estado producción', ['id' => $production->id, 'message' => $e->getMessage()]);
            return back()->with('error', $e->getMessage());
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
            $production->delete();
            return redirect()->route('productions.index')->with('success', 'Producción eliminada exitosamente.');
        } catch (\Throwable $e) {
            Log::error('Error al eliminar producción', ['id' => $production->id, 'message' => $e->getMessage()]);
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
