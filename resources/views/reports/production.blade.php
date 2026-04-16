@extends('layouts.app')
@section('title', 'Reporte de Producción')
@section('page')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">Reporte de Producción</h1>
            <p class="text-muted mb-0">Órdenes de producción en el período seleccionado.</p>
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
                    <a href="{{ route('reports.production', ['from' => $from, 'to' => $to, 'export' => 'xlsx']) }}" class="btn btn-success"><i class="bi bi-file-earmark-excel"></i> Exportar Excel</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary bg-opacity-10">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Total órdenes</h6>
                    <h3 class="fw-bold text-primary mb-0">{{ $productions->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success bg-opacity-10">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Completadas</h6>
                    <h3 class="fw-bold text-success mb-0">{{ $productions->where('status', 'completed')->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning bg-opacity-10">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">En proceso</h6>
                    <h3 class="fw-bold text-warning mb-0">{{ $productions->where('status', 'in_progress')->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info bg-opacity-10">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Costo total</h6>
                    <h3 class="fw-bold text-info mb-0">${{ number_format($productions->sum('total_cost'), 2) }}</h3>
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
                            <th>Lote</th>
                            <th>Producto</th>
                            <th class="text-end">Cantidad</th>
                            <th>Fecha</th>
                            <th class="text-end">Costo total</th>
                            <th>Estado</th>
                            <th>Materiales</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($productions as $prod)
                            <tr>
                                <td>{{ $prod->batch_number }}</td>
                                <td>{{ $prod->product?->name }}</td>
                                <td class="text-end">{{ number_format($prod->quantity_produced, 2) }}</td>
                                <td>{{ $prod->production_date?->format('d/m/Y') }}</td>
                                <td class="text-end">${{ number_format($prod->total_cost, 2) }}</td>
                                <td><span class="badge bg-{{ \App\Models\Production::STATUS_COLORS[$prod->status] ?? 'secondary' }}">{{ \App\Models\Production::STATUS_LABELS[$prod->status] ?? $prod->status }}</span></td>
                                <td>
                                    @if($prod->materials->count())
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#mat-{{ $prod->id }}">
                                            <i class="bi bi-list"></i> {{ $prod->materials->count() }}
                                        </button>
                                    @else
                                        <span class="text-muted">0</span>
                                    @endif
                                </td>
                            </tr>
                            @if($prod->materials->count())
                                <tr class="collapse" id="mat-{{ $prod->id }}">
                                    <td colspan="7" class="p-0">
                                        <table class="table table-sm table-bordered mb-0 ms-4" style="width: calc(100% - 2rem);">
                                            <thead class="table-light"><tr><th>Material</th><th class="text-end">Cantidad</th><th class="text-end">Costo unit.</th><th class="text-end">Total</th></tr></thead>
                                            <tbody>
                                                @foreach($prod->materials as $mat)
                                                    <tr>
                                                        <td>{{ $mat->product?->name }}</td>
                                                        <td class="text-end">{{ number_format($mat->quantity, 2) }}</td>
                                                        <td class="text-end">${{ number_format($mat->unit_cost, 2) }}</td>
                                                        <td class="text-end">${{ number_format($mat->total_cost, 2) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">No hay producciones en este período.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
