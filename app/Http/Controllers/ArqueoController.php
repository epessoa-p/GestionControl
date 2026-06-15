<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\CashSession;
use Illuminate\Http\Request;

class ArqueoController extends Controller
{
    private function getCompanyId(): ?int
    {
        $user = auth()->user();
        if ($user->is_super_admin) {
            return session('current_company_id');
        }
        return $user->getCurrentCompany()?->id;
    }

    public function index(Request $request)
    {
        $companyId = $this->getCompanyId();
        $branchId  = $request->branch_id;
        $month     = $request->month; // formato Y-m (ej: 2025-06)
        $search    = $request->search;

        $branches = Branch::where('company_id', $companyId)->orderBy('name')->get();

        $query = CashSession::with(['cashRegister.branch', 'openedBy', 'closedBy'])
            ->whereHas('cashRegister', function ($q) use ($companyId, $branchId) {
                $q->where('company_id', $companyId);
                if ($branchId) {
                    $q->where('branch_id', $branchId);
                }
            })
            ->where('status', 'closed');

        if ($month) {
            $query->whereYear('closed_at', substr($month, 0, 4))
                  ->whereMonth('closed_at', substr($month, 5, 2));
        }

        if ($search) {
            $query->whereHas('cashRegister', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $arqueos = $query->orderByDesc('closed_at')->paginate(20)->appends($request->query());

        return view('arqueos.index', compact('arqueos', 'branches', 'branchId', 'month', 'search'));
    }
}
