<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\SaleInstallment;
use Illuminate\Http\Request;

class ReceivablesController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $this->getCompanyId();

        $query = SaleInstallment::with(['sale.client'])
            ->whereHas('sale', fn($q) => $q->where('company_id', $companyId)->whereNull('deleted_at'));

        $status = $request->input('status', 'open'); // open | pending | partial | overdue | paid | all
        if ($status === 'open') {
            $query->whereIn('status', ['pending', 'partial', 'overdue']);
        } elseif ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($request->filled('q')) {
            $search = $request->q;
            $query->whereHas('sale', function ($q) use ($search) {
                $q->where('sale_number', 'like', "%{$search}%")
                  ->orWhere('client_name', 'like', "%{$search}%");
            });
        }

        // KPIs sobre todo lo pendiente (independiente del filtro de búsqueda)
        $openBase = SaleInstallment::whereHas('sale', fn($q) => $q->where('company_id', $companyId)->whereNull('deleted_at'))
            ->whereIn('status', ['pending', 'partial', 'overdue']);
        $totalPorCobrar = (float) (clone $openBase)->selectRaw('SUM(amount - paid_amount) as t')->value('t');
        $totalVencido   = (float) (clone $openBase)->where(function ($q) {
            $q->where('status', 'overdue')->orWhere('due_date', '<', now()->toDateString());
        })->selectRaw('SUM(amount - paid_amount) as t')->value('t');

        $installments = $query->orderBy('due_date')->paginate(20)->withQueryString();

        return view('sales.receivables.index', compact('installments', 'status', 'totalPorCobrar', 'totalVencido'));
    }

    private function getCompanyId(): ?int
    {
        $user = auth()->user();
        return $user->is_super_admin
            ? ($user->getCurrentCompany()?->id ?? session('current_company_id'))
            : $user->getCurrentCompany()?->id;
    }
}
