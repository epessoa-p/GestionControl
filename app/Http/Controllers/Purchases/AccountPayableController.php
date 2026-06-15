<?php

namespace App\Http\Controllers\Purchases;

use App\Http\Controllers\Controller;
use App\Models\AccountPayable;
use App\Models\AccountPayablePayment;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AccountPayableController extends Controller
{
    public function index(Request $request)
    {
        $companyId  = $this->getCompanyId();
        $status     = $request->get('status', 'todos');
        $supplierId = $request->get('supplier_id');

        $base = fn() => AccountPayable::where('company_id', $companyId);

        // Marcar vencidas automáticamente
        $base()->whereNotIn('status', ['pagada', 'anulada'])
            ->where('due_date', '<', now()->toDateString())
            ->update(['status' => 'vencida']);

        $payables = $base()
            ->with(['supplier', 'purchaseOrder'])
            ->when($status !== 'todos', fn($q) => $q->where('status', $status))
            ->when($supplierId, fn($q) => $q->where('supplier_id', $supplierId))
            ->orderByDesc('due_date')
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'todos'        => $base()->count(),
            'pendiente'    => $base()->where('status', 'pendiente')->count(),
            'pago_parcial' => $base()->where('status', 'pago_parcial')->count(),
            'vencida'      => $base()->where('status', 'vencida')->count(),
            'pagada'       => $base()->where('status', 'pagada')->count(),
        ];

        $totals = [
            'pendiente' => $base()->whereIn('status', ['pendiente', 'pago_parcial', 'vencida'])->sum('balance'),
            'vencido'   => $base()->where('status', 'vencida')->sum('balance'),
        ];

        $suppliers = Supplier::where('company_id', $companyId)->orderBy('name')->get();

        return view('purchases.payables.index', compact(
            'payables', 'status', 'counts', 'totals', 'suppliers', 'supplierId'
        ));
    }

    public function show(AccountPayable $accountPayable)
    {
        $this->authorizeRecord($accountPayable);
        $accountPayable->load(['supplier', 'purchaseOrder.items.product', 'reception', 'createdBy', 'payments.createdBy']);
        return view('purchases.payables.show', compact('accountPayable'));
    }

    public function addPayment(Request $request, AccountPayable $accountPayable)
    {
        $this->authorizeRecord($accountPayable);

        if (in_array($accountPayable->status, ['pagada', 'anulada'])) {
            return back()->with('error', 'Esta cuenta ya está pagada o anulada.');
        }

        $validated = $request->validate([
            'amount'         => 'required|numeric|min:0.01|max:' . $accountPayable->balance,
            'payment_date'   => 'required|date',
            'payment_method' => 'required|in:' . implode(',', AccountPayablePayment::PAYMENT_METHODS),
            'reference'      => 'nullable|string|max:255',
            'notes'          => 'nullable|string',
        ]);

        try {
            AccountPayablePayment::create(array_merge($validated, [
                'accounts_payable_id' => $accountPayable->id,
                'created_by'          => auth()->id(),
            ]));

            $accountPayable->recalculateBalance();

            return back()->with('success', 'Pago registrado exitosamente.');
        } catch (\Throwable $e) {
            Log::error('Error al registrar pago', ['message' => $e->getMessage()]);
            return back()->with('error', 'No fue posible registrar el pago.');
        }
    }

    public function deletePayment(AccountPayable $accountPayable, AccountPayablePayment $payment)
    {
        $this->authorizeRecord($accountPayable);

        if ($payment->accounts_payable_id !== $accountPayable->id) {
            abort(404);
        }

        $payment->delete();
        $accountPayable->recalculateBalance();

        return back()->with('success', 'Pago eliminado.');
    }

    private function getCompanyId(): ?int
    {
        $user = auth()->user();
        return $user->is_super_admin
            ? ($user->getCurrentCompany()?->id ?? request('company_id'))
            : $user->getCurrentCompany()?->id;
    }

    private function authorizeRecord(AccountPayable $record): void
    {
        if (!auth()->user()->is_super_admin && $record->company_id !== $this->getCompanyId()) {
            abort(403);
        }
    }
}
