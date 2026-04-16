<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Commission;
use App\Models\Production;
use App\Models\CashSession;
use App\Models\Tracking;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $company = $user->getCurrentCompany();
        $companyId = $company?->id;

        if ($user->is_super_admin) {
            $totalUsers = User::count();
            $totalCompanies = Company::count();
        } else {
            $totalUsers = $company ? $company->users()->count() : 0;
            $totalCompanies = 1;
        }

        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();

        // Sales KPIs
        $salesQuery = Sale::where('company_id', $companyId);
        $totalSalesMonth = (clone $salesQuery)->where('status', '!=', 'cancelled')
            ->whereBetween('sale_date', [$startOfMonth, $now])->sum('total');
        $totalSalesCount = (clone $salesQuery)->where('status', '!=', 'cancelled')
            ->whereBetween('sale_date', [$startOfMonth, $now])->count();
        $pendingSales = (clone $salesQuery)->where('status', 'pending')->count();

        // Commissions
        $pendingCommissions = Commission::where('company_id', $companyId)->where('status', 'pending')->sum('amount');

        // Inventory
        $lowStockCount = Product::where('company_id', $companyId)->where('active', true)
            ->whereColumn('current_stock', '<=', 'min_stock')->where('min_stock', '>', 0)->count();
        $totalProducts = Product::where('company_id', $companyId)->where('active', true)->count();

        // Productions
        $activeProductions = Production::where('company_id', $companyId)->where('status', 'in_progress')->count();

        // Trackings
        $openTrackings = Tracking::where('company_id', $companyId)->whereIn('status', ['open', 'in_progress'])->count();

        // Cash sessions
        $openCashSessions = CashSession::whereHas('cashRegister', fn($q) => $q->where('company_id', $companyId))
            ->where('status', 'open')->count();

        // Monthly sales chart data (last 6 months)
        $salesChartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $amount = Sale::where('company_id', $companyId)
                ->where('status', '!=', 'cancelled')
                ->whereYear('sale_date', $month->year)
                ->whereMonth('sale_date', $month->month)
                ->sum('total');
            $salesChartData[] = ['month' => $month->translatedFormat('M Y'), 'total' => round($amount, 2)];
        }

        // Sales by payment method
        $paymentMethodData = Sale::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->whereBetween('sale_date', [$startOfMonth, $now])
            ->selectRaw('payment_method, SUM(total) as total')
            ->groupBy('payment_method')
            ->get()
            ->map(fn($r) => [
                'method' => Sale::PAYMENT_LABELS[$r->payment_method] ?? $r->payment_method,
                'total' => round($r->total, 2),
            ]);

        return view('dashboard.index', compact(
            'totalUsers',
            'totalCompanies',
            'company',
            'totalSalesMonth',
            'totalSalesCount',
            'pendingSales',
            'pendingCommissions',
            'lowStockCount',
            'totalProducts',
            'activeProductions',
            'openTrackings',
            'openCashSessions',
            'salesChartData',
            'paymentMethodData',
        ));
    }
}
