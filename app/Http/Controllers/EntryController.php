<?php

namespace App\Http\Controllers;

use App\Models\Entry;
use App\Models\EntryDetail;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EntryController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Entry::with(['warehouse', 'createdBy'])->latest();

        if (!$user->is_super_admin) {
            $query->where('company_id', $user->getCurrentCompany()?->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }
        if ($request->filled('from')) {
            $query->whereDate('entry_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('entry_date', '<=', $request->to);
        }

        $companyId = $this->getCompanyId();

        return view('entries.index', [
            'entries' => $query->paginate(15)->withQueryString(),
            'warehouses' => Warehouse::where('company_id', $companyId)->orderBy('name')->get(),
            'filters' => $request->only(['status', 'warehouse_id', 'from', 'to']),
        ]);
    }

    public function create()
    {
        $companyId = $this->getCompanyId();
        return view('entries.create', [
            'entry' => null,
            'warehouses' => Warehouse::where('company_id', $companyId)->where('active', true)->orderBy('name')->get(),
            'products' => Product::where('company_id', $companyId)->where('active', true)->orderBy('name')->get(),
            'nextNumber' => Entry::generateNumber($companyId),
            'action' => route('entries.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'warehouse_id' => 'required|exists:warehouses,id',
                'supplier' => 'nullable|string|max:255',
                'entry_date' => 'required|date',
                'notes' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.unit_cost' => 'required|numeric|min:0',
            ]);

            $companyId = $this->getCompanyId();

            DB::transaction(function () use ($validated, $companyId) {
                $entry = Entry::create([
                    'company_id' => $companyId,
                    'warehouse_id' => $validated['warehouse_id'],
                    'entry_number' => Entry::generateNumber($companyId),
                    'supplier' => $validated['supplier'] ?? null,
                    'entry_date' => $validated['entry_date'],
                    'notes' => $validated['notes'] ?? null,
                    'status' => 'draft',
                    'created_by' => auth()->id(),
                ]);

                foreach ($validated['items'] as $item) {
                    EntryDetail::create([
                        'entry_id' => $entry->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_cost' => $item['unit_cost'],
                        'total' => $item['quantity'] * $item['unit_cost'],
                    ]);
                }

                $entry->recalculateTotal();
            });

            return redirect()->route('entries.index')->with('success', 'Entrada registrada exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Error al crear entrada', ['message' => $e->getMessage()]);
            return back()->withInput()->with('error', 'No fue posible registrar la entrada.');
        }
    }

    public function show(Entry $entry)
    {
        $this->authorizeRecord($entry);
        $entry->load(['details.product', 'warehouse', 'createdBy', 'company']);
        return view('entries.show', compact('entry'));
    }

    public function confirm(Entry $entry)
    {
        $this->authorizeRecord($entry);
        if ($entry->status !== 'draft') {
            return back()->with('error', 'Solo se pueden confirmar entradas en borrador.');
        }

        try {
            DB::transaction(function () use ($entry) {
                $entry->update(['status' => 'confirmed']);
                foreach ($entry->details as $detail) {
                    Product::where('id', $detail->product_id)
                        ->increment('current_stock', $detail->quantity);
                }
            });
            return back()->with('success', 'Entrada confirmada e inventario actualizado.');
        } catch (\Throwable $e) {
            Log::error('Error al confirmar entrada', ['id' => $entry->id, 'message' => $e->getMessage()]);
            return back()->with('error', 'No fue posible confirmar la entrada.');
        }
    }

    public function cancel(Entry $entry)
    {
        $this->authorizeRecord($entry);
        if ($entry->status === 'cancelled') {
            return back()->with('error', 'La entrada ya está anulada.');
        }

        try {
            DB::transaction(function () use ($entry) {
                if ($entry->status === 'confirmed') {
                    foreach ($entry->details as $detail) {
                        Product::where('id', $detail->product_id)
                            ->decrement('current_stock', $detail->quantity);
                    }
                }
                $entry->update(['status' => 'cancelled']);
            });
            return back()->with('success', 'Entrada anulada exitosamente.');
        } catch (\Throwable $e) {
            Log::error('Error al anular entrada', ['id' => $entry->id, 'message' => $e->getMessage()]);
            return back()->with('error', 'No fue posible anular la entrada.');
        }
    }

    public function destroy(Entry $entry)
    {
        $this->authorizeRecord($entry);
        if ($entry->status === 'confirmed') {
            return back()->with('error', 'No se puede eliminar una entrada confirmada. Anúlela primero.');
        }
        try {
            $entry->details()->delete();
            $entry->delete();
            return redirect()->route('entries.index')->with('success', 'Entrada eliminada exitosamente.');
        } catch (\Throwable $e) {
            Log::error('Error al eliminar entrada', ['id' => $entry->id, 'message' => $e->getMessage()]);
            return back()->with('error', 'No fue posible eliminar la entrada.');
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
