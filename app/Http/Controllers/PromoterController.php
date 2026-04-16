<?php

namespace App\Http\Controllers;

use App\Models\Personal;
use App\Models\Promoter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PromoterController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $query = Promoter::with(['personal', 'createdBy'])->latest();

        if (!$user->is_super_admin) {
            $query->where('company_id', $user->getCurrentCompany()?->id);
        }

        return view('promoters.index', ['promoters' => $query->paginate(15)]);
    }

    public function create()
    {
        $companyId = $this->getCompanyId();
        return view('promoters.create', [
            'promoter' => null,
            'personals' => Personal::where('company_id', $companyId)->where('active', true)->orderBy('full_name')->get(),
            'action' => route('promoters.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'nullable|string|max:50',
                'email' => 'nullable|email|max:255',
                'personal_id' => 'nullable|exists:personals,id',
                'commission_rate' => 'required|numeric|min:0|max:100',
                'active' => 'sometimes|boolean',
            ]);

            Promoter::create([
                ...$validated,
                'company_id' => $this->getCompanyId(),
                'active' => $request->boolean('active', true),
                'created_by' => auth()->id(),
            ]);

            return redirect()->route('promoters.index')->with('success', 'Promotor creado exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Error al crear promotor', ['message' => $e->getMessage()]);
            return back()->withInput()->with('error', 'No fue posible crear el promotor.');
        }
    }

    public function show(Promoter $promoter)
    {
        $this->authorizeRecord($promoter);
        $promoter->load(['personal', 'createdBy', 'sales' => fn($q) => $q->latest()->take(10), 'commissions']);
        return view('promoters.show', compact('promoter'));
    }

    public function edit(Promoter $promoter)
    {
        $this->authorizeRecord($promoter);
        return view('promoters.edit', [
            'promoter' => $promoter,
            'personals' => Personal::where('company_id', $promoter->company_id)->where('active', true)->orderBy('full_name')->get(),
            'action' => route('promoters.update', $promoter),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, Promoter $promoter)
    {
        $this->authorizeRecord($promoter);
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'nullable|string|max:50',
                'email' => 'nullable|email|max:255',
                'personal_id' => 'nullable|exists:personals,id',
                'commission_rate' => 'required|numeric|min:0|max:100',
                'active' => 'sometimes|boolean',
            ]);

            $promoter->update([...$validated, 'active' => $request->boolean('active', false)]);

            return redirect()->route('promoters.index')->with('success', 'Promotor actualizado exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Error al actualizar promotor', ['id' => $promoter->id, 'message' => $e->getMessage()]);
            return back()->withInput()->with('error', 'No fue posible actualizar el promotor.');
        }
    }

    public function destroy(Promoter $promoter)
    {
        $this->authorizeRecord($promoter);
        try {
            $promoter->delete();
            return redirect()->route('promoters.index')->with('success', 'Promotor eliminado exitosamente.');
        } catch (\Throwable $e) {
            Log::error('Error al eliminar promotor', ['id' => $promoter->id, 'message' => $e->getMessage()]);
            return back()->with('error', 'No fue posible eliminar el promotor.');
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
