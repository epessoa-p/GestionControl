<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\Promoter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CommissionController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Commission::with(['promoter', 'sale', 'createdBy'])->latest();

        if (!$user->is_super_admin) {
            $query->where('company_id', $user->getCurrentCompany()?->id);
        }

        if ($request->filled('status')) { $query->where('status', $request->status); }
        if ($request->filled('promoter_id')) { $query->where('promoter_id', $request->promoter_id); }
        if ($request->filled('from')) { $query->whereDate('created_at', '>=', $request->from); }
        if ($request->filled('to')) { $query->whereDate('created_at', '<=', $request->to); }

        $companyId = $this->getCompanyId();

        $totalPending = Commission::where('company_id', $companyId)->where('status', 'pending')->sum('amount');
        $totalPaid = Commission::where('company_id', $companyId)->where('status', 'paid')->sum('amount');

        return view('commissions.index', [
            'commissions' => $query->paginate(15)->withQueryString(),
            'promoters' => Promoter::where('company_id', $companyId)->orderBy('name')->get(),
            'totalPending' => $totalPending,
            'totalPaid' => $totalPaid,
            'filters' => $request->only(['status', 'promoter_id', 'from', 'to']),
        ]);
    }

    public function markPaid(Commission $commission)
    {
        $this->authorizeRecord($commission);
        if ($commission->status === 'paid') {
            return back()->with('error', 'La comisión ya está pagada.');
        }

        try {
            $commission->update([
                'status' => 'paid',
                'paid_at' => now(),
                'paid_by' => auth()->id(),
            ]);
            return back()->with('success', 'Comisión marcada como pagada.');
        } catch (\Throwable $e) {
            Log::error('Error al pagar comisión', ['id' => $commission->id, 'message' => $e->getMessage()]);
            return back()->with('error', 'No fue posible actualizar la comisión.');
        }
    }

    public function markPaidBulk(Request $request)
    {
        $validated = $request->validate([
            'commission_ids' => 'required|array|min:1',
            'commission_ids.*' => 'exists:commissions,id',
        ]);

        try {
            Commission::whereIn('id', $validated['commission_ids'])
                ->where('status', 'pending')
                ->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'paid_by' => auth()->id(),
                ]);
            return back()->with('success', 'Comisiones marcadas como pagadas.');
        } catch (\Throwable $e) {
            Log::error('Error al pagar comisiones en lote', ['message' => $e->getMessage()]);
            return back()->with('error', 'No fue posible actualizar las comisiones.');
        }
    }

    public function destroy(Commission $commission)
    {
        $this->authorizeRecord($commission);
        try {
            $commission->delete();
            return back()->with('success', 'Comisión eliminada exitosamente.');
        } catch (\Throwable $e) {
            Log::error('Error al eliminar comisión', ['id' => $commission->id, 'message' => $e->getMessage()]);
            return back()->with('error', 'No fue posible eliminar la comisión.');
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
