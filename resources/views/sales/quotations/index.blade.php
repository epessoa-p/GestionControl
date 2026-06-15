@extends('layouts.app')

@section('title', 'Cotizaciones de venta')

@include('layouts._compact_style')

@section('page')
<div class="view-compact container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3 page-head">
        <div>
            <h1 class="mb-1"><i class="bi bi-file-earmark-text text-primary me-2"></i>Cotizaciones de venta</h1>
            <p class="text-muted mb-0">Propuestas de precio a clientes.</p>
        </div>
        <a href="{{ route('sales-quotations.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Nueva cotización</a>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-auto">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Estado</option>
                        @foreach(\App\Models\SalesQuotation::STATUS_LABELS as $val => $label)
                            <option value="{{ $val }}" {{ ($filters['status'] ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto"><input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control form-control-sm" placeholder="Número..."></div>
                <div class="col-auto"><button class="btn btn-sm btn-dark"><i class="bi bi-search"></i></button></div>
                <div class="col-auto"><a href="{{ route('sales-quotations.index') }}" class="btn btn-sm btn-outline-secondary">Limpiar</a></div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Número</th><th>Cliente</th><th>Fecha</th><th>Vence</th><th class="text-end">Total</th><th>Estado</th><th class="text-end">Acciones</th></tr>
                    </thead>
                    <tbody>
                        @forelse($quotations as $q)
                            <tr>
                                <td><a href="{{ route('sales-quotations.show', $q) }}" class="text-decoration-none fw-semibold">{{ $q->quotation_number }}</a></td>
                                <td>{{ $q->client?->display_name ?? $q->client_name ?? '—' }}</td>
                                <td>{{ $q->quotation_date?->format('d/m/Y') }}</td>
                                <td>{{ $q->valid_until?->format('d/m/Y') ?? '—' }}</td>
                                <td class="text-end">${{ number_format($q->total, 2) }}</td>
                                <td><span class="badge bg-{{ \App\Models\SalesQuotation::STATUS_COLORS[$q->status] ?? 'secondary' }}">{{ \App\Models\SalesQuotation::STATUS_LABELS[$q->status] ?? $q->status }}</span></td>
                                <td class="text-end">
                                    <a href="{{ route('sales-quotations.show', $q) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                                    @if($q->status !== 'convertida')
                                        <a href="{{ route('sales-quotations.edit', $q) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>No hay cotizaciones registradas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="mt-3 d-flex justify-content-center">{{ $quotations->links() }}</div>
</div>
@endsection
