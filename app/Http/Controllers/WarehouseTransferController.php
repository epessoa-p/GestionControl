<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseTransfer;
use App\Models\WarehouseTransferDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WarehouseTransferController extends Controller
{
    public function index(Request $request)
    {
        $query = WarehouseTransfer::with(['fromWarehouse', 'toWarehouse', 'createdBy'])->latest();

        if (!auth()->user()->is_super_admin) {
            $query->where('company_id', auth()->user()->getCurrentCompany()?->id);
        }

        if ($request->filled('status')) { $query->where('status', $request->status); }
        if ($request->filled('from')) { $query->whereDate('transfer_date', '>=', $request->from); }
        if ($request->filled('to')) { $query->whereDate('transfer_date', '<=', $request->to); }

        return view('transfers.index', [
            'transfers' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only(['status', 'from', 'to']),
        ]);
    }

    public function create()
    {
        $companyId = $this->getCompanyId();
        return view('transfers.create', [
            'products' => Product::where('company_id', $companyId)->where('active', true)->orderBy('name')->get(),
            'warehouses' => Warehouse::where('company_id', $companyId)->where('active', true)->orderBy('name')->get(),
            'nextNumber' => WarehouseTransfer::generateNumber($companyId),
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'transfer_date' => 'required|date',
                'from_warehouse_id' => 'required|exists:warehouses,id',
                'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
                'notes' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.notes' => 'nullable|string|max:255',
            ]);

            $companyId = $this->getCompanyId();

            DB::transaction(function () use ($validated, $companyId) {
                $transfer = WarehouseTransfer::create([
                    'company_id' => $companyId,
                    'transfer_number' => WarehouseTransfer::generateNumber($companyId),
                    'from_warehouse_id' => $validated['from_warehouse_id'],
                    'to_warehouse_id' => $validated['to_warehouse_id'],
                    'transfer_date' => $validated['transfer_date'],
                    'status' => 'draft',
                    'notes' => $validated['notes'] ?? null,
                    'total_items' => count($validated['items']),
                    'created_by' => auth()->id(),
                ]);

                foreach ($validated['items'] as $item) {
                    WarehouseTransferDetail::create([
                        'warehouse_transfer_id' => $transfer->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'notes' => $item['notes'] ?? null,
                    ]);
                }
            });

            return redirect()->route('transfers.index')->with('success', 'Traspaso creado exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Error al crear traspaso', ['message' => $e->getMessage()]);
            return back()->withInput()->with('error', 'No fue posible crear el traspaso.');
        }
    }

    public function show(WarehouseTransfer $transfer)
    {
        $this->authorizeRecord($transfer);
        $transfer->load(['details.product', 'fromWarehouse', 'toWarehouse', 'createdBy', 'confirmedBy', 'company']);
        return view('transfers.show', compact('transfer'));
    }

    public function dispatch(WarehouseTransfer $transfer)
    {
        $this->authorizeRecord($transfer);
        if ($transfer->status !== 'draft') {
            return back()->with('error', 'Solo se pueden enviar traspasos en borrador.');
        }

        try {
            DB::transaction(function () use ($transfer) {
                // Decrease stock from source warehouse
                foreach ($transfer->details as $detail) {
                    $product = Product::find($detail->product_id);
                    if ($product && $product->current_stock < $detail->quantity) {
                        throw new \Exception("Stock insuficiente para {$product->name} en almacén origen.");
                    }
                    Product::where('id', $detail->product_id)->decrement('current_stock', $detail->quantity);
                }
                $transfer->update(['status' => 'in_transit']);
            });
            return back()->with('success', 'Traspaso enviado. Stock descontado del almacén origen.');
        } catch (\Throwable $e) {
            Log::error('Error al enviar traspaso', ['id' => $transfer->id, 'message' => $e->getMessage()]);
            return back()->with('error', $e->getMessage());
        }
    }

    public function complete(WarehouseTransfer $transfer)
    {
        $this->authorizeRecord($transfer);
        if ($transfer->status !== 'in_transit') {
            return back()->with('error', 'Solo se pueden completar traspasos en tránsito.');
        }

        try {
            DB::transaction(function () use ($transfer) {
                // Increase stock in destination warehouse
                foreach ($transfer->details as $detail) {
                    Product::where('id', $detail->product_id)->increment('current_stock', $detail->quantity);
                }
                $transfer->update([
                    'status' => 'completed',
                    'confirmed_by' => auth()->id(),
                    'confirmed_at' => now(),
                ]);
            });
            return back()->with('success', 'Traspaso completado. Stock agregado al almacén destino.');
        } catch (\Throwable $e) {
            Log::error('Error al completar traspaso', ['id' => $transfer->id, 'message' => $e->getMessage()]);
            return back()->with('error', 'No fue posible completar el traspaso.');
        }
    }

    public function cancel(WarehouseTransfer $transfer)
    {
        $this->authorizeRecord($transfer);
        if ($transfer->status === 'completed' || $transfer->status === 'cancelled') {
            return back()->with('error', 'No se puede cancelar este traspaso.');
        }

        try {
            DB::transaction(function () use ($transfer) {
                // If was in transit, reverse the stock decrement from source
                if ($transfer->status === 'in_transit') {
                    foreach ($transfer->details as $detail) {
                        Product::where('id', $detail->product_id)->increment('current_stock', $detail->quantity);
                    }
                }
                $transfer->update(['status' => 'cancelled']);
            });
            return back()->with('success', 'Traspaso cancelado exitosamente.');
        } catch (\Throwable $e) {
            Log::error('Error al cancelar traspaso', ['id' => $transfer->id, 'message' => $e->getMessage()]);
            return back()->with('error', 'No fue posible cancelar el traspaso.');
        }
    }

    public function destroy(WarehouseTransfer $transfer)
    {
        $this->authorizeRecord($transfer);
        if ($transfer->status !== 'draft') {
            return back()->with('error', 'Solo se pueden eliminar traspasos en borrador.');
        }
        try {
            $transfer->details()->delete();
            $transfer->delete();
            return redirect()->route('transfers.index')->with('success', 'Traspaso eliminado exitosamente.');
        } catch (\Throwable $e) {
            Log::error('Error al eliminar traspaso', ['id' => $transfer->id, 'message' => $e->getMessage()]);
            return back()->with('error', 'No fue posible eliminar el traspaso.');
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
