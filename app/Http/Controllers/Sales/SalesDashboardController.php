<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\SaleInstallment;
use Carbon\Carbon;

class SalesDashboardController extends Controller
{
    public function index()
    {
        $companyId = $this->getCompanyId();
        $now          = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfDay   = $now->copy()->startOfDay();

        $base = fn() => Sale::where('company_id', $companyId)->where('status', '!=', 'cancelled');

        $salesToday      = (float) (clone $base())->whereDate('sale_date', $startOfDay)->sum('total');
        $salesMonth      = (float) (clone $base())->whereBetween('sale_date', [$startOfMonth, $now])->sum('total');
        $salesMonthCount = (int) (clone $base())->whereBetween('sale_date', [$startOfMonth, $now])->count();
        $avgTicket       = $salesMonthCount > 0 ? $salesMonth / $salesMonthCount : 0;

        // Por cobrar (cuotas pendientes/parciales/vencidas)
        $pendingReceivables = (float) SaleInstallment::whereHas('sale', fn($q) => $q->where('company_id', $companyId))
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->selectRaw('SUM(amount - paid_amount) as total')->value('total');

        // Top 5 productos del mes (por monto)
        $topProducts = SaleDetail::query()
            ->join('sales', 'sales.id', '=', 'sale_details.sale_id')
            ->join('products', 'products.id', '=', 'sale_details.product_id')
            ->where('sales.company_id', $companyId)
            ->where('sales.status', '!=', 'cancelled')
            ->whereBetween('sales.sale_date', [$startOfMonth, $now])
            ->whereNull('sales.deleted_at')
            ->groupBy('products.id', 'products.name')
            ->selectRaw('products.name as name, SUM(sale_details.quantity) as qty, SUM(sale_details.total) as total')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Serie últimos 6 meses
        $salesChartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month  = $now->copy()->subMonths($i);
            $amount = Sale::where('company_id', $companyId)
                ->where('status', '!=', 'cancelled')
                ->whereYear('sale_date', $month->year)
                ->whereMonth('sale_date', $month->month)
                ->sum('total');
            $salesChartData[] = ['month' => $month->translatedFormat('M Y'), 'total' => round($amount, 2)];
        }

        // Por método de pago (mes)
        $paymentMethodData = Sale::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->whereBetween('sale_date', [$startOfMonth, $now])
            ->selectRaw('payment_method, SUM(total) as total')
            ->groupBy('payment_method')
            ->get()
            ->map(fn($r) => [
                'method' => Sale::PAYMENT_LABELS[$r->payment_method] ?? $r->payment_method,
                'total'  => round($r->total, 2),
            ]);

        $recentSales = Sale::with(['branch', 'createdBy'])
            ->where('company_id', $companyId)
            ->latest('sale_date')->latest('id')
            ->limit(8)->get();

        return view('sales.dashboard', compact(
            'salesToday', 'salesMonth', 'salesMonthCount', 'avgTicket', 'pendingReceivables',
            'topProducts', 'salesChartData', 'paymentMethodData', 'recentSales'
        ));
    }

    private function getCompanyId(): ?int
    {
        $user = auth()->user();
        return $user->is_super_admin
            ? ($user->getCurrentCompany()?->id ?? session('current_company_id'))
            : $user->getCurrentCompany()?->id;
    }
}
