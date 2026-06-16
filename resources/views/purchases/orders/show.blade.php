@extends('layouts.app')
@section('title', 'Orden ' . $order->order_number)
@section('page')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h1 class="mb-0"><i class="bi bi-bag-check text-primary me-2"></i>{{ $order->order_number }}</h1>
                <span class="badge bg-{{ \App\Models\PurchaseOrder::STATUS_COLORS[$order->status] }}-subtle text-{{ \App\Models\PurchaseOrder::STATUS_COLORS[$order->status] }} fs-6">
                    {{ \App\Models\PurchaseOrder::STATUS_LABELS[$order->status] }}
                </span>
            </div>
            <p class="text-muted mb-0">
                {{ $order->supplier?->display_name }}
                · Almacén: {{ $order->warehouse?->name }}
                · {{ $order->order_date?->format('d/m/Y') }}
            </p>
        </div>
        <div class="d-flex gap-2">
            @if(in_array($order->status, ['borrador','aprobada']))
            <a href="{{ route('purchases.orders.edit', $order) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil me-1"></i> Editar</a>
            @endif
            <a href="{{ route('purchases.orders.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Volver</a>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success alert-dismissible fade show mb-3"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if(session('error'))<div class="alert alert-danger alert-dismissible fade show mb-3"><i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    <div class="row g-4">
        <div class="col-lg-8">
            {{-- Ítems con progreso de recepción --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-2 px-4">
                    <h6 class="fw-bold mb-0"><i class="bi bi-list-ul me-1 text-primary"></i> Ítems</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Producto</th>
                                <th class="text-end">Ordenado</th>
                                <th class="text-end">Recibido</th>
                                <th class="text-end">Pendiente</th>
                                <th class="text-end">P. Unit.</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($order->items as $item)
                            <tr>
                                <td>{{ $item->product?->name ?? '—' }}</td>
                                <td class="text-end">{{ number_format($item->quantity, 2) }}</td>
                                <td class="text-end text-success">{{ number_format($item->quantity_received, 2) }}</td>
                                <td class="text-end {{ $item->pending_quantity > 0 ? 'text-warning fw-semibold' : 'text-muted' }}">{{ number_format($item->pending_quantity, 2) }}</td>
                                <td class="text-end">${{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-end fw-semibold">${{ number_format($item->total, 2) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">Sin ítems</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light">
                            @if($order->discount > 0)
                            <tr><td colspan="5" class="text-end text-muted">Descuento:</td><td class="text-end text-muted">-${{ number_format($order->discount, 2) }}</td></tr>
                            @endif
                            <tr><td colspan="5" class="text-end">IVA (12%):</td><td class="text-end">${{ number_format($order->tax, 2) }}</td></tr>
                            <tr><td colspan="5" class="text-end fw-bold fs-6">Total:</td><td class="text-end fw-bold fs-6 text-primary">${{ number_format($order->total, 2) }}</td></tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Recepciones --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-2 px-4 d-flex justify-content-between">
                    <h6 class="fw-bold mb-0"><i class="bi bi-box-arrow-in-down-right me-1 text-primary"></i> Recepciones</h6>
                    @if(in_array($order->status, ['aprobada','recibida_parcial']))
                    <a href="{{ route('purchases.receptions.create', ['order_id' => $order->id]) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-plus-lg me-1"></i> Registrar recepción
                    </a>
                    @endif
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light"><tr><th>Recepción</th><th>Fecha</th><th>Factura</th><th class="text-end">Total</th><th>Estado</th></tr></thead>
                        <tbody>
                            @forelse($order->receptions as $rec)
                            <tr>
                                <td><a href="{{ route('purchases.receptions.show', $rec) }}">{{ $rec->reception_number }}</a></td>
                                <td>{{ $rec->reception_date?->format('d/m/Y') }}</td>
                                <td>{{ $rec->invoice_number ?? '—' }}</td>
                                <td class="text-end">${{ number_format($rec->total, 2) }}</td>
                                <td><span class="badge bg-{{ \App\Models\PurchaseReception::STATUS_COLORS[$rec->status] }}-subtle text-{{ \App\Models\PurchaseReception::STATUS_COLORS[$rec->status] }}">{{ \App\Models\PurchaseReception::STATUS_LABELS[$rec->status] }}</span></td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">Sin recepciones</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- Info --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    @if($order->expected_date)
                    <div class="mb-3">
                        <small class="text-muted d-block">Entrega esperada</small>
                        <strong>{{ $order->expected_date->format('d/m/Y') }}</strong>
                    </div>
                    @endif
                    @if($order->quotation)
                    <div class="mb-3">
                        <small class="text-muted d-block">Cotización base</small>
                        <a href="{{ route('purchases.quotations.show', $order->quotation) }}">{{ $order->quotation->quotation_number }}</a>
                    </div>
                    @endif
                    @if($order->accountPayable)
                    <div>
                        <small class="text-muted d-block">Cuenta por pagar</small>
                        <a href="{{ route('purchases.payables.show', $order->accountPayable) }}" class="fw-semibold">
                            {{ $order->accountPayable->ap_number }}
                            <span class="badge bg-{{ \App\Models\AccountPayable::STATUS_COLORS[$order->accountPayable->status] }}-subtle text-{{ \App\Models\AccountPayable::STATUS_COLORS[$order->accountPayable->status] }} ms-1">{{ \App\Models\AccountPayable::STATUS_LABELS[$order->accountPayable->status] }}</span>
                        </a>
                    </div>
                    @endif
                    @if($order->notes)
                    <div class="mt-3"><small class="text-muted d-block">Notas</small>{{ $order->notes }}</div>
                    @endif
                </div>
            </div>

            {{-- Transiciones --}}
            @php
                $transitions = [
                    'borrador'  => ['aprobada' => ['Aprobar', 'success'], 'cancelada' => ['Cancelar', 'danger']],
                    'aprobada'  => ['cancelada' => ['Cancelar', 'warning']],
                ];
                $avail = $transitions[$order->status] ?? [];
            @endphp
            @if($avail)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-2 px-4">
                    <h6 class="fw-bold mb-0 small"><i class="bi bi-arrow-right-circle me-1 text-primary"></i> Cambiar estado</h6>
                </div>
                <div class="card-body p-3 d-flex flex-column gap-2">
                    @foreach($avail as $ns => [$label, $color])
                    <form action="{{ route('purchases.orders.update-status', $order) }}" method="POST">
                        @csrf <input type="hidden" name="status" value="{{ $ns }}">
                        <button class="btn btn-{{ $color }} btn-sm w-100">{{ $label }}</button>
                    </form>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Registrar recepción shortcut --}}
            @if(in_array($order->status, ['aprobada','recibida_parcial']))
            <a href="{{ route('purchases.receptions.create', ['order_id' => $order->id]) }}" class="btn btn-outline-success w-100 mb-2">
                <i class="bi bi-box-arrow-in-down-right me-1"></i> Registrar recepción
            </a>
            @endif

            {{-- Eliminar --}}
            @if(in_array($order->status, ['borrador','cancelada']))
            <form action="{{ route('purchases.orders.destroy', $order) }}" method="POST" onsubmit="return confirm('¿Eliminar esta orden?')">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger w-100"><i class="bi bi-trash me-1"></i> Eliminar</button>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection
