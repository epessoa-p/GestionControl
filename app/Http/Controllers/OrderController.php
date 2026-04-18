<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['createdBy', 'branch'])->latest();

        if (!auth()->user()->is_super_admin) {
            $query->where('company_id', auth()->user()->getCurrentCompany()?->id);
        }

        if ($request->filled('status')) { $query->where('status', $request->status); }
        if ($request->filled('order_type')) { $query->where('order_type', $request->order_type); }
        if ($request->filled('priority')) { $query->where('priority', $request->priority); }
        if ($request->filled('from')) { $query->whereDate('order_date', '>=', $request->from); }
        if ($request->filled('to')) { $query->whereDate('order_date', '<=', $request->to); }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('order_number', 'like', "%{$s}%")
                  ->orWhere('client_name', 'like', "%{$s}%");
            });
        }

        return view('orders.index', [
            'orders' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only(['status', 'order_type', 'priority', 'from', 'to', 'search']),
        ]);
    }

    public function create()
    {
        $companyId = $this->getCompanyId();
        return view('orders.create', [
            'order' => null,
            'products' => Product::where('company_id', $companyId)->where('active', true)->orderBy('name')->get(),
            'branches' => Branch::where('company_id', $companyId)->orderBy('name')->get(),
            'warehouses' => Warehouse::where('company_id', $companyId)->where('active', true)->orderBy('name')->get(),
            'nextNumber' => Order::generateNumber($companyId),
            'action' => route('orders.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'order_date' => 'required|date',
                'expected_date' => 'nullable|date|after_or_equal:order_date',
                'order_type' => 'required|in:purchase,customer',
                'client_name' => 'nullable|string|max:255',
                'client_phone' => 'nullable|string|max:50',
                'client_document' => 'nullable|string|max:50',
                'client_email' => 'nullable|email|max:255',
                'client_address' => 'nullable|string',
                'branch_id' => 'nullable|exists:branches,id',
                'warehouse_id' => 'nullable|exists:warehouses,id',
                'priority' => 'required|in:low,medium,high,urgent',
                'tax' => 'nullable|numeric|min:0',
                'discount' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.discount' => 'nullable|numeric|min:0',
            ]);

            $companyId = $this->getCompanyId();

            DB::transaction(function () use ($validated, $companyId) {
                $subtotal = 0;
                foreach ($validated['items'] as $item) {
                    $lineDiscount = $item['discount'] ?? 0;
                    $subtotal += ($item['quantity'] * $item['unit_price']) - $lineDiscount;
                }

                $tax = $validated['tax'] ?? 0;
                $discount = $validated['discount'] ?? 0;
                $total = $subtotal + $tax - $discount;

                $order = Order::create([
                    'company_id' => $companyId,
                    'order_number' => Order::generateNumber($companyId),
                    'order_type' => $validated['order_type'],
                    'order_date' => $validated['order_date'],
                    'expected_date' => $validated['expected_date'] ?? null,
                    'client_name' => $validated['client_name'] ?? null,
                    'client_phone' => $validated['client_phone'] ?? null,
                    'client_document' => $validated['client_document'] ?? null,
                    'client_email' => $validated['client_email'] ?? null,
                    'client_address' => $validated['client_address'] ?? null,
                    'branch_id' => $validated['branch_id'] ?? null,
                    'warehouse_id' => $validated['warehouse_id'] ?? null,
                    'priority' => $validated['priority'],
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'discount' => $discount,
                    'total' => $total,
                    'status' => 'draft',
                    'notes' => $validated['notes'] ?? null,
                    'created_by' => auth()->id(),
                ]);

                foreach ($validated['items'] as $item) {
                    $lineDiscount = $item['discount'] ?? 0;
                    OrderDetail::create([
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'discount' => $lineDiscount,
                        'total' => ($item['quantity'] * $item['unit_price']) - $lineDiscount,
                    ]);
                }
            });

            return redirect()->route('orders.index')->with('success', 'Orden registrada exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Error al crear orden', ['message' => $e->getMessage()]);
            return back()->withInput()->with('error', 'No fue posible registrar la orden.');
        }
    }

    public function show(Order $order)
    {
        $this->authorizeRecord($order);
        $order->load(['details.product', 'branch', 'warehouse', 'createdBy', 'company']);
        return view('orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $this->authorizeRecord($order);
        $validated = $request->validate([
            'status' => 'required|in:confirmed,in_process,shipped,delivered,cancelled',
        ]);

        $newStatus = $validated['status'];
        $allowed = $this->getAllowedTransitions($order->status);

        if (!in_array($newStatus, $allowed)) {
            return back()->with('error', 'Transición de estado no permitida.');
        }

        try {
            $data = ['status' => $newStatus];
            if ($newStatus === 'delivered') {
                $data['delivered_date'] = now();
            }
            $order->update($data);
            return back()->with('success', 'Estado actualizado a: ' . Order::STATUS_LABELS[$newStatus]);
        } catch (\Throwable $e) {
            Log::error('Error al cambiar estado de orden', ['id' => $order->id, 'message' => $e->getMessage()]);
            return back()->with('error', 'No fue posible cambiar el estado.');
        }
    }

    public function destroy(Order $order)
    {
        $this->authorizeRecord($order);
        if (!in_array($order->status, ['draft', 'cancelled'])) {
            return back()->with('error', 'Solo se pueden eliminar órdenes en borrador o canceladas.');
        }
        try {
            $order->details()->delete();
            $order->delete();
            return redirect()->route('orders.index')->with('success', 'Orden eliminada exitosamente.');
        } catch (\Throwable $e) {
            Log::error('Error al eliminar orden', ['id' => $order->id, 'message' => $e->getMessage()]);
            return back()->with('error', 'No fue posible eliminar la orden.');
        }
    }

    private function getAllowedTransitions(string $current): array
    {
        return match ($current) {
            'draft' => ['confirmed', 'cancelled'],
            'confirmed' => ['in_process', 'cancelled'],
            'in_process' => ['shipped', 'delivered', 'cancelled'],
            'shipped' => ['delivered', 'cancelled'],
            default => [],
        };
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
