<?php

namespace App\Http\Controllers;

use App\Models\Tracking;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TrackingController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Tracking::with(['assignedTo', 'createdBy'])->latest();

        if (!$user->is_super_admin) {
            $query->where('company_id', $user->getCurrentCompany()?->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        return view('trackings.index', [
            'trackings' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only(['status', 'priority', 'type']),
        ]);
    }

    public function create()
    {
        $companyId = $this->getCompanyId();
        $users = User::whereHas('companies', fn($q) => $q->where('companies.id', $companyId))->orderBy('name')->get();

        return view('trackings.create', [
            'tracking' => null,
            'users' => $users,
            'action' => route('trackings.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'type' => 'required|in:operation,client,sale,internal',
                'status' => 'required|in:pending,in_progress,completed',
                'priority' => 'required|in:low,medium,high,urgent',
                'due_date' => 'nullable|date',
                'assigned_to' => 'nullable|exists:users,id',
            ]);

            Tracking::create([
                ...$validated,
                'company_id' => $this->getCompanyId(),
                'created_by' => auth()->id(),
                'completed_at' => $validated['status'] === 'completed' ? now() : null,
            ]);

            return redirect()->route('trackings.index')->with('success', 'Seguimiento creado exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Error al crear seguimiento', ['message' => $e->getMessage()]);
            return back()->withInput()->with('error', 'No fue posible crear el seguimiento.');
        }
    }

    public function show(Tracking $tracking)
    {
        $this->authorizeRecord($tracking);
        $tracking->load(['assignedTo', 'createdBy', 'company']);
        return view('trackings.show', compact('tracking'));
    }

    public function edit(Tracking $tracking)
    {
        $this->authorizeRecord($tracking);
        $companyId = $tracking->company_id;
        $users = User::whereHas('companies', fn($q) => $q->where('companies.id', $companyId))->orderBy('name')->get();

        return view('trackings.edit', [
            'tracking' => $tracking,
            'users' => $users,
            'action' => route('trackings.update', $tracking),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, Tracking $tracking)
    {
        $this->authorizeRecord($tracking);
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'type' => 'required|in:operation,client,sale,internal',
                'status' => 'required|in:pending,in_progress,completed',
                'priority' => 'required|in:low,medium,high,urgent',
                'due_date' => 'nullable|date',
                'assigned_to' => 'nullable|exists:users,id',
            ]);

            $completedAt = $tracking->completed_at;
            if ($validated['status'] === 'completed' && !$tracking->completed_at) {
                $completedAt = now();
            } elseif ($validated['status'] !== 'completed') {
                $completedAt = null;
            }

            $tracking->update([...$validated, 'completed_at' => $completedAt]);

            return redirect()->route('trackings.index')->with('success', 'Seguimiento actualizado exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Error al actualizar seguimiento', ['id' => $tracking->id, 'message' => $e->getMessage()]);
            return back()->withInput()->with('error', 'No fue posible actualizar el seguimiento.');
        }
    }

    public function destroy(Tracking $tracking)
    {
        $this->authorizeRecord($tracking);
        try {
            $tracking->delete();
            return redirect()->route('trackings.index')->with('success', 'Seguimiento eliminado exitosamente.');
        } catch (\Throwable $e) {
            Log::error('Error al eliminar seguimiento', ['id' => $tracking->id, 'message' => $e->getMessage()]);
            return back()->with('error', 'No fue posible eliminar el seguimiento.');
        }
    }

    private function getCompanyId(): ?int
    {
        $user = auth()->user();
        return $user->is_super_admin ? ($user->getCurrentCompany()?->id ?? request('company_id')) : $user->getCurrentCompany()?->id;
    }

    private function authorizeRecord(Tracking $tracking): void
    {
        if (!auth()->user()->is_super_admin && $tracking->company_id !== auth()->user()->getCurrentCompany()?->id) {
            abort(403);
        }
    }
}
