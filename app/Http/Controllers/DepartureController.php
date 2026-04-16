<?php

namespace App\Http\Controllers;

use App\Models\Departure;
use App\Models\DepartureDetail;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DepartureController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Departure::with(['warehouse', 'createdBy'])->latest();

        if (!$user->is_super_admin) {
            $query->where('company_id', $user->getCurrentCompany()?->id);
        }

        if ($request->filled('status')) { $query->where('status', $request->status); }
        if ($request->filled('reason')) { $query->where('reason', $request->reason); }
        if ($request->filled('warehouse_id')) { $query->where('warehouse_id', $request->warehouse_id); }
        if ($request->filled('from')) { $query->whereDate('departure_date', '>=', $request->from); }
        if ($request->filled('to')) { $query->whereDate('departure_date', '<=', $request->to); }

        $companyId = $this->getCompanyId();

        return view('departures.index', [
            'departures' => $query->paginate(15)->withQueryString(),
            'warehouses' => Warehouse::where('company_id', $companyId)->orderBy('name')->get(),
            'filters' => $request->only(['status', 'reason', 'warehouse_id', 'from', 'to']),
        ]);
    }

    public function create()
    {
        $companyId = $this->getCompanyId();
        return view('departures.create', [
            'departure' => null,
            'warehouses' => Warehouse::where('company_id', $companyId)->where('active', true)->orderBy('name')->get(),
            'products' => Product::where('company_id', $companyId)->where('active', true)->orderBy('name')->get(),
            'nextNumber' => Departure::generateNumber($companyId),
            'action' => route('departures.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'warehouse_id' => 'required|exists:warehouses,id',
                'reason' => 'required|in:sale,production,transfer,damage,other',
                'departure_date' => 'required|date',
                'notes' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.unit_cost' => 'required|numeric|min:0',
            ]);

            $companyId = $this->getCompanyId();

            DB::transaction(function () use ($validated, $companyId) {
                $departure = Departure::create([
                    'company_id' => $companyId,
                    'warehouse_id' => $validated['warehouse_id'],
                    'departure_number' => Departure::generateNumber($companyId),
                    'reason' => $validated['reason'],
                    'departure_date' => $validated['departure_date'],
                    'notes' => $validated['notes'] ?? null,
                    'status' => 'draft',
                    'created_by' => auth()->id(),
                ]);

                foreach ($validated['items'] as $item) {
                    DepartureDetail::create([
                        'departure_id' => $departure->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_cost' => $item['unit_cost'],
                        'total' => $item['quantity'] * $item['unit_cost'],
                    ]);
                }

                $departure->recalculateTotal();
            });

            return redirect()->route('departures.index')->with('success', 'Salida registrada exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Error al crear salida', ['message' => $e->getMessage()]);
            return back()->withInput()->with('error', 'No fue posible registrar la salida.');
        }
    }

    public function show(Departure $departure)
    {
        $this->authorizeRecord($departure);
        $departure->load(['details.product', 'warehouse', 'createdBy', 'company']);
        return view('departures.show', compact('departure'));
    }

    public function confirm(Departure $departure)
    {
        $this->authorizeRecord($departure);
        if ($departure->status !== 'draft') {
            return back()->with('error', 'Solo se pueden confirmar salidas en borrador.');
        }

        // Validate stock availability
        foreach ($departure->details as $detail) {
            $product = Product::find($detail->product_id);
            if ($product && $product->current_stock < $detail->quantity) {
                return back()->with('error', "Stock insuficiente para {$product->name}. Disponible: {$product->current_stock}, Requerido: {$detail->quantity}");
            }
        }

        try {
            DB::transaction(function () use ($departure) {
                $departure->update(['status' => 'confirmed']);
                foreach ($departure->details as $detail) {
                    Product::where('id', $detail->product_id)
                        ->decrement('current_stock', $detail->quantity);
                }
            });
            return back()->with('success', 'Salida confirmada e inventario actualizado.');
        } catch (\Throwable $e) {
            Log::error('Error al confirmar salida', ['id' => $departure->id, 'message' => $e->getMessage()]);
            return back()->with('error', 'No fue posible confirmar la salida.');
        }
    }

    public function cancel(Departure $departure)
    {
        $this->authorizeRecord($departure);
        if ($departure->status === 'cancelled') {
            return back()->with('error', 'La salida ya está anulada.');
        }

        try {
            DB::transaction(function () use ($departure) {
                if ($departure->status === 'confirmed') {
                    foreach ($departure->details as $detail) {
                        Product::where('id', $detail->product_id)
                            ->increment('current_stock', $detail->quantity);
                    }
                }
                $departure->update(['status' => 'cancelled']);
            });
            return back()->with('success', 'Salida anulada exitosamente.');
        } catch (\Throwable $e) {
            Log::error('Error al anular salida', ['id' => $departure->id, 'message' => $e->getMessage()]);
            return back()->with('error', 'No fue posible anular la salida.');
        }
    }

    public function destroy(Departure $departure)
    {
        $this->authorizeRecord($departure);
        if ($departure->status === 'confirmed') {
            return back()->with('error', 'No se puede eliminar una salida confirmada. Anúlela primero.');
        }
        try {
            $departure->details()->delete();
            $departure->delete();
            return redirect()->route('departures.index')->with('success', 'Salida eliminada exitosamente.');
        } catch (\Throwable $e) {
            Log::error('Error al eliminar salida', ['id' => $departure->id, 'message' => $e->getMessage()]);
            return back()->with('error', 'No fue posible eliminar la salida.');
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
