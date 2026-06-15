@extends('layouts.app')
@section('title', 'Proveedor: ' . $supplier->display_name)
@section('page')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h1 class="mb-0"><i class="bi bi-truck text-primary me-2"></i>{{ $supplier->display_name }}</h1>
                <span class="badge bg-{{ \App\Models\Supplier::STATUS_COLORS[$supplier->status] }}">{{ \App\Models\Supplier::STATUS_LABELS[$supplier->status] }}</span>
            </div>
            <p class="text-muted mb-0">{{ $supplier->supplier_number }} · {{ \App\Models\Supplier::TYPE_LABELS[$supplier->type] }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('purchases.suppliers.edit', $supplier) }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-pencil me-1"></i> Editar
            </a>
            <a href="{{ route('purchases.suppliers.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Volver
            </a>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success alert-dismissible fade show mb-3"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    <div class="row g-4">
        <div class="col-lg-8">
            {{-- Datos generales --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-2 px-4">
                    <h6 class="fw-bold mb-0"><i class="bi bi-info-circle me-1 text-primary"></i> Datos generales</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Nombre</small>
                            <strong>{{ $supplier->name }}</strong>
                        </div>
                        @if($supplier->commercial_name)
                        <div class="col-md-6">
                            <small class="text-muted d-block">Nombre comercial</small>
                            <strong>{{ $supplier->commercial_name }}</strong>
                        </div>
                        @endif
                        @if($supplier->document_number)
                        <div class="col-md-6">
                            <small class="text-muted d-block">{{ \App\Models\Supplier::DOCUMENT_LABELS[$supplier->document_type] ?? 'Documento' }}</small>
                            <strong>{{ $supplier->document_number }}</strong>
                        </div>
                        @endif
                        @if($supplier->email)
                        <div class="col-md-6">
                            <small class="text-muted d-block">Email</small>
                            <a href="mailto:{{ $supplier->email }}">{{ $supplier->email }}</a>
                        </div>
                        @endif
                        @if($supplier->phone)
                        <div class="col-md-4">
                            <small class="text-muted d-block">Teléfono</small>{{ $supplier->phone }}
                        </div>
                        @endif
                        @if($supplier->mobile)
                        <div class="col-md-4">
                            <small class="text-muted d-block">Móvil</small>{{ $supplier->mobile }}
                        </div>
                        @endif
                        @if($supplier->address)
                        <div class="col-12">
                            <small class="text-muted d-block">Dirección</small>
                            {{ $supplier->address }}{{ $supplier->city ? ', ' . $supplier->city : '' }}{{ $supplier->country ? ', ' . $supplier->country : '' }}
                        </div>
                        @endif
                        @if($supplier->contact_name)
                        <div class="col-12"><hr class="my-1"></div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Contacto</small>{{ $supplier->contact_name }}
                        </div>
                        @if($supplier->contact_email)
                        <div class="col-md-4">
                            <small class="text-muted d-block">Email contacto</small>
                            <a href="mailto:{{ $supplier->contact_email }}">{{ $supplier->contact_email }}</a>
                        </div>
                        @endif
                        @if($supplier->contact_phone)
                        <div class="col-md-4">
                            <small class="text-muted d-block">Tel. contacto</small>{{ $supplier->contact_phone }}
                        </div>
                        @endif
                        @endif
                    </div>
                </div>
            </div>

            {{-- Últimas órdenes --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-2 px-4 d-flex justify-content-between">
                    <h6 class="fw-bold mb-0"><i class="bi bi-bag-check me-1 text-primary"></i> Últimas órdenes de compra</h6>
                    <a href="{{ route('purchases.orders.index') }}?supplier_id={{ $supplier->id }}" class="btn btn-sm btn-link p-0">Ver todas</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light"><tr><th>OC</th><th>Fecha</th><th>Total</th><th>Estado</th></tr></thead>
                        <tbody>
                            @forelse($orders as $order)
                            <tr>
                                <td><a href="{{ route('purchases.orders.show', $order) }}">{{ $order->order_number }}</a></td>
                                <td>{{ $order->order_date?->format('d/m/Y') }}</td>
                                <td>${{ number_format($order->total, 2) }}</td>
                                <td><span class="badge bg-{{ \App\Models\PurchaseOrder::STATUS_COLORS[$order->status] }}-subtle text-{{ \App\Models\PurchaseOrder::STATUS_COLORS[$order->status] }}">{{ \App\Models\PurchaseOrder::STATUS_LABELS[$order->status] }}</span></td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">Sin órdenes registradas</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- Condiciones --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <small class="text-muted d-block">Plazo de pago</small>
                        <strong class="fs-5">{{ $supplier->payment_terms }} días</strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Límite de crédito</small>
                        <strong class="fs-5">${{ number_format($supplier->credit_limit, 2) }}</strong>
                    </div>
                    <div>
                        <small class="text-muted d-block">Saldo pendiente</small>
                        <strong class="fs-5 text-danger">${{ number_format($supplier->pending_balance, 2) }}</strong>
                    </div>
                </div>
            </div>

            {{-- CXP recientes --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-2 px-3">
                    <h6 class="fw-bold mb-0 small"><i class="bi bi-credit-card-2-front me-1 text-primary"></i> Cuentas por pagar</h6>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($payables as $ap)
                    <a href="{{ route('purchases.payables.show', $ap) }}" class="list-group-item list-group-item-action py-2 px-3">
                        <div class="d-flex justify-content-between">
                            <small class="fw-semibold">{{ $ap->ap_number }}</small>
                            <span class="badge bg-{{ \App\Models\AccountPayable::STATUS_COLORS[$ap->status] }}-subtle text-{{ \App\Models\AccountPayable::STATUS_COLORS[$ap->status] }} small">{{ \App\Models\AccountPayable::STATUS_LABELS[$ap->status] }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <small class="text-muted">Vence: {{ $ap->due_date?->format('d/m/Y') }}</small>
                            <small class="fw-semibold">${{ number_format($ap->balance, 2) }}</small>
                        </div>
                    </a>
                    @empty
                    <div class="list-group-item text-center text-muted py-3 small">Sin cuentas por pagar</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
