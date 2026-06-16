<?php

namespace App\Http\Controllers\Purchases;

use App\Http\Controllers\Controller;
use App\Models\AccountPayable;
use App\Models\AccountPayablePayment;
use App\Models\CashMovement;
use App\Models\CashRegister;
use App\Models\CashSession;
use App\Models\Personal;
use App\Models\Supplier;
use App\Models\TreasuryAccount;
use App\Models\TreasuryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $accountPayable->load([
            'supplier', 'purchaseOrder.items.product', 'reception', 'createdBy',
            'payments.createdBy', 'payments.treasuryAccount',
        ]);

        $companyId        = $this->getCompanyId();
        $openSession      = $this->activeSession($companyId);
        $treasuryAccounts = TreasuryAccount::where('company_id', $companyId)
            ->where('active', true)->orderBy('name')->get();

        return view('purchases.payables.show', compact('accountPayable', 'openSession', 'treasuryAccounts'));
    }

    public function addPayment(Request $request, AccountPayable $accountPayable)
    {
        $this->authorizeRecord($accountPayable);

        if (in_array($accountPayable->status, ['pagada', 'anulada'])) {
            return back()->with('error', 'Esta cuenta ya está pagada o anulada.');
        }

        $companyId = $this->getCompanyId();

        $validated = $request->validate([
            'amount'              => 'required|numeric|min:0.01|max:' . $accountPayable->balance,
            'payment_date'        => 'required|date',
            'source'              => 'required|in:caja,tesoreria',
            'payment_method'      => 'required|in:' . implode(',', AccountPayablePayment::PAYMENT_METHODS),
            'treasury_account_id' => 'required_if:source,tesoreria|nullable|exists:treasury_accounts,id',
            'reference'           => 'nullable|string|max:255',
            'notes'               => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($validated, $accountPayable, $companyId) {
                $amount   = (float) $validated['amount'];
                $supplier = $accountPayable->supplier?->display_name ?? '';
                $concept  = "Pago CxP {$accountPayable->ap_number}" . ($supplier ? " · {$supplier}" : '');

                $payment = AccountPayablePayment::create([
                    'accounts_payable_id' => $accountPayable->id,
                    'amount'              => $amount,
                    'payment_date'        => $validated['payment_date'],
                    'payment_method'      => $validated['payment_method'],
                    'reference'           => $validated['reference'] ?? null,
                    'notes'               => $validated['notes'] ?? null,
                    'source'              => $validated['source'],
                    'created_by'          => auth()->id(),
                ]);

                if ($validated['source'] === 'caja') {
                    $session = $this->activeSession($companyId);
                    if (!$session) {
                        throw new \RuntimeException('No tienes una caja abierta. Abre una caja o paga desde Tesorería.');
                    }
                    $cm = CashMovement::create([
                        'cash_session_id' => $session->id,
                        'type'            => 'expense',
                        'category'        => 'expense_supplier',
                        'amount'          => $amount,
                        'concept'         => $concept,
                        'payment_method'  => $this->mapPaymentMethod($validated['payment_method']),
                        'reference'       => $validated['reference'] ?? null,
                        'movement_date'   => now(),
                        'created_by'      => auth()->id(),
                    ]);
                    $payment->update(['cash_session_id' => $session->id, 'cash_movement_id' => $cm->id]);
                } else {
                    $account = TreasuryAccount::where('company_id', $companyId)->findOrFail($validated['treasury_account_id']);
                    $tm = TreasuryMovement::create([
                        'treasury_account_id' => $account->id,
                        'company_id'          => $companyId,
                        'type'                => 'salida',
                        'category'            => 'pago_proveedor',
                        'amount'              => $amount,
                        'description'         => $concept,
                        'reference'           => $validated['reference'] ?? null,
                        'movement_date'       => $validated['payment_date'],
                        'created_by'          => auth()->id(),
                    ]);
                    $account->recalculateBalance();
                    $payment->update(['treasury_account_id' => $account->id, 'treasury_movement_id' => $tm->id]);
                }

                $accountPayable->recalculateBalance();
            });

            return back()->with('success', 'Pago registrado y reflejado en movimientos.');
        } catch (\Throwable $e) {
            Log::error('Error al registrar pago', ['message' => $e->getMessage()]);
            return back()->with('error', $e->getMessage());
        }
    }

    public function deletePayment(AccountPayable $accountPayable, AccountPayablePayment $payment)
    {
        $this->authorizeRecord($accountPayable);

        if ($payment->accounts_payable_id !== $accountPayable->id) {
            abort(404);
        }

        DB::transaction(function () use ($payment, $accountPayable) {
            // Revertir el movimiento financiero asociado
            if ($payment->cash_movement_id) {
                CashMovement::where('id', $payment->cash_movement_id)->delete();
            }
            if ($payment->treasury_movement_id) {
                $tm = TreasuryMovement::find($payment->treasury_movement_id);
                if ($tm) {
                    $accountId = $tm->treasury_account_id;
                    $tm->delete();
                    TreasuryAccount::find($accountId)?->recalculateBalance();
                }
            }

            $payment->delete();
            $accountPayable->recalculateBalance();
        });

        return back()->with('success', 'Pago eliminado y movimiento revertido.');
    }

    /** Sesión de caja abierta asignada al usuario actual, si existe. */
    private function activeSession(?int $companyId): ?CashSession
    {
        if (!$companyId) {
            return null;
        }
        $personal = Personal::where('user_id', auth()->id())->where('company_id', $companyId)->first();
        if (!$personal) {
            return null;
        }
        $register = CashRegister::where('assigned_personal_id', $personal->id)
            ->where('company_id', $companyId)->where('active', true)->first();
        return $register?->activeSession();
    }

    /** Mapea el método de pago (es) al enum de CashMovement (en). */
    private function mapPaymentMethod(string $method): string
    {
        return [
            'efectivo'      => 'cash',
            'transferencia' => 'transfer',
            'tarjeta'       => 'card',
            'cheque'        => 'other',
            'otro'          => 'other',
        ][$method] ?? 'other';
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
