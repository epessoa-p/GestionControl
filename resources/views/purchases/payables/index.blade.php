@extends('layouts.app')
@section('title', 'Cuentas por Pagar')
@section('page')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><i class="bi bi-credit-card-2-front text-primary me-2"></i>Cuentas por Pagar</h1>
            <p class="text-muted mb-0">Gestión de obligaciones con proveedores</p>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    {{-- KPIs --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="text-muted small mb-1">Total pendiente</div>
                <div class="fw-bold fs-5 text-warning">${{ number_format($kpis['pendiente'] ?? 0, 2) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="text-muted small mb-1">Pago parcial</div>
                <div class="fw-bold fs-5 text-info">${{ number_format($kpis['pago_parcial'] ?? 0, 2) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="text-muted small mb-1">Vencidas</div>
                <div class="fw-bold fs-5 text-danger">${{ number_format($kpis['vencida'] ?? 0, 2) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="text-muted small mb-1">Pagadas (mes)</div>
                <div class="fw-bold fs-5 text-success">${{ number_format($kpis['pagada'] ?? 0, 2) }}</div>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="d-flex gap-2 flex-wrap mb-3">
        @foreach(array_merge(['todos' => 'Todos'], \App\Models\AccountPayable::STATUS_LABELS) as $val => $label)
            <a href="{{ request()->fullUrlWithQuery(['status' => $val]) }}"
               class="btn btn-sm {{ $status === $val ? 'btn-primary' : 'btn-outline-secondary' }}">
                {{ $label }}
                <span class="badge {{ $status === $val ? 'bg-white text-primary' : 'bg-secondary' }} ms-1">{{ $counts[$val] ?? 0 }}</span>
            </a>
        @endforeach
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>CXP</th>
                        <th>Proveedor</th>
                        <th>Factura</th>
                        <th>F. Vencimiento</th>
                        <th class="text-end">Monto</th>
                        <th class="text-end">Pagado</th>
                        <th class="text-end">Saldo</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payables as $ap)
                    <tr class="{{ $ap->status === 'vencida' ? 'table-danger' : '' }}">
                        <td><a href="{{ route('purchases.payables.show', $ap) }}" class="fw-semibold text-decoration-none">{{ $ap->ap_number }}</a></td>
                        <td>{{ $ap->supplier?->display_name ?? '—' }}</td>
                        <td>{{ $ap->invoice_number ?? '—' }}</td>
                        <td class="{{ $ap->due_date < now() && !in_array($ap->status, ['pagada','anulada']) ? 'text-danger fw-semibold' : '' }}">
                            {{ $ap->due_date?->format('d/m/Y') }}
                        </td>
                        <td class="text-end">${{ number_format($ap->amount, 2) }}</td>
                        <td class="text-end text-success">${{ number_format($ap->amount_paid, 2) }}</td>
                        <td class="text-end fw-bold">${{ number_format($ap->balance, 2) }}</td>
                        <td><span class="badge bg-{{ \App\Models\AccountPayable::STATUS_COLORS[$ap->status] }}-subtle text-{{ \App\Models\AccountPayable::STATUS_COLORS[$ap->status] }}">{{ \App\Models\AccountPayable::STATUS_LABELS[$ap->status] }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('purchases.payables.show', $ap) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center py-5 text-muted">
                        <i class="bi bi-credit-card-2-front display-4 d-block mb-2 opacity-25"></i>
                        No hay cuentas por pagar registradas.
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payables->hasPages())<div class="card-footer bg-white">{{ $payables->links() }}</div>@endif
    </div>
</div>
@endsection
