<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\PettyCash;
use App\Models\PettyCashMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PettyCashController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $query = PettyCash::with(['branch', 'createdBy'])->latest();

        if (!$user->is_super_admin) {
            $query->where('company_id', $user->getCurrentCompany()?->id);
        }

        return view('petty-cash.index', ['pettyCashes' => $query->paginate(15)]);
    }

    public function create()
    {
        $companyId = $this->getCompanyId();
        return view('petty-cash.create', [
            'pettyCash' => null,
            'branches' => Branch::where('company_id', $companyId)->orderBy('name')->get(),
            'action' => route('petty-cash.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'branch_id' => 'nullable|exists:branches,id',
                'initial_amount' => 'required|numeric|min:0',
            ]);

            PettyCash::create([
                ...$validated,
                'company_id' => $this->getCompanyId(),
                'current_balance' => $validated['initial_amount'],
                'active' => true,
                'created_by' => auth()->id(),
            ]);

            return redirect()->route('petty-cash.index')->with('success', 'Caja chica creada exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Error al crear caja chica', ['message' => $e->getMessage()]);
            return back()->withInput()->with('error', 'No fue posible crear la caja chica.');
        }
    }

    public function show(PettyCash $pettyCash)
    {
        $this->authorizeRecord($pettyCash);
        $pettyCash->load(['branch', 'createdBy']);

        return view('petty-cash.show', [
            'pettyCash' => $pettyCash,
            'movements' => $pettyCash->movements()->with('createdBy')->latest('movement_date')->paginate(15),
        ]);
    }

    public function addMovement(Request $request, PettyCash $pettyCash)
    {
        $this->authorizeRecord($pettyCash);
        if (!$pettyCash->active) {
            return back()->with('error', 'Esta caja chica está inactiva.');
        }

        try {
            $validated = $request->validate([
                'type' => 'required|in:expense,replenishment',
                'amount' => 'required|numeric|min:0.01',
                'concept' => 'required|string|max:255',
                'receipt_number' => 'nullable|string|max:100',
                'movement_date' => 'required|date',
            ]);

            if ($validated['type'] === 'expense' && $pettyCash->current_balance < $validated['amount']) {
                return back()->withInput()->with('error', 'Saldo insuficiente en caja chica.');
            }

            PettyCashMovement::create([
                'petty_cash_id' => $pettyCash->id,
                ...$validated,
                'created_by' => auth()->id(),
            ]);

            $pettyCash->recalculateBalance();

            return back()->with('success', 'Movimiento registrado exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Error al registrar movimiento caja chica', ['id' => $pettyCash->id, 'message' => $e->getMessage()]);
            return back()->with('error', 'No fue posible registrar el movimiento.');
        }
    }

    public function destroy(PettyCash $pettyCash)
    {
        $this->authorizeRecord($pettyCash);
        try {
            $pettyCash->movements()->delete();
            $pettyCash->delete();
            return redirect()->route('petty-cash.index')->with('success', 'Caja chica eliminada exitosamente.');
        } catch (\Throwable $e) {
            Log::error('Error al eliminar caja chica', ['id' => $pettyCash->id, 'message' => $e->getMessage()]);
            return back()->with('error', 'No fue posible eliminar la caja chica.');
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
