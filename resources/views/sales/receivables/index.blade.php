@extends('layouts.app')

@section('title', 'Cuentas por cobrar')

@include('layouts._compact_style')

@php
    $statusTabs = ['open' => 'Pendientes', 'overdue' => 'Vencidas', 'partial' => 'Parciales', 'paid' => 'Pagadas', 'all' => 'Todas'];
@endphp

@section('page')
<div class="view-compact container-fluid">
    <div class="d-flex justify-content-between align-items-start mb-3 page-head">
        <div>
            <h1 class="mb-1"><i class="bi bi-cash-coin text-info me-2"></i>Cuentas por cobrar</h1>
            <p class="text-muted mb-0">Cuotas de ventas a crédito pendientes de pago.</p>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="row g-2 mb-3">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm"><div class="card-body">
                <div class="text-muted small">Total por cobrar</div>
                <div class="fw-bold fs-5 text-info">${{ number_format($totalPorCobrar, 2) }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm"><div class="card-body">
                <div class="text-muted small">Vencido</div>
                <div class="fw-bold fs-5 text-danger">${{ number_format($totalVencido, 2) }}</div>
            </div></div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="d-flex flex-wrap gap-2 align-items-center">
                <div class="btn-group btn-group-sm">
                    @foreach($statusTabs as $val => $label)
                        <a href="{{ request()->fullUrlWithQuery(['status' => $val]) }}"
                           class="btn {{ $status === $val ? 'btn-info text-white' : 'btn-outline-secondary' }}">{{ $label }}</a>
                    @endforeach
                </div>
                <input type="hidden" name="status" value="{{ $status }}">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" style="max-width:220px" placeholder="Venta o cliente...">
                <button class="btn btn-sm btn-dark"><i class="bi bi-search"></i></button>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Venta</th><th>Cliente</th><th class="text-center">Cuota</th><th>Vence</th><th class="text-end">Monto</th><th class="text-end">Pagado</th><th class="text-end">Saldo</th><th>Estado</th><th class="text-end">Acción</th></tr>
                    </thead>
                    <tbody>
                        @forelse($installments as $i)
                            @php $vencida = $i->status !== 'paid' && $i->due_date && $i->due_date->isPast(); @endphp
                            <tr class="{{ $vencida ? 'table-danger' : '' }}">
                                <td><a href="{{ route('sales.show', $i->sale_id) }}" class="text-decoration-none fw-semibold">{{ $i->sale?->sale_number }}</a></td>
                                <td>{{ $i->sale?->client?->display_name ?? $i->sale?->client_name ?? '—' }}</td>
                                <td class="text-center">{{ $i->installment_number }}</td>
                                <td>{{ $i->due_date?->format('d/m/Y') }}</td>
                                <td class="text-end">${{ number_format($i->amount, 2) }}</td>
                                <td class="text-end">${{ number_format($i->paid_amount, 2) }}</td>
                                <td class="text-end fw-semibold">${{ number_format($i->remaining, 2) }}</td>
                                <td><span class="badge bg-{{ \App\Models\SaleInstallment::STATUS_COLORS[$i->status] ?? 'secondary' }}">{{ \App\Models\SaleInstallment::STATUS_LABELS[$i->status] ?? $i->status }}</span></td>
                                <td class="text-end">
                                    @if($i->status !== 'paid')
                                        <a href="{{ route('sales.show', $i->sale_id) }}#cuotas" class="btn btn-sm btn-outline-success"><i class="bi bi-cash"></i> Pagar</a>
                                    @else
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>No hay cuotas que coincidan con el filtro.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="mt-3 d-flex justify-content-center">{{ $installments->links() }}</div>
</div>
@endsection
