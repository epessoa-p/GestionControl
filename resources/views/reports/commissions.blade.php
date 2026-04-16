@extends('layouts.app')
@section('title', 'Reporte de Comisiones')
@section('page')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">Reporte de Comisiones</h1>
            <p class="text-muted mb-0">Comisiones agrupadas por promotor.</p>
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
                    <a href="{{ route('reports.commissions', ['from' => $from, 'to' => $to, 'export' => 'xlsx']) }}" class="btn btn-success"><i class="bi bi-file-earmark-excel"></i> Exportar Excel</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-primary bg-opacity-10">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Total comisiones</h6>
                    <h3 class="fw-bold text-primary mb-0">${{ number_format($grouped->sum('total'), 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-warning bg-opacity-10">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Pendientes</h6>
                    <h3 class="fw-bold text-warning mb-0">${{ number_format($grouped->sum('pending'), 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-success bg-opacity-10">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Pagadas</h6>
                    <h3 class="fw-bold text-success mb-0">${{ number_format($grouped->sum('paid'), 2) }}</h3>
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
                            <th>Promotor</th>
                            <th class="text-end">Ventas</th>
                            <th class="text-end">Total comisión</th>
                            <th class="text-end">Pendiente</th>
                            <th class="text-end">Pagado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($grouped as $data)
                            <tr>
                                <td>{{ $data['promoter']?->name ?? '-' }}</td>
                                <td class="text-end">{{ $data['count'] }}</td>
                                <td class="text-end">${{ number_format($data['total'], 2) }}</td>
                                <td class="text-end"><span class="text-warning">${{ number_format($data['pending'], 2) }}</span></td>
                                <td class="text-end"><span class="text-success">${{ number_format($data['paid'], 2) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No hay comisiones en este período.</td></tr>
                        @endforelse
                    </tbody>
                    @if($grouped->count())
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td>Totales</td>
                                <td class="text-end">{{ $grouped->sum('count') }}</td>
                                <td class="text-end">${{ number_format($grouped->sum('total'), 2) }}</td>
                                <td class="text-end">${{ number_format($grouped->sum('pending'), 2) }}</td>
                                <td class="text-end">${{ number_format($grouped->sum('paid'), 2) }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
