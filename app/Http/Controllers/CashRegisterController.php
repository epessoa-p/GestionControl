<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\CashRegister;
use App\Models\CashSession;
use App\Models\CashMovement;
use App\Models\Personal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CashRegisterController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $query = CashRegister::with(['branch', 'createdBy'])->latest();

        if (!$user->is_super_admin) {
            $query->where('company_id', $user->getCurrentCompany()?->id);
        }

        return view('cash-registers.index', [
            'cashRegisters' => $query->paginate(15),
        ]);
    }

    public function create()
    {
        $companyId = $this->getCompanyId();
        return view('cash-registers.create', [
            'cashRegister' => null,
            'branches' => Branch::where('company_id', $companyId)->orderBy('name')->get(),
            'action' => route('cash-registers.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'nullable|string|max:50',
                'branch_id' => 'nullable|exists:branches,id',
                'active' => 'sometimes|boolean',
            ]);

            CashRegister::create([
                ...$validated,
                'company_id' => $this->getCompanyId(),
                'active' => $request->boolean('active', true),
                'created_by' => auth()->id(),
            ]);

            return redirect()->route('cash-registers.index')->with('success', 'Caja creada exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Error al crear caja', ['message' => $e->getMessage()]);
            return back()->withInput()->with('error', 'No fue posible crear la caja.');
        }
    }

    public function show(CashRegister $cashRegister)
    {
        $this->authorizeRecord($cashRegister);
        $cashRegister->load(['branch', 'createdBy', 'sessions.personal', 'sessions.openedBy']);

        return view('cash-registers.show', [
            'cashRegister' => $cashRegister,
            'sessions' => $cashRegister->sessions()->with(['personal', 'openedBy', 'closedBy'])->latest('opened_at')->paginate(10),
        ]);
    }

    public function edit(CashRegister $cashRegister)
    {
        $this->authorizeRecord($cashRegister);
        $companyId = $cashRegister->company_id;

        return view('cash-registers.edit', [
            'cashRegister' => $cashRegister,
            'branches' => Branch::where('company_id', $companyId)->orderBy('name')->get(),
            'action' => route('cash-registers.update', $cashRegister),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, CashRegister $cashRegister)
    {
        $this->authorizeRecord($cashRegister);
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'nullable|string|max:50',
                'branch_id' => 'nullable|exists:branches,id',
                'active' => 'sometimes|boolean',
            ]);

            $cashRegister->update([...$validated, 'active' => $request->boolean('active', false)]);

            return redirect()->route('cash-registers.index')->with('success', 'Caja actualizada exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Error al actualizar caja', ['id' => $cashRegister->id, 'message' => $e->getMessage()]);
            return back()->withInput()->with('error', 'No fue posible actualizar la caja.');
        }
    }

    public function destroy(CashRegister $cashRegister)
    {
        $this->authorizeRecord($cashRegister);
        if ($cashRegister->sessions()->where('status', 'open')->exists()) {
            return back()->with('error', 'No se puede eliminar una caja con sesiones abiertas.');
        }
        try {
            $cashRegister->delete();
            return redirect()->route('cash-registers.index')->with('success', 'Caja eliminada exitosamente.');
        } catch (\Throwable $e) {
            Log::error('Error al eliminar caja', ['id' => $cashRegister->id, 'message' => $e->getMessage()]);
            return back()->with('error', 'No fue posible eliminar la caja.');
        }
    }

    // ─── Session Management ───
    public function openSession(Request $request, CashRegister $cashRegister)
    {
        $this->authorizeRecord($cashRegister);

        if ($cashRegister->activeSession()) {
            return back()->with('error', 'Esta caja ya tiene una sesión abierta.');
        }

        try {
            $validated = $request->validate([
                'personal_id' => 'required|exists:personals,id',
                'opening_amount' => 'required|numeric|min:0',
            ]);

            CashSession::create([
                'cash_register_id' => $cashRegister->id,
                'personal_id' => $validated['personal_id'],
                'opening_amount' => $validated['opening_amount'],
                'status' => 'open',
                'opened_at' => now(),
                'opened_by' => auth()->id(),
            ]);

            return back()->with('success', 'Caja abierta exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Error al abrir caja', ['id' => $cashRegister->id, 'message' => $e->getMessage()]);
            return back()->with('error', 'No fue posible abrir la caja.');
        }
    }

    public function closeSession(Request $request, CashSession $cashSession)
    {
        if (!$cashSession->isOpen()) {
            return back()->with('error', 'Esta sesión ya está cerrada.');
        }

        try {
            $validated = $request->validate([
                'closing_amount' => 'required|numeric|min:0',
                'notes' => 'nullable|string',
            ]);

            $expected = $cashSession->calculateExpectedAmount();

            $cashSession->update([
                'closing_amount' => $validated['closing_amount'],
                'expected_amount' => $expected,
                'difference' => $validated['closing_amount'] - $expected,
                'status' => 'closed',
                'closed_at' => now(),
                'closed_by' => auth()->id(),
                'notes' => $validated['notes'] ?? $cashSession->notes,
            ]);

            return back()->with('success', 'Caja cerrada exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Error al cerrar caja', ['session_id' => $cashSession->id, 'message' => $e->getMessage()]);
            return back()->with('error', 'No fue posible cerrar la caja.');
        }
    }

    public function sessionDetail(CashSession $cashSession)
    {
        $cashSession->load(['cashRegister', 'personal', 'openedBy', 'closedBy', 'movements.createdBy']);
        $companyId = $cashSession->cashRegister->company_id;
        $personals = Personal::where('company_id', $companyId)->where('active', true)->orderBy('full_name')->get();

        return view('cash-registers.session', [
            'session' => $cashSession,
            'personals' => $personals,
        ]);
    }

    public function addMovement(Request $request, CashSession $cashSession)
    {
        if (!$cashSession->isOpen()) {
            return back()->with('error', 'No se pueden agregar movimientos a una sesión cerrada.');
        }

        try {
            $validated = $request->validate([
                'type' => 'required|in:income,expense',
                'amount' => 'required|numeric|min:0.01',
                'concept' => 'required|string|max:255',
                'payment_method' => 'required|in:cash,card,transfer,other',
                'reference' => 'nullable|string|max:255',
            ]);

            CashMovement::create([
                'cash_session_id' => $cashSession->id,
                ...$validated,
                'created_by' => auth()->id(),
            ]);

            return back()->with('success', 'Movimiento registrado exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Error al registrar movimiento', ['session_id' => $cashSession->id, 'message' => $e->getMessage()]);
            return back()->with('error', 'No fue posible registrar el movimiento.');
        }
    }

    public function openSessionForm(CashRegister $cashRegister)
    {
        $this->authorizeRecord($cashRegister);
        $companyId = $cashRegister->company_id;
        $personals = Personal::where('company_id', $companyId)->where('active', true)->orderBy('full_name')->get();

        return view('cash-registers.open-session', [
            'cashRegister' => $cashRegister,
            'personals' => $personals,
        ]);
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
