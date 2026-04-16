@extends('layouts.app')

@section('title', 'Dashboard - Sistema CRM')

@section('page')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h4 class="fw-bold mb-1">Dashboard</h4>
            <p class="text-muted mb-0">Resumen general — {{ $company?->name ?? 'Sin empresa activa' }}</p>
        </div>
    </div>

    <!-- KPI Cards Row 1 -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="kpi-card">
                <div class="kpi-body">
                    <div>
                        <div class="kpi-value">${{ number_format($totalSalesMonth, 2) }}</div>
                        <div class="kpi-label">Ventas del mes</div>
                        <div class="kpi-trend text-primary"><i class="bi bi-receipt"></i> {{ $totalSalesCount }} ventas</div>
                    </div>
                    <div class="kpi-icon" style="background: #0d6efd;"><i class="bi bi-cart3"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="kpi-card">
                <div class="kpi-body">
                    <div>
                        <div class="kpi-value">{{ $pendingSales }}</div>
                        <div class="kpi-label">Ventas pendientes</div>
                        <div class="kpi-trend text-warning"><i class="bi bi-clock"></i> Por completar</div>
                    </div>
                    <div class="kpi-icon" style="background: #ffc107;"><i class="bi bi-hourglass-split"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="kpi-card">
                <div class="kpi-body">
                    <div>
                        <div class="kpi-value">${{ number_format($pendingCommissions, 2) }}</div>
                        <div class="kpi-label">Comisiones pendientes</div>
                        <div class="kpi-trend text-danger"><i class="bi bi-percent"></i> Por pagar</div>
                    </div>
                    <div class="kpi-icon" style="background: #dc3545;"><i class="bi bi-cash-coin"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="kpi-card">
                <div class="kpi-body">
                    <div>
                        <div class="kpi-value">{{ $lowStockCount }}</div>
                        <div class="kpi-label">Productos stock bajo</div>
                        <div class="kpi-trend {{ $lowStockCount > 0 ? 'text-danger' : 'text-success' }}"><i class="bi bi-box-seam"></i> de {{ $totalProducts }} totales</div>
                    </div>
                    <div class="kpi-icon" style="background: {{ $lowStockCount > 0 ? '#dc3545' : '#198754' }};"><i class="bi bi-exclamation-triangle"></i></div>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Cards Row 2 -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="kpi-card">
                <div class="kpi-body">
                    <div>
                        <div class="kpi-value">{{ $openTrackings }}</div>
                        <div class="kpi-label">Seguimientos abiertos</div>
                        <div class="kpi-trend text-info"><i class="bi bi-journal-text"></i> Activos</div>
                    </div>
                    <div class="kpi-icon" style="background: #0dcaf0;"><i class="bi bi-journal-check"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="kpi-card">
                <div class="kpi-body">
                    <div>
                        <div class="kpi-value">{{ $activeProductions }}</div>
                        <div class="kpi-label">Producciones activas</div>
                        <div class="kpi-trend text-secondary"><i class="bi bi-gear"></i> En proceso</div>
                    </div>
                    <div class="kpi-icon" style="background: #6c757d;"><i class="bi bi-gear-wide-connected"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="kpi-card">
                <div class="kpi-body">
                    <div>
                        <div class="kpi-value">{{ $openCashSessions }}</div>
                        <div class="kpi-label">Cajas abiertas</div>
                        <div class="kpi-trend text-success"><i class="bi bi-cash-stack"></i> Sesiones</div>
                    </div>
                    <div class="kpi-icon" style="background: #198754;"><i class="bi bi-safe"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="kpi-card">
                <div class="kpi-body">
                    <div>
                        <div class="kpi-value">{{ $totalUsers }}</div>
                        <div class="kpi-label">Usuarios</div>
                        <div class="kpi-trend text-primary"><i class="bi bi-people"></i> {{ $totalCompanies }} empresa(s)</div>
                    </div>
                    <div class="kpi-icon" style="background: #7c4dff;"><i class="bi bi-person-gear"></i></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><strong>Ventas mensuales (últimos 6 meses)</strong></div>
                <div class="card-body">
                    <div id="salesChart" style="width: 100%; height: 350px;"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><strong>Métodos de pago (mes actual)</strong></div>
                <div class="card-body">
                    <div id="paymentChart" style="width: 100%; height: 350px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .kpi-card {
        background: #fff;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        border: 0;
    }
    .kpi-body {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }
    .kpi-value {
        font-size: 1.8rem;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 4px;
    }
    .kpi-label {
        font-size: 0.82rem;
        color: #777;
        margin-bottom: 6px;
    }
    .kpi-trend {
        font-size: 0.75rem;
    }
    .kpi-icon {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        display: grid;
        place-items: center;
        color: #fff;
        font-size: 1.2rem;
        flex-shrink: 0;
    }
