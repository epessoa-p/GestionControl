@extends('layouts.app')
@section('title', 'Reporte de Movimientos de Caja')
@section('page')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">Movimientos de Caja</h1>
            <p class="text-muted mb-0">Sesiones de caja con montos y diferencias.</p>
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
                    <a href="{{ route('reports.cash-movements', ['from' => $from, 'to' => $to, 'export' => 'xlsx']) }}" class="btn btn-success"><i class="bi bi-file-earmark-excel"></i> Exportar Excel</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Caja</th>
                            <th>Personal</th>
                            <th>Apertura</th>
                            <th>Cierre</th>
                            <th class="text-end">Monto apertura</th>
                            <th class="text-end">Monto cierre</th>
                            <th class="text-end">Esperado</th>
                            <th class="text-end">Diferencia</th>
                            <th>Estado</th>
                            <th>Movimientos</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sessions as $s)
                            <tr>
                                <td>{{ $s->cashRegister?->name }}</td>
                                <td>{{ $s->personal?->full_name ?? '-' }}</td>
                                <td>{{ $s->opened_at?->format('d/m/Y H:i') }}</td>
                                <td>{{ $s->closed_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                <td class="text-end">${{ number_format($s->opening_amount, 2) }}</td>
                                <td class="text-end">{{ $s->closing_amount !== null ? '$' . number_format($s->closing_amount, 2) : '-' }}</td>
                                <td class="text-end">{{ $s->expected_amount !== null ? '$' . number_format($s->expected_amount, 2) : '-' }}</td>
                                <td class="text-end">
                                    @if($s->difference !== null)
                                        <span class="{{ $s->difference < 0 ? 'text-danger' : ($s->difference > 0 ? 'text-warning' : 'text-success') }}">
                                            ${{ number_format($s->difference, 2) }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td><span class="badge bg-{{ $s->status === 'open' ? 'success' : 'secondary' }}">{{ $s->status === 'open' ? 'Abierta' : 'Cerrada' }}</span></td>
                                <td>
                                    @if($s->movements->count())
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#mov-{{ $s->id }}">
                                            <i class="bi bi-list"></i> {{ $s->movements->count() }}
                                        </button>
                                    @else
                                        <span class="text-muted">0</span>
                                    @endif
                                </td>
                            </tr>
                            @if($s->movements->count())
                                <tr class="collapse" id="mov-{{ $s->id }}">
                                    <td colspan="10" class="p-0">
                                        <table class="table table-sm table-bordered mb-0 ms-4" style="width: calc(100% - 2rem);">
                                            <thead class="table-light"><tr><th>Tipo</th><th>Concepto</th><th class="text-end">Monto</th><th>Fecha</th></tr></thead>
                                            <tbody>
                                                @foreach($s->movements as $m)
                                                    <tr>
                                                        <td><span class="badge bg-{{ $m->type === 'income' ? 'success' : 'danger' }}">{{ $m->type === 'income' ? 'Ingreso' : 'Egreso' }}</span></td>
                                                        <td>{{ $m->concept }}</td>
                                                        <td class="text-end">${{ number_format($m->amount, 2) }}</td>
                                                        <td>{{ $m->created_at->format('d/m/Y H:i') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr><td colspan="10" class="text-center text-muted py-4">No hay sesiones de caja en este período.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
