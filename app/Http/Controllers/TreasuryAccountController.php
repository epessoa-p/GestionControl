<?php

namespace App\Http\Controllers;

use App\Models\TreasuryAccount;
use App\Models\TreasuryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TreasuryAccountController extends Controller
{
    private function getCompanyId(): ?int
    {
        $user = auth()->user();
        if ($user->is_super_admin) {
            return session('current_company_id');
        }
        return $user->getCurrentCompany()?->id;
    }

    private function authorizeRecord(TreasuryAccount $account): void
    {
        $companyId = $this->getCompanyId();
        if ($account->company_id !== $companyId) {
            abort(403);
        }
    }

    public function index()
    {
        $companyId = $this->getCompanyId();

        $accounts = TreasuryAccount::where('company_id', $companyId)
            ->withCount('movements')
            ->orderBy('name')
            ->get();

        $totalBalance = $accounts->where('active', true)->sum('current_balance');

        return view('treasury.index', compact('accounts', 'totalBalance'));
    }

    public function create()
    {
        return view('treasury.create', [
            'account' => null,
            'action'  => route('treasury.store'),
            'method'  => 'POST',
        ]);
    }

    public function store(Request $request)
    {
        $companyId = $this->getCompanyId();

        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'type'            => 'required|in:banco,efectivo,otro',
            'bank_name'       => 'nullable|string|max:100',
            'account_number'  => 'nullable|string|max:50',
            'initial_balance' => 'required|numeric|min:0',
            'color'           => 'nullable|string|max:20',
            'active'          => 'boolean',
            'notes'           => 'nullable|string',
        ]);

        $validated['company_id']      = $companyId;
        $validated['current_balance'] = $validated['initial_balance'];
        $validated['active']          = $request->boolean('active', true);
        $validated['color']           = $validated['color'] ?? TreasuryAccount::TYPE_COLORS[$validated['type']];
        $validated['created_by']      = auth()->id();

        DB::transaction(function () use ($validated) {
            $account = TreasuryAccount::create($validated);

            if ((float) $validated['initial_balance'] > 0) {
                TreasuryMovement::create([
                    'treasury_account_id' => $account->id,
                    'company_id'          => $account->company_id,
                    'type'                => 'entrada',
                    'category'            => 'aporte_capital',
                    'amount'              => $validated['initial_balance'],
                    'description'         => 'Saldo inicial',
                    'movement_date'       => now()->toDateString(),
                    'created_by'          => auth()->id(),
                ]);
            }
        });

        return redirect()->route('treasury.index')->with('success', 'Cuenta creada correctamente.');
    }

    public function show(TreasuryAccount $account)
    {
        $this->authorizeRecord($account);

        $movements = $account->movements()
            ->with('createdBy')
            ->orderByDesc('movement_date')
            ->orderByDesc('id')
            ->paginate(20);

        return view('treasury.show', compact('account', 'movements'));
    }

    public function edit(TreasuryAccount $account)
    {
        $this->authorizeRecord($account);

        return view('treasury.edit', [
            'account' => $account,
            'action'  => route('treasury.update', $account),
            'method'  => 'PUT',
        ]);
    }

    public function update(Request $request, TreasuryAccount $account)
    {
        $this->authorizeRecord($account);

        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'type'           => 'required|in:banco,efectivo,otro',
            'bank_name'      => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:50',
            'color'          => 'nullable|string|max:20',
            'active'         => 'boolean',
            'notes'          => 'nullable|string',
        ]);

        $validated['active'] = $request->boolean('active');

        $account->update($validated);

        return redirect()->route('treasury.show', $account)->with('success', 'Cuenta actualizada correctamente.');
    }

    public function destroy(TreasuryAccount $account)
    {
        $this->authorizeRecord($account);

        $account->delete();

        return redirect()->route('treasury.index')->with('success', 'Cuenta eliminada.');
    }

    public function addMovement(Request $request, TreasuryAccount $account)
    {
        $this->authorizeRecord($account);

        $validated = $request->validate([
            'type'          => 'required|in:entrada,salida',
            'category'      => 'required|string|max:60',
            'amount'        => 'required|numeric|min:0.01',
            'description'   => 'nullable|string',
            'reference'     => 'nullable|string|max:100',
            'movement_date' => 'required|date',
        ]);

        $categories = TreasuryMovement::CATEGORIES;
        if (isset($categories[$validated['category']]) && $categories[$validated['category']]['type'] !== $validated['type']) {
            return back()->withErrors(['category' => 'La categoría no corresponde al tipo de movimiento.']);
        }

        DB::transaction(function () use ($validated, $account) {
            TreasuryMovement::create([
                'treasury_account_id' => $account->id,
                'company_id'          => $account->company_id,
                'type'                => $validated['type'],
                'category'            => $validated['category'],
                'amount'              => $validated['amount'],
                'description'         => $validated['description'] ?? null,
                'reference'           => $validated['reference'] ?? null,
                'movement_date'       => $validated['movement_date'],
                'created_by'          => auth()->id(),
            ]);

            $account->recalculateBalance();
        });

        return redirect()->route('treasury.show', $account)->with('success', 'Movimiento registrado correctamente.');
    }

    public function deleteMovement(TreasuryAccount $account, TreasuryMovement $mov)
    {
        $this->authorizeRecord($account);

        if ($mov->treasury_account_id !== $account->id) {
            abort(403);
        }

        DB::transaction(function () use ($mov, $account) {
            $mov->delete();
            $account->recalculateBalance();
        });

        return redirect()->route('treasury.show', $account)->with('success', 'Movimiento eliminado.');
    }
}
