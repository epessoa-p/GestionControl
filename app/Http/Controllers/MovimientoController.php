<?php

namespace App\Http\Controllers;

use App\Models\AccountPayable;
use App\Models\Branch;
use App\Models\CashMovement;
use App\Models\CashSession;
use App\Models\SaleInstallment;
use App\Models\TreasuryAccount;
use App\Models\TreasuryMovement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $companyId          = $this->getCompanyId();
        $branchId           = $request->branch_id;
        $treasuryAccountId  = $request->treasury_account_id;
        $period             = $request->period ?? 'mes';
        $dateFrom           = $request->date_from;
        $dateTo             = $request->date_to;
        $tab                = $request->tab ?? 'todos';

        // Sucursal y cuenta de tesorería son mutuamente excluyentes como filtro de origen.
        if ($treasuryAccountId) {
            $branchId = null;
        }

        [$dateFrom, $dateTo] = $this->resolveDateRange($period, $dateFrom, $dateTo);

        $branches         = Branch::where('company_id', $companyId)->orderBy('name')->get();
        $treasuryAccounts = TreasuryAccount::where('company_id', $companyId)
            ->where('active', true)->orderBy('name')->get();

        // ── Ledger unificado: caja (cash_movements) + tesorería (treasury_movements) ──
        // - Filtro por cuenta de tesorería → solo esa cuenta.
        // - Filtro por sucursal → solo caja de esa sucursal.
        // - Sin filtro → caja (todas) + tesorería (todas).
        $treasurySub = function () use ($companyId, $treasuryAccountId, $dateFrom, $dateTo) {
            return DB::table('treasury_movements as tm')
                ->join('treasury_accounts as ta', 'ta.id', '=', 'tm.treasury_account_id')
                ->where('tm.company_id', $companyId)
                ->whereNull('tm.deleted_at')
                ->when($treasuryAccountId, fn($q) => $q->where('tm.treasury_account_id', $treasuryAccountId))
                ->when($dateFrom, fn($q) => $q->whereDate('tm.movement_date', '>=', $dateFrom))
                ->when($dateTo, fn($q) => $q->whereDate('tm.movement_date', '<=', $dateTo))
                ->selectRaw("'tesoreria' as origin_type,
                    CASE WHEN tm.type = 'entrada' THEN 'income' ELSE 'expense' END as type,
                    tm.category as category, tm.description as concept,
                    NULL as payment_method, tm.amount as amount, tm.movement_date as movement_date,
                    ta.name as origin_name, NULL as origin_branch");
        };

        $buildLedger = function () use ($companyId, $branchId, $treasuryAccountId, $dateFrom, $dateTo, $treasurySub) {
            // Solo tesorería de la cuenta seleccionada
            if ($treasuryAccountId) {
                return DB::query()->fromSub($treasurySub(), 'm');
            }

            $cash = DB::table('cash_movements as cm')
                ->join('cash_sessions as cs', 'cs.id', '=', 'cm.cash_session_id')
                ->join('cash_registers as cr', 'cr.id', '=', 'cs.cash_register_id')
                ->leftJoin('branches as b', 'b.id', '=', 'cr.branch_id')
                ->where('cr.company_id', $companyId)
                ->whereNull('cm.deleted_at')
                ->when($branchId, fn($q) => $q->where('cr.branch_id', $branchId))
                ->when($dateFrom, fn($q) => $q->whereDate('cm.movement_date', '>=', $dateFrom))
                ->when($dateTo, fn($q) => $q->whereDate('cm.movement_date', '<=', $dateTo))
                ->selectRaw("'caja' as origin_type, cm.type as type, cm.category as category, cm.concept as concept,
                    cm.payment_method as payment_method, cm.amount as amount, cm.movement_date as movement_date,
                    cr.name as origin_name, b.name as origin_branch");

            // Sin filtro de sucursal → agregar tesorería (todas las cuentas)
            if (!$branchId) {
                $cash->unionAll($treasurySub());
            }

            return DB::query()->fromSub($cash, 'm');
        };

        // KPI totales del período/sucursal
        $totalIngresos = (float) $buildLedger()->where('type', 'income')->sum('amount');
        $totalEgresos  = (float) $buildLedger()->where('type', 'expense')->sum('amount');
        $balance       = $totalIngresos - $totalEgresos;

        // KPIs históricos (sin filtro): caja + tesorería de toda la empresa
        $histCashBase = fn() => CashMovement::whereHas('cashSession.cashRegister', fn($q) => $q->where('company_id', $companyId));
        $historicIngresos = (float) $histCashBase()->where('type', 'income')->sum('amount')
            + (float) TreasuryMovement::where('company_id', $companyId)->where('type', 'entrada')->sum('amount');
        $historicEgresos  = (float) $histCashBase()->where('type', 'expense')->sum('amount')
            + (float) TreasuryMovement::where('company_id', $companyId)->where('type', 'salida')->sum('amount');
        $historicBalance  = $historicIngresos - $historicEgresos;

        // "Por cobrar" count: cuotas de venta pendientes
        $porCobrarCount = SaleInstallment::whereHas('sale', fn($q) => $q->where('company_id', $companyId))
            ->whereIn('status', ['pending', 'partial', 'overdue'])->count();

        // "Por pagar" count: cuentas por pagar pendientes/vencidas
        $porPagarCount = AccountPayable::where('company_id', $companyId)
            ->whereIn('status', ['pendiente', 'pago_parcial', 'vencida'])
            ->count();

        // Lista de movimientos (filtrada por sub-tab)
        $listQuery = $buildLedger();
        if ($tab === 'ingresos') {
            $listQuery->where('type', 'income');
        } elseif ($tab === 'egresos') {
            $listQuery->where('type', 'expense');
        }
        $movements = $listQuery->orderByDesc('movement_date')->paginate(25)->withQueryString();

        $ingresosCount = (int) $buildLedger()->where('type', 'income')->count();
        $egresosCount  = (int) $buildLedger()->where('type', 'expense')->count();

        // Cash sessions for "Cierres de caja" sub-tab.
        // Las sesiones ABIERTAS siempre se muestran (representan un cierre pendiente),
        // sin importar el filtro de período. Las CERRADAS respetan el período.
        $cashSessions = CashSession::with(['cashRegister.branch', 'openedBy', 'closedBy'])
            ->whereHas('cashRegister', function ($q) use ($companyId, $branchId) {
                $q->where('company_id', $companyId);
                if ($branchId) {
                    $q->where('branch_id', $branchId);
                }
            })
            ->where(function ($q) use ($dateFrom, $dateTo) {
                $q->where('status', 'open')
                  ->orWhere(function ($q2) use ($dateFrom, $dateTo) {
                      $q2->where('status', 'closed')
                         ->when($dateFrom, fn($x) => $x->whereDate('opened_at', '>=', $dateFrom))
                         ->when($dateTo, fn($x) => $x->whereDate('opened_at', '<=', $dateTo));
                  });
            })
            ->orderByRaw("CASE WHEN status = 'open' THEN 0 ELSE 1 END")
            ->orderByDesc('opened_at')
            ->paginate(15);

        return view('movimientos.index', compact(
            'movements', 'cashSessions',
            'branches', 'branchId',
            'treasuryAccounts', 'treasuryAccountId',
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