</style>
@endpush

@push('scripts')
<!-- amCharts 5 -->
<script src="https://cdn.amcharts.com/lib/5/index.js"></script>
<script src="https://cdn.amcharts.com/lib/5/xy.js"></script>
<script src="https://cdn.amcharts.com/lib/5/percent.js"></script>
<script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ─── Sales Bar Chart ───
    var salesRoot = am5.Root.new("salesChart");
    salesRoot.setThemes([am5themes_Animated.new(salesRoot)]);

    var salesChart = salesRoot.container.children.push(am5xy.XYChart.new(salesRoot, {
        panX: false, panY: false, wheelX: "none", wheelY: "none",
        paddingLeft: 0
    }));

    var xAxis = salesChart.xAxes.push(am5xy.CategoryAxis.new(salesRoot, {
        categoryField: "month",
        renderer: am5xy.AxisRendererX.new(salesRoot, { minGridDistance: 30 }),
    }));

    var yAxis = salesChart.yAxes.push(am5xy.ValueAxis.new(salesRoot, {
        renderer: am5xy.AxisRendererY.new(salesRoot, {}),
        numberFormat: "'$'#,###.##"
    }));

    var series = salesChart.series.push(am5xy.ColumnSeries.new(salesRoot, {
        name: "Ventas",
        xAxis: xAxis,
        yAxis: yAxis,
        valueYField: "total",
        categoryXField: "month",
        tooltip: am5.Tooltip.new(salesRoot, { labelText: "${valueY}" })
    }));

    series.columns.template.setAll({
        cornerRadiusTL: 5, cornerRadiusTR: 5,
        strokeOpacity: 0
    });

    series.columns.template.adapters.add("fill", function(fill, target) {
        return salesChart.get("colors").getIndex(series.columns.indexOf(target));
    });

    var salesData = @json($salesChartData);
    xAxis.data.setAll(salesData);
    series.data.setAll(salesData);
    series.appear(1000);
    salesChart.appear(1000, 100);

    // ─── Payment Pie Chart ───
    var paymentData = @json($paymentMethodData);
    if (paymentData.length > 0) {
        var pieRoot = am5.Root.new("paymentChart");
        pieRoot.setThemes([am5themes_Animated.new(pieRoot)]);

        var pieChart = pieRoot.container.children.push(am5percent.PieChart.new(pieRoot, {
            layout: pieRoot.verticalLayout,
            innerRadius: am5.percent(50)
        }));

        var pieSeries = pieChart.series.push(am5percent.PieSeries.new(pieRoot, {
            valueField: "total",
            categoryField: "method",
            tooltip: am5.Tooltip.new(pieRoot, { labelText: "{category}: ${value}" })
        }));

        pieSeries.labels.template.set("fontSize", 12);
        pieSeries.data.setAll(paymentData);
        pieSeries.appear(1000, 100);
    } else {
        document.getElementById('paymentChart').innerHTML = '<div class="text-center text-muted py-5">Sin ventas este mes</div>';
    }
});
</script>
@endpush
@endsection
