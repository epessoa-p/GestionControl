<?php

namespace App\Http\Controllers\Purchases;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReception;
use App\Models\PurchaseReceptionItem;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseReceptionController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $this->getCompanyId();
        $status    = $request->get('status', 'todos');

        $base = fn() => PurchaseReception::where('company_id', $companyId);

        $receptions = $base()
            ->with(['purchaseOrder.supplier', 'warehouse', 'createdBy'])
            ->when($status !== 'todos', fn($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'todos'      => $base()->count(),
            'borrador'   => $base()->where('status', 'borrador')->count(),
            'confirmada' => $base()->where('status', 'confirmada')->count(),
            'cancelada'  => $base()->where('status', 'cancelada')->count(),
        ];

        return view('purchases.receptions.index', compact('receptions', 'status', 'counts'));
    }

    public function create(Request $request)
    {
        $companyId       = $this->getCompanyId();
        $receptionNumber = PurchaseReception::generateReceptionNumber($companyId);
        $warehouses      = Warehouse::where('company_id', $companyId)->where('active', true)->orderBy('name')->get();

        $orders = PurchaseOrder::where('company_id', $companyId)
            ->whereIn('status', ['aprobada', 'enviada', 'recibida_parcial'])
            ->with('supplier')
            ->orderBy('order_number')
            ->get();

        $selectedOrder = null;
        if ($request->has('order_id')) {
            $selectedOrder = PurchaseOrder::with(['items.product', 'supplier', 'warehouse'])
                ->findOrFail($request->order_id);
        }

        return view('purchases.receptions.create', compact(
            'receptionNumber', 'warehouses', 'orders', 'selectedOrder'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_order_id'  => 'required|exists:purchase_orders,id',
            'warehouse_id'       => 'required|exists:warehouses,id',
            'reception_date'     => 'required|date',
            'invoice_number'     => 'nullable|string|max:100',
            'notes'              => 'nullable|string',
            'items'              => 'required|array|min:1',
            'items.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.product_id'             => 'required|exists:products,id',
            'items.*.quantity_ordered'       => 'required|numeric|min:0',
            'items.*.quantity_received'      => 'required|numeric|min:0',
            'items.*.unit_price'             => 'required|numeric|min:0',
        ]);

        $companyId = $this->getCompanyId();

        try {
            DB::transaction(function () use ($validated, $companyId) {
                $reception = PurchaseReception::create([
                    'company_id'        => $companyId,
                    'reception_number'  => PurchaseReception::generateReceptionNumber($companyId),
                    'purchase_order_id' => $validated['purchase_order_id'],
                    'warehouse_id'      => $validated['warehouse_id'],
                    'reception_date'    => $validated['reception_date'],
                    'invoice_number'    => $validated['invoice_number'] ?? null,
                    'status'            => 'borrador',
                    'notes'             => $validated['notes'] ?? null,
                    'created_by'        => auth()->id(),
                ]);

                foreach ($validated['items'] as $item) {
                    $total = (float) $item['quantity_received'] * (float) $item['unit_price'];
                    PurchaseReceptionItem::create([
                        'purchase_reception_id'  => $reception->id,
                        'purchase_order_item_id' => $item['purchase_order_item_id'],
                        'product_id'             => $item['product_id'],
                        'quantity_ordered'        => $item['quantity_ordered'],
                        'quantity_received'       => $item['quantity_received'],
                        'unit_price'             => $item['unit_price'],
                        'total'                  => $total,
                    ]);
                }
            });

            return redirect()->route('purchases.receptions.index')
                ->with('success', 'Recepción guardada. Revisa los ítems y confirma cuando sea correcto.');
        } catch (\Throwable $e) {
            Log::error('Error al crear recepción', ['message' => $e->getMessage()]);
            return back()->withInput()->with('error', 'No fue posible guardar la recepción.');
        }
    }

    public function show(PurchaseReception $purchaseReception)
    {
        $this->authorizeRecord($purchaseReception);
        $purchaseReception->load([
            'purchaseOrder.supplier', 'warehouse', 'createdBy',
            'items.product', 'items.purchaseOrderItem', 'accountPayable',
        ]);
        return view('purchases.receptions.show', compact('purchaseReception'));
    }

    public function confirm(PurchaseReception $purchaseReception)
    {
        $this->authorizeRecord($purchaseReception);

        if ($purchaseReception->status !== 'borrador') {
            return back()->with('error', 'Solo se pueden confirmar recepciones en borrador.');
        }

        try {
            $purchaseReception->load('items.product', 'items.purchaseOrderItem', 'purchaseOrder.supplier');
            $purchaseReception->confirm();
            return back()->with('success', 'Recepción confirmada. Stock actualizado y cuenta por pagar generada.');
        } catch (\Throwable $e) {
            Log::error('Error al confirmar recepción', ['message' => $e->getMessage()]);
            return back()->with('error', 'No fue posible confirmar la recepción: ' . $e->getMessage());
        }
    }

    public function cancel(PurchaseReception $purchaseReception)
    {
        $this->authorizeRecord($purchaseReception);
        if ($purchaseReception->status !== 'borrador') {
            return back()->with('error', 'Solo se pueden cancelar recepciones en borrador.');
        }
        $purchaseReception->update(['status' => 'cancelada']);
        return back()->with('success', 'Recepción cancelada.');
    }

    public function destroy(PurchaseReception $purchaseReception)
    {
        $this->authorizeRecord($purchaseReception);
        if ($purchaseReception->status !== 'borrador') {
            return back()->with('error', 'Solo se pueden eliminar recepciones en borrador.');
        }
        $purchaseReception->delete();
        return redirect()->route('purchases.receptions.index')->with('success', 'Recepción eliminada.');
    }

    private function getCompanyId(): ?int
    {
        $user = auth()->user();
        return $user->is_super_admin
            ? ($user->getCurrentCompany()?->id ?? request('company_id'))
            : $user->getCurrentCompany()?->id;
    }

    private function authorizeRecord(PurchaseReception $record): void
    {
        if (!auth()->user()->is_super_admin && $record->company_id !== $this->getCompanyId()) {
            abort(403);
        }
    }
}
