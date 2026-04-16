@extends('layouts.app')
@section('title', 'Reporte de Ventas')
@section('page')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">Reporte de Ventas</h1>
            <p class="text-muted mb-0">Ventas realizadas en el período seleccionado.</p>
        </div>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">Volver</a>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Desde</label>
                    <input type="date" name="from" class="form-control" value="{{ $from }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Hasta</label>
                    <input type="date" name="to" class="form-control" value="{{ $to }}">
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <button class="btn btn-primary"><i class="bi bi-search"></i> Filtrar</button>
                    <a href="{{ route('reports.sales', ['from' => $from, 'to' => $to, 'export' => 'xlsx']) }}" class="btn btn-success"><i class="bi bi-file-earmark-excel"></i> Exportar Excel</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary bg-opacity-10">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Total ventas</h6>
                    <h3 class="fw-bold text-primary mb-0">{{ $sales->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success bg-opacity-10">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Monto total</h6>
                    <h3 class="fw-bold text-success mb-0">${{ number_format($sales->sum('total'), 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info bg-opacity-10">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Subtotal</h6>
                    <h3 class="fw-bold text-info mb-0">${{ number_format($sales->sum('subtotal'), 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning bg-opacity-10">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Impuestos</h6>
                    <h3 class="fw-bold text-warning mb-0">${{ number_format($sales->sum('tax'), 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Número</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Promotor</th>
                            <th>Método pago</th>
                            <th class="text-end">Total</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $i => $sale)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $sale->sale_number }}</td>
                                <td>{{ $sale->sale_date?->format('d/m/Y') }}</td>
                                <td>{{ $sale->client_name ?: '-' }}</td>
                                <td>{{ $sale->promoter?->name ?? '-' }}</td>
                                <td>{{ \App\Models\Sale::PAYMENT_LABELS[$sale->payment_method] ?? $sale->payment_method }}</td>
                                <td class="text-end">${{ number_format($sale->total, 2) }}</td>
                                <td><span class="badge bg-{{ \App\Models\Sale::STATUS_COLORS[$sale->status] ?? 'secondary' }}">{{ \App\Models\Sale::STATUS_LABELS[$sale->status] ?? $sale->status }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">No hay ventas en este período.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
