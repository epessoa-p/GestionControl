<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Commission;
use App\Models\Product;
use App\Models\Promoter;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\SaleInstallment;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Sale::with(['promoter', 'createdBy', 'branch'])->latest();

        if (!$user->is_super_admin) {
            $query->where('company_id', $user->getCurrentCompany()?->id);
        }

        if ($request->filled('status')) { $query->where('status', $request->status); }
        if ($request->filled('promoter_id')) { $query->where('promoter_id', $request->promoter_id); }
        if ($request->filled('from')) { $query->whereDate('sale_date', '>=', $request->from); }
        if ($request->filled('to')) { $query->whereDate('sale_date', '<=', $request->to); }

        $companyId = $this->getCompanyId();

        return view('sales.index', [
            'sales' => $query->paginate(15)->withQueryString(),
            'promoters' => Promoter::where('company_id', $companyId)->orderBy('name')->get(),
            'filters' => $request->only(['status', 'promoter_id', 'from', 'to']),
        ]);
    }

    public function create()
    {
        $companyId = $this->getCompanyId();
        return view('sales.create', [
            'sale' => null,
            'products' => Product::where('company_id', $companyId)->where('active', true)->orderBy('name')->get(),
            'promoters' => Promoter::where('company_id', $companyId)->where('active', true)->orderBy('name')->get(),
            'branches' => Branch::where('company_id', $companyId)->orderBy('name')->get(),
            'warehouses' => Warehouse::where('company_id', $companyId)->where('active', true)->orderBy('name')->get(),
            'nextNumber' => Sale::generateNumber($companyId),
            'action' => route('sales.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'sale_date' => 'required|date',
                'client_name' => 'nullable|string|max:255',
                'client_phone' => 'nullable|string|max:50',
                'client_document' => 'nullable|string|max:50',
                'promoter_id' => 'nullable|exists:promoters,id',
                'branch_id' => 'nullable|exists:branches,id',
                'warehouse_id' => 'nullable|exists:warehouses,id',
                'payment_method' => 'required|in:cash,card,transfer,credit,other',
                'sale_type' => 'required|in:cash,credit',
                'tax' => 'nullable|numeric|min:0',
                'discount' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.discount' => 'nullable|numeric|min:0',
                'installments' => 'nullable|array',
                'installments.*.due_date' => 'required_if:sale_type,credit|date',
                'installments.*.amount' => 'required_if:sale_type,credit|numeric|min:0',
                'installments.*.percentage' => 'nullable|numeric|min:0|max:100',
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

                $sale = Sale::create([
                    'company_id' => $companyId,
                    'sale_number' => Sale::generateNumber($companyId),
                    'sale_date' => $validated['sale_date'],
                    'client_name' => $validated['client_name'] ?? null,
                    'client_phone' => $validated['client_phone'] ?? null,
                    'client_document' => $validated['client_document'] ?? null,
                    'promoter_id' => $validated['promoter_id'] ?? null,
                    'branch_id' => $validated['branch_id'] ?? null,
                    'warehouse_id' => $validated['warehouse_id'] ?? null,
                    'payment_method' => $validated['payment_method'],
                    'sale_type' => $validated['sale_type'] ?? 'cash',
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'discount' => $discount,
                    'total' => $total,
                    'status' => 'pending',
                    'notes' => $validated['notes'] ?? null,
                    'created_by' => auth()->id(),
                ]);

                // Create installments for credit sales
                if (($validated['sale_type'] ?? 'cash') === 'credit' && !empty($validated['installments'])) {
                    $installmentCount = count($validated['installments']);
                    $sale->update([
                        'credit_total_installments' => $installmentCount,
                        'credit_status' => 'pending',
                    ]);

                    foreach ($validated['installments'] as $idx => $inst) {
                        SaleInstallment::create([
                            'sale_id' => $sale->id,
                            'installment_number' => $idx + 1,
                            'due_date' => $inst['due_date'],
                            'amount' => $inst['amount'],
                            'percentage' => $inst['percentage'] ?? 0,
                            'status' => 'pending',
                        ]);
                    }
                }

                foreach ($validated['items'] as $item) {
                    $lineDiscount = $item['discount'] ?? 0;
                    SaleDetail::create([
                        'sale_id' => $sale->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'discount' => $lineDiscount,
                        'total' => ($item['quantity'] * $item['unit_price']) - $lineDiscount,
                    ]);
                }

                // Auto-generate commission if promoter has a commission rate
                if ($sale->promoter_id) {
                    $promoter = Promoter::find($sale->promoter_id);
                    if ($promoter && $promoter->commission_rate > 0) {
                        Commission::create([
                            'company_id' => $companyId,
                            'promoter_id' => $promoter->id,
                            'sale_id' => $sale->id,
                            'amount' => $total * ($promoter->commission_rate / 100),
                            'rate' => $promoter->commission_rate,
                            'status' => 'pending',
                            'created_by' => auth()->id(),
                        ]);
                    }
                }
            });

            return redirect()->route('sales.index')->with('success', 'Venta registrada exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Error al crear venta', ['message' => $e->getMessage()]);
            return back()->withInput()->with('error', 'No fue posible registrar la venta.');
        }
    }

    public function show(Sale $sale)
    {
        $this->authorizeRecord($sale);
        $sale->load(['details.product', 'promoter', 'branch', 'warehouse', 'createdBy', 'commissions', 'company', 'installments']);
        return view('sales.show', compact('sale'));
    }

    public function complete(Sale $sale)
    {
        $this->authorizeRecord($sale);
        if ($sale->status !== 'pending') {
            return back()->with('error', 'Solo se pueden completar ventas pendientes.');
        }

        try {
            DB::transaction(function () use ($sale) {
                // Decrease stock for each item
                foreach ($sale->details as $detail) {
                    $product = Product::find($detail->product_id);
                    if ($product && $product->current_stock < $detail->quantity) {
                        throw new \Exception("Stock insuficiente para {$product->name}.");
                    }
                    Product::where('id', $detail->product_id)->decrement('current_stock', $detail->quantity);
                }
                $sale->update(['status' => 'completed']);
            });
            return back()->with('success', 'Venta completada e inventario actualizado.');
        } catch (\Throwable $e) {
            Log::error('Error al completar venta', ['id' => $sale->id, 'message' => $e->getMessage()]);
            return back()->with('error', $e->getMessage());
        }
    }

    public function cancel(Sale $sale)
    {
        $this->authorizeRecord($sale);
        if ($sale->status === 'cancelled') {
            return back()->with('error', 'La venta ya está cancelada.');
        }

        try {
            DB::transaction(function () use ($sale) {
                if ($sale->status === 'completed') {
                    foreach ($sale->details as $detail) {
                        Product::where('id', $detail->product_id)->increment('current_stock', $detail->quantity);
                    }
                }
                $sale->update(['status' => 'cancelled']);
                $sale->commissions()->update(['status' => 'paid', 'notes' => 'Cancelada por anulación de venta']);
            });
            return back()->with('success', 'Venta cancelada exitosamente.');
        } catch (\Throwable $e) {
            Log::error('Error al cancelar venta', ['id' => $sale->id, 'message' => $e->getMessage()]);
            return back()->with('error', 'No fue posible cancelar la venta.');
        }
    }

    public function payInstallment(Request $request, Sale $sale, SaleInstallment $installment)
    {
        $this->authorizeRecord($sale);
        if ($installment->sale_id !== $sale->id) { abort(404); }
        if ($installment->status === 'paid') {
            return back()->with('error', 'Esta cuota ya está pagada.');
        }

        $validated = $request->validate([
            'pay_amount' => 'required|numeric|min:0.01|max:' . $installment->remaining,
            'pay_method' => 'required|in:cash,card,transfer,other',
            'pay_notes' => 'nullable|string|max:255',
        ]);

        try {
            DB::transaction(function () use ($sale, $installment, $validated) {
                $newPaid = $installment->paid_amount + $validated['pay_amount'];
                $status = $newPaid >= $installment->amount ? 'paid' : 'partial';

                $installment->update([
                    'paid_amount' => $newPaid,
                    'status' => $status,
                    'payment_method' => $validated['pay_method'],
                    'notes' => $validated['pay_notes'] ?? $installment->notes,
                    'paid_at' => $status === 'paid' ? now() : null,
                    'paid_by' => $status === 'paid' ? auth()->id() : null,
                ]);

                // Recalculate credit totals on the sale
                $totalPaid = $sale->installments()->sum('paid_amount');
                $allPaid = $sale->installments()->where('status', '!=', 'paid')->count() === 0;
                $sale->update([
                    'credit_paid_amount' => $totalPaid,
                    'credit_status' => $allPaid ? 'paid' : ($totalPaid > 0 ? 'partial' : 'pending'),
                ]);
            });
            return back()->with('success', 'Pago registrado exitosamente.');
        } catch (\Throwable $e) {
            Log::error('Error al registrar pago de cuota', ['message' => $e->getMessage()]);
            return back()->with('error', 'No fue posible registrar el pago.');
        }
    }

    public function destroy(Sale $sale)
    {
        $this->authorizeRecord($sale);
        if ($sale->status === 'completed') {
            return back()->with('error', 'No se puede eliminar una venta completada. Cancélela primero.');
        }
        try {
            $sale->commissions()->delete();
            $sale->details()->delete();
            $sale->delete();
            return redirect()->route('sales.index')->with('success', 'Venta eliminada exitosamente.');
        } catch (\Throwable $e) {
            Log::error('Error al eliminar venta', ['id' => $sale->id, 'message' => $e->getMessage()]);
            return back()->with('error', 'No fue posible eliminar la venta.');
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
