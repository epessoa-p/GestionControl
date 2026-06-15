<?php

namespace App\Http\Controllers\Purchases;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseQuotation;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $this->getCompanyId();
        $status    = $request->get('status', 'todos');
        $search    = $request->get('q');

        $base = fn() => PurchaseOrder::where('company_id', $companyId);

        $orders = $base()
            ->with(['supplier', 'warehouse', 'createdBy'])
            ->when($status !== 'todos', fn($q) => $q->where('status', $status))
            ->when($search, fn($q) => $q->where(function ($q2) use ($search) {
                $q2->where('order_number', 'like', "%{$search}%");
            }))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $counts = collect(array_merge(['todos' => $base()->count()],
            array_fill_keys(PurchaseOrder::STATUSES, 0),
            $base()->groupBy('status')->selectRaw('status, count(*) as total')
                   ->pluck('total', 'status')->toArray()
        ));

        return view('purchases.orders.index', compact('orders', 'status', 'counts', 'search'));
    }

    public function create(Request $request)
    {
        $companyId   = $this->getCompanyId();
        $orderNumber = PurchaseOrder::generateOrderNumber($companyId);
        $suppliers   = Supplier::where('company_id', $companyId)->where('status', 'activo')->orderBy('name')->get();
        $warehouses  = Warehouse::where('company_id', $companyId)->where('active', true)->orderBy('name')->get();
        $products    = Product::where('company_id', $companyId)->where('active', true)->orderBy('name')->get();
        $quotations  = PurchaseQuotation::where('company_id', $companyId)
            ->where('status', 'aprobada')->with('supplier')->orderBy('quotation_number')->get();

        $fromQuotation = null;
        if ($request->has('quotation_id')) {
            $fromQuotation = PurchaseQuotation::with('items.product', 'supplier')->find($request->quotation_id);
        }

        return view('purchases.orders.create', compact(
            'orderNumber', 'suppliers', 'warehouses', 'products', 'quotations', 'fromQuotation'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id'          => 'required|exists:suppliers,id',
            'purchase_quotation_id'=> 'nullable|exists:purchase_quotations,id',
            'warehouse_id'         => 'required|exists:warehouses,id',
            'order_date'           => 'required|date',
            'expected_date'        => 'nullable|date|after_or_equal:order_date',
            'notes'                => 'nullable|string',
            'items'                => 'required|array|min:1',
            'items.*.product_id'   => 'required|exists:products,id',
            'items.*.quantity'     => 'required|numeric|min:0.01',
            'items.*.unit_price'   => 'required|numeric|min:0',
            'items.*.discount'     => 'nullable|numeric|min:0',
        ]);

        $companyId = $this->getCompanyId();

        try {
            DB::transaction(function () use ($validated, $companyId) {
                $order = PurchaseOrder::create([
                    'company_id'           => $companyId,
                    'order_number'         => PurchaseOrder::generateOrderNumber($companyId),
                    'supplier_id'          => $validated['supplier_id'],
                    'purchase_quotation_id'=> $validated['purchase_quotation_id'] ?? null,
                    'warehouse_id'         => $validated['warehouse_id'],
                    'order_date'           => $validated['order_date'],
                    'expected_date'        => $validated['expected_date'] ?? null,
                    'status'               => 'borrador',
                    'notes'                => $validated['notes'] ?? null,
                    'created_by'           => auth()->id(),
                ]);

                foreach ($validated['items'] as $item) {
                    if (empty($item['product_id'])) continue;
                    $discount = (float) ($item['discount'] ?? 0);
                    $total    = ((float) $item['quantity'] * (float) $item['unit_price']) - $discount;
                    PurchaseOrderItem::create([
                        'purchase_order_id' => $order->id,
                        'product_id'        => $item['product_id'],
                        'quantity'          => $item['quantity'],
                        'unit_price'        => $item['unit_price'],
                        'discount'          => $discount,
                        'total'             => max(0, $total),
                    ]);
                }

                $order->recalculateTotals();
            });

            return redirect()->route('purchases.orders.index')
                ->with('success', 'Orden de compra creada exitosamente.');
        } catch (\Throwable $e) {
            Log::error('Error al crear orden de compra', ['message' => $e->getMessage()]);
            return back()->withInput()->with('error', 'No fue posible crear la orden de compra.');
        }
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $this->authorizeRecord($purchaseOrder);
        $purchaseOrder->load([
            'supplier', 'warehouse', 'quotation', 'createdBy',
            'items.product', 'receptions.items', 'accountPayable.payments',
        ]);
        return view('purchases.orders.show', ['order' => $purchaseOrder]);
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        $this->authorizeRecord($purchaseOrder);

        if (!in_array($purchaseOrder->status, ['borrador', 'aprobada'])) {
            return redirect()->route('purchases.orders.show', $purchaseOrder)
                ->with('error', 'Solo se pueden editar órdenes en borrador o aprobadas.');
        }

        $companyId  = $this->getCompanyId();
        $purchaseOrder->load('items.product');

        $suppliers  = Supplier::where('company_id', $companyId)->where('status', 'activo')->orderBy('name')->get();
        $warehouses = Warehouse::where('company_id', $companyId)->where('active', true)->orderBy('name')->get();
        $products   = Product::where('company_id', $companyId)->where('active', true)->orderBy('name')->get();
        $quotations = PurchaseQuotation::where('company_id', $companyId)
            ->where('status', 'aprobada')->with('supplier')->orderBy('quotation_number')->get();

        return view('purchases.orders.edit', [
            'order'         => $purchaseOrder,
            'orderNumber'   => $purchaseOrder->order_number,
            'suppliers'     => $suppliers,
            'warehouses'    => $warehouses,
            'products'      => $products,
            'quotations'    => $quotations,
            'fromQuotation' => null,
        ]);
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->authorizeRecord($purchaseOrder);

        if (!in_array($purchaseOrder->status, ['borrador', 'aprobada'])) {
            return back()->with('error', 'Solo se pueden editar órdenes en borrador o aprobadas.');
        }
        if ($purchaseOrder->receptions()->exists()) {
            return back()->with('error', 'No se puede editar una orden que ya tiene recepciones.');
        }

        $validated = $request->validate([
            'supplier_id'          => 'required|exists:suppliers,id',
            'purchase_quotation_id'=> 'nullable|exists:purchase_quotations,id',
            'warehouse_id'         => 'required|exists:warehouses,id',
            'order_date'           => 'required|date',
            'expected_date'        => 'nullable|date|after_or_equal:order_date',
            'notes'                => 'nullable|string',
            'items'                => 'required|array|min:1',
            'items.*.product_id'   => 'required|exists:products,id',
            'items.*.quantity'     => 'required|numeric|min:0.01',
            'items.*.unit_price'   => 'required|numeric|min:0',
            'items.*.discount'     => 'nullable|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($validated, $purchaseOrder) {
                $purchaseOrder->update([
                    'supplier_id'           => $validated['supplier_id'],
                    'purchase_quotation_id' => $validated['purchase_quotation_id'] ?? null,
                    'warehouse_id'          => $validated['warehouse_id'],
                    'order_date'            => $validated['order_date'],
                    'expected_date'         => $validated['expected_date'] ?? null,
                    'notes'                 => $validated['notes'] ?? null,
                ]);

                $purchaseOrder->items()->delete();

                foreach ($validated['items'] as $item) {
                    if (empty($item['product_id'])) continue;
                    $discount = (float) ($item['discount'] ?? 0);
                    $total    = ((float) $item['quantity'] * (float) $item['unit_price']) - $discount;
                    PurchaseOrderItem::create([
                        'purchase_order_id' => $purchaseOrder->id,
                        'product_id'        => $item['product_id'],
                        'quantity'          => $item['quantity'],
                        'unit_price'        => $item['unit_price'],
                        'discount'          => $discount,
                        'total'             => max(0, $total),
                    ]);
                }

                $purchaseOrder->recalculateTotals();
            });

            return redirect()->route('purchases.orders.show', $purchaseOrder)
                ->with('success', 'Orden de compra actualizada exitosamente.');
        } catch (\Throwable $e) {
            Log::error('Error al actualizar orden de compra', ['id' => $purchaseOrder->id, 'message' => $e->getMessage()]);
            return back()->withInput()->with('error', 'No fue posible actualizar la orden de compra.');
        }
    }

    public function updateStatus(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->authorizeRecord($purchaseOrder);

        $allowed = ['aprobada', 'enviada', 'cancelada'];
        $request->validate(['status' => 'required|in:' . implode(',', $allowed)]);

        if ($purchaseOrder->status === 'cancelada') {
            return back()->with('error', 'No se puede cambiar el estado de una orden cancelada.');
        }

        $purchaseOrder->update(['status' => $request->status]);
        return back()->with('success', 'Estado actualizado a: ' . PurchaseOrder::STATUS_LABELS[$request->status]);
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        $this->authorizeRecord($purchaseOrder);
        if (!in_array($purchaseOrder->status, ['borrador', 'cancelada'])) {
            return back()->with('error', 'Solo se pueden eliminar órdenes en borrador o canceladas.');
        }
        $purchaseOrder->delete();
        return redirect()->route('purchases.orders.index')->with('success', 'Orden de compra eliminada.');
    }

    private function getCompanyId(): ?int
    {
        $user = auth()->user();
        return $user->is_super_admin
            ? ($user->getCurrentCompany()?->id ?? request('company_id'))
            : $user->getCurrentCompany()?->id;
    }

    private function authorizeRecord(PurchaseOrder $record): void
    {
        if (!auth()->user()->is_super_admin && $record->company_id !== $this->getCompanyId()) {
            abort(403);
        }
    }
}
