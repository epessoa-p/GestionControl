@extends('layouts.app')

@section('title', 'Devoluciones de venta')

@include('layouts._compact_style')

@section('page')
<div class="view-compact container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3 page-head">
        <div>
            <h1 class="mb-1"><i class="bi bi-arrow-return-left text-warning me-2"></i>Devoluciones de venta</h1>
            <p class="text-muted mb-0">Productos devueltos por clientes que reingresan al inventario.</p>
        </div>
        <a href="{{ route('sales-returns.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Nueva devolución</a>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-auto">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Estado</option>
                        @foreach(\App\Models\SalesReturn::STATUS_LABELS as $val => $label)
                            <option value="{{ $val }}" {{ ($filters['status'] ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto"><button class="btn btn-sm btn-dark"><i class="bi bi-search"></i></button></div>
                <div class="col-auto"><a href="{{ route('sales-returns.index') }}" class="btn btn-sm btn-outline-secondary">Limpiar</a></div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Número</th><th>Venta</th><th>Cliente</th><th>Fecha</th><th>Motivo</th><th class="text-end">Total</th><th>Estado</th><th class="text-end">Acciones</th></tr>
                    </thead>
                    <tbody>
                        @forelse($returns as $r)
                            <tr>
                                <td><a href="{{ route('sales-returns.show', $r) }}" class="text-decoration-none fw-semibold">{{ $r->return_number }}</a></td>
                                <td>{{ $r->sale?->sale_number ?? '—' }}</td>
                                <td>{{ $r->client?->display_name ?? $r->client_name ?? '—' }}</td>
                                <td>{{ $r->return_date?->format('d/m/Y') }}</td>
                                <td>{{ \App\Models\SalesReturn::REASON_LABELS[$r->reason] ?? $r->reason }}</td>
                                <td class="text-end">${{ number_format($r->total, 2) }}</td>
                                <td><span class="badge bg-{{ \App\Models\SalesReturn::STATUS_COLORS[$r->status] ?? 'secondary' }}">{{ \App\Models\SalesReturn::STATUS_LABELS[$r->status] ?? $r->status }}</span></td>
                                <td class="text-end"><a href="{{ route('sales-returns.show', $r) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a></td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>No hay devoluciones registradas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="mt-3 d-flex justify-content-center">{{ $returns->links() }}</div>
</div>
@endsection
