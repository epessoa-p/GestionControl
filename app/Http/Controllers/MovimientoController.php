<?php

namespace App\Http\Controllers;

use App\Models\AccountPayable;
use App\Models\Branch;
use App\Models\CashMovement;
use App\Models\CashSession;
use App\Models\SaleInstallment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MovimientoController extends Controller
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
        $period    = $request->period ?? 'mes';
        $dateFrom  = $request->date_from;
        $dateTo    = $request->date_to;
        $tab       = $request->tab ?? 'todos';

        [$dateFrom, $dateTo] = $this->resolveDateRange($period, $dateFrom, $dateTo);

        $branches = Branch::where('company_id', $companyId)->orderBy('name')->get();

        // Base query: movements linked to this company via cash_registers
        $baseQuery = CashMovement::with(['cashSession.cashRegister.branch', 'createdBy'])
            ->whereHas('cashSession.cashRegister', function ($q) use ($companyId, $branchId) {
                $q->where('company_id', $companyId);
                if ($branchId) {
                    $q->where('branch_id', $branchId);
                }
            });

        if ($dateFrom) {
            $baseQuery->whereDate('movement_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $baseQuery->whereDate('movement_date', '<=', $dateTo);
        }

        // KPI totals for the active period/branch filter
        $kpiQuery      = clone $baseQuery;
        $totalIngresos = (float) (clone $kpiQuery)->where('type', 'income')->sum('amount');
        $totalEgresos  = (float) (clone $kpiQuery)->where('type', 'expense')->sum('amount');
        $balance       = $totalIngresos - $totalEgresos;

        // Historical KPIs (no date filter, all branches) — shown in header
        $historicBase      = CashMovement::whereHas('cashSession.cashRegister', fn($q) => $q->where('company_id', $companyId));
        $historicIngresos  = (float) (clone $historicBase)->where('type', 'income')->sum('amount');
        $historicEgresos   = (float) (clone $historicBase)->where('type', 'expense')->sum('amount');
        $historicBalance   = $historicIngresos - $historicEgresos;

        // "Por cobrar" count: pending sale installments
        $porCobrarCount = SaleInstallment::whereHas('sale', function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })->where('status', 'pendiente')->count();

        // "Por pagar" count: pending/overdue accounts payable
        $porPagarCount = AccountPayable::where('company_id', $companyId)
            ->whereIn('status', ['pendiente', 'pago_parcial', 'vencida'])
            ->count();

        // Filter movements by tab
        $movQuery = clone $baseQuery;
        if ($tab === 'ingresos') {
            $movQuery->where('type', 'income');
        } elseif ($tab === 'egresos') {
            $movQuery->where('type', 'expense');
        }

        $movements = $movQuery->orderByDesc('movement_date')->orderByDesc('id')->paginate(25)->appends($request->query());

        $ingresosCount = (clone $baseQuery)->where('type', 'income')->count();
        $egresosCount  = (clone $baseQuery)->where('type', 'expense')->count();

        // Cash sessions for "Cierres de caja" sub-tab — all statuses (open + closed)
        $cashSessions = CashSession::with(['cashRegister.branch', 'openedBy', 'closedBy'])
            ->whereHas('cashRegister', function ($q) use ($companyId, $branchId) {
                $q->where('company_id', $companyId);
                if ($branchId) {
                    $q->where('branch_id', $branchId);
                }
            })
            ->when($dateFrom, fn($q) => $q->whereDate('opened_at', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('opened_at', '<=', $dateTo))
            ->orderByDesc('opened_at')
            ->paginate(15);

        return view('movimientos.index', compact(
            'movements', 'cashSessions',
            'branches', 'branchId',
            'period', 'dateFrom', 'dateTo', 'tab',
            'totalIngresos', 'totalEgresos', 'balance',
            'historicIngresos', 'historicEgresos', 'historicBalance',
            'ingresosCount', 'egresosCount', 'porCobrarCount', 'porPagarCount'
        ));
    }

    private function resolveDateRange(string $period, ?string $from, ?string $to): array
    {
        return match ($period) {
            'dia'    => [now()->toDateString(), now()->toDateString()],
            'semana' => [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()],
            'mes'    => [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()],
            'todo'   => [null, null],
            'rango'  => [$from, $to],
            default  => [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()],
        };
    }
}
