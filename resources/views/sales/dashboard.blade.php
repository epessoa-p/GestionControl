@extends('layouts.app')

@section('title', 'Dashboard de ventas')

@include('layouts._compact_style')

@section('page')
<div class="view-compact container-fluid">

    <div class="d-flex justify-content-between align-items-start mb-3 page-head">
        <div>
            <h1 class="mb-1"><i class="bi bi-graph-up text-success me-2"></i>Dashboard de ventas</h1>
            <p class="text-muted mb-0">Resumen comercial del mes en curso.</p>
        </div>
        <a href="{{ route('pos.index') }}" class="btn btn-success"><i class="bi bi-upc-scan me-1"></i> Ir al POS</a>
    </div>

    {{-- KPIs --}}
    <div class="row g-2 mb-3">
        <div class="col-6 col-xl">
            <div class="kpi-card bg-white shadow-sm">
                <div class="kpi-body d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-value">${{ number_format($salesToday, 2) }}</div>
                        <div class="kpi-label">Ventas de hoy</div>
                    </div>
                    <div class="kpi-icon" style="background:#198754;"><i class="bi bi-calendar-day"></i></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl">
            <div class="kpi-card bg-white shadow-sm">
                <div class="kpi-body d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-value">${{ number_format($salesMonth, 2) }}</div>
                        <div class="kpi-label">Ventas del mes</div>
                        <div class="kpi-trend text-primary"><i class="bi bi-receipt"></i> {{ $salesMonthCount }} ventas</div>
                    </div>
                    <div class="kpi-icon" style="background:#0d6efd;"><i class="bi bi-cart3"></i></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl">
            <div class="kpi-card bg-white shadow-sm">
                <div class="kpi-body d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-value">${{ number_format($avgTicket, 2) }}</div>
                        <div class="kpi-label">Ticket promedio</div>
                    </div>
                    <div class="kpi-icon" style="background:#6f42c1;"><i class="bi bi-tag"></i></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl">
            <div class="kpi-card bg-white shadow-sm">
                <div class="kpi-body d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-value">${{ number_format($pendingReceivables, 2) }}</div>
                        <div class="kpi-label">Por cobrar</div>
                        <div class="kpi-trend text-info"><a href="{{ route('receivables.index') }}" class="text-decoration-none">Ver cuotas</a></div>
                    </div>
                    <div class="kpi-icon" style="background:#0dcaf0;"><i class="bi bi-cash-coin"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><strong>Ventas mensuales (últimos 6 meses)</strong></div>
                <div class="card-body"><div id="salesChart" style="width:100%;height:300px;"></div></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><strong>Métodos de pago (mes)</strong></div>
                <div class="card-body"><div id="paymentChart" style="width:100%;height:300px;"></div></div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><strong>Top productos del mes</strong></div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Producto</th><th class="text-end">Cant.</th><th class="text-end">Total</th></tr></thead>
                        <tbody>
                            @forelse($topProducts as $p)
                                <tr><td>{{ $p->name }}</td><td class="text-end">{{ number_format($p->qty, 2) }}</td><td class="text-end">${{ number_format($p->total, 2) }}</td></tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-4">Sin ventas este mes</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <strong>Ventas recientes</strong>
                    <a href="{{ route('sales.index') }}" class="btn btn-sm btn-outline-secondary">Ver todas</a>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Número</th><th>Fecha</th><th class="text-end">Total</th><th>Estado</th></tr></thead>
                        <tbody>
                            @forelse($recentSales as $s)
                                <tr>
                                    <td><a href="{{ route('sales.show', $s) }}" class="text-decoration-none fw-semibold">{{ $s->sale_number }}</a></td>
                                    <td>{{ $s->sale_date?->format('d/m/Y') }}</td>
                                    <td class="text-end">${{ number_format($s->total, 2) }}</td>
                                    <td><span class="badge bg-{{ \App\Models\Sale::STATUS_COLORS[$s->status] ?? 'secondary' }}">{{ \App\Models\Sale::STATUS_LABELS[$s->status] ?? $s->status }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">Sin ventas registradas</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .kpi-card { background:#fff; border-radius:10px; padding:14px; }
    .kpi-body { display:flex; justify-content:space-between; align-items:flex-start; }
    .kpi-value { font-size:1.35rem; font-weight:700; line-height:1; margin-bottom:4px; }
    .kpi-label { font-size:.72rem; color:#777; }
    .kpi-trend { font-size:.68rem; margin-top:4px; }
    .kpi-icon { width:38px; height:38px; border-radius:10px; display:grid; place-items:center; color:#fff; font-size:1rem; flex-shrink:0; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.amcharts.com/lib/5/index.js"></script>
<script src="https://cdn.amcharts.com/lib/5/xy.js"></script>
<script src="https://cdn.amcharts.com/lib/5/percent.js"></script>
<script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var salesRoot = am5.Root.new("salesChart");
    salesRoot.setThemes([am5themes_Animated.new(salesRoot)]);
    var salesChart = salesRoot.container.children.push(am5xy.XYChart.new(salesRoot, { panX:false, panY:false, wheelX:"none", wheelY:"none", paddingLeft:0 }));
    var xAxis = salesChart.xAxes.push(am5xy.CategoryAxis.new(salesRoot, { categoryField:"month", renderer: am5xy.AxisRendererX.new(salesRoot, { minGridDistance:30 }) }));
    var yAxis = salesChart.yAxes.push(am5xy.ValueAxis.new(salesRoot, { renderer: am5xy.AxisRendererY.new(salesRoot, {}), numberFormat:"'$'#,###.##" }));
    var series = salesChart.series.push(am5xy.ColumnSeries.new(salesRoot, { name:"Ventas", xAxis:xAxis, yAxis:yAxis, valueYField:"total", categoryXField:"month", tooltip: am5.Tooltip.new(salesRoot, { labelText:"${valueY}" }) }));
    series.columns.template.setAll({ cornerRadiusTL:5, cornerRadiusTR:5, strokeOpacity:0, fill: am5.color(0x198754) });
    var salesData = @json($salesChartData);
    xAxis.data.setAll(salesData); series.data.setAll(salesData); series.appear(1000); salesChart.appear(1000, 100);

    var paymentData = @json($paymentMethodData);
    if (paymentData.length > 0) {
        var pieRoot = am5.Root.new("paymentChart");
        pieRoot.setThemes([am5themes_Animated.new(pieRoot)]);
        var pieChart = pieRoot.container.children.push(am5percent.PieChart.new(pieRoot, { layout: pieRoot.verticalLayout, innerRadius: am5.percent(50) }));
        var pieSeries = pieChart.series.push(am5percent.PieSeries.new(pieRoot, { valueField:"total", categoryField:"method", tooltip: am5.Tooltip.new(pieRoot, { labelText:"{category}: ${value}" }) }));
        pieSeries.labels.template.set("fontSize", 11);
        pieSeries.data.setAll(paymentData); pieSeries.appear(1000, 100);
    } else {
        document.getElementById('paymentChart').innerHTML = '<div class="text-center text-muted py-5">Sin ventas este mes</div>';
    }
});
</script>
@endpush
@endsection
