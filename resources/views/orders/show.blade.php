@extends('layouts.app')
@section('title', $order->order_number)
@section('page')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><i class="bi bi-clipboard2-data text-primary me-2"></i>{{ $order->order_number }}</h1>
            <p class="text-muted mb-0">Orden — {{ $order->order_date?->format('d/m/Y') }}</p>
        </div>
        <div class="d-flex gap-2">
            @php
                $transitions = match($order->status) {
                    'draft' => ['confirmed' => 'Confirmar', 'cancelled' => 'Cancelar'],
                    'confirmed' => ['in_process' => 'En Proceso', 'cancelled' => 'Cancelar'],
                    'in_process' => ['shipped' => 'Enviar', 'delivered' => 'Entregar', 'cancelled' => 'Cancelar'],
                    'shipped' => ['delivered' => 'Entregar', 'cancelled' => 'Cancelar'],
                    default => [],
                };
                $btnColors = [
                    'confirmed' => 'primary', 'in_process' => 'info', 'shipped' => 'warning',
                    'delivered' => 'success', 'cancelled' => 'danger',
                ];
            @endphp
            @foreach($transitions as $status => $label)
                <form action="{{ route('orders.update-status', $order) }}" method="POST">@csrf
                    <input type="hidden" name="status" value="{{ $status }}">
                    <button class="btn btn-{{ $btnColors[$status] ?? 'secondary' }}" onclick="return confirm('¿Cambiar estado a {{ $label }}?')">
                        <i class="bi bi-{{ $status === 'cancelled' ? 'x-lg' : ($status === 'delivered' ? 'check-all' : 'arrow-right') }} me-1"></i> {{ $label }}
                    </button>
                </form>
            @endforeach
            <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <small class="text-muted d-block">Estado</small>
                    <span class="badge bg-{{ \App\Models\Order::STATUS_COLORS[$order->status] ?? 'secondary' }} mt-1">
                        {{ \App\Models\Order::STATUS_LABELS[$order->status] ?? $order->status }}
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <small class="text-muted d-block">Tipo</small>
                    <span class="badge bg-{{ $order->order_type === 'customer' ? 'primary' : 'dark' }} mt-1">
                        {{ \App\Models\Order::ORDER_TYPE_LABELS[$order->order_type] ?? $order->order_type }}
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <small class="text-muted d-block">Prioridad</small>
                    <span class="badge bg-{{ \App\Models\Order::PRIORITY_COLORS[$order->priority] ?? 'secondary' }} mt-1">
                        {{ \App\Models\Order::PRIORITY_LABELS[$order->priority] ?? $order->priority }}
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <small class="text-muted d-block">Productos</small>
                    <strong class="fs-5">{{ $order->details->count() }}</strong>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <small class="text-muted d-block">Entrega</small>
                    <strong>{{ $order->expected_date?->format('d/m/Y') ?? '—' }}</strong>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm bg-primary bg-opacity-10">
                <div class="card-body text-center py-3">
                    <small class="text-muted d-block">Total</small>
                    <h4 class="fw-bold text-primary mb-0">${{ number_format($order->total, 2) }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Progress bar --}}
    @php
        $steps = ['draft', 'confirmed', 'in_process', 'shipped', 'delivered'];
        $currentIdx = array_search($order->status, $steps);
        $isCancelled = $order->status === 'cancelled';
    @endphp
    @if(!$isCancelled)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-center position-relative" style="z-index:1;">
                @foreach($steps as $idx => $step)
                    @php
                        $done = $currentIdx !== false && $idx <= $currentIdx;
                        $active = $idx === $currentIdx;
                    @endphp
                    <div class="text-center flex-fill">
                        <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center {{ $done ? 'bg-primary text-white' : 'bg-light text-muted border' }}" style="width:36px;height:36px;">
                            @if($done && !$active)<i class="bi bi-check"></i>@else<small>{{ $idx + 1 }}</small>@endif
                        </div>
                        <small class="{{ $active ? 'fw-bold text-primary' : 'text-muted' }} d-block mt-1">{{ \App\Models\Order::STATUS_LABELS[$step] }}</small>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @else
    <div class="alert alert-danger d-flex align-items-center mb-4">
        <i class="bi bi-x-circle-fill me-2 fs-4"></i>
        <div><strong>Orden cancelada</strong></div>
    </div>
    @endif

    <div class="row g-4">
        {{-- Info sidebar --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold mb-0"><i class="bi bi-person-circle me-2 text-primary"></i>Cliente / Proveedor</h5>
                </div>
                <div class="card-body px-4 pt-3">
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted">Nombre</span><strong>{{ $order->client_name ?: '—' }}</strong></li>
                        <li class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted">Teléfono</span><span>{{ $order->client_phone ?: '—' }}</span></li>
                        <li class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted">Documento</span><span>{{ $order->client_document ?: '—' }}</span></li>
                        <li class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted">Email</span><span>{{ $order->client_email ?: '—' }}</span></li>
                        <li class="d-flex justify-content-between py-2"><span class="text-muted">Dirección</span><span class="text-end" style="max-width:60%;">{{ $order->client_address ?: '—' }}</span></li>
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold mb-0"><i class="bi bi-info-circle me-2 text-primary"></i>Información</h5>
                </div>
                <div class="card-body px-4 pt-3">
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted">Sucursal</span><span>{{ $order->branch?->name ?? '—' }}</span></li>
                        <li class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted">Almacén</span><span>{{ $order->warehouse?->name ?? '—' }}</span></li>
                        <li class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted">Creado por</span><span>{{ $order->createdBy?->name }}</span></li>
                        @if($order->delivered_date)
                        <li class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted">Entregado</span><span>{{ $order->delivered_date?->format('d/m/Y') }}</span></li>
                        @endif
                        <li class="d-flex justify-content-between py-2"><span class="text-muted">Notas</span><span class="text-end" style="max-width:60%;">{{ $order->notes ?: '—' }}</span></li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Products --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold mb-0"><i class="bi bi-box-seam me-2 text-primary"></i>Productos</h5>
                </div>
                <div class="card-body p-0 pt-3">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Producto</th>
                                    <th class="text-end">Cantidad</th>
                                    <th class="text-end">Precio unit.</th>
                                    <th class="text-end">Descuento</th>
                                    <th class="text-end pe-4">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->details as $d)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-semibold">{{ $d->product?->name }}</div>
                                            <small class="text-muted">{{ $d->product?->sku }}</small>
                                        </td>
                                        <td class="text-end">{{ number_format($d->quantity, 2) }}</td>
                                        <td class="text-end">${{ number_format($d->unit_price, 2) }}</td>
                                        <td class="text-end">${{ number_format($d->discount, 2) }}</td>
                                        <td class="text-end pe-4 fw-semibold">${{ number_format($d->total, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="border-top"><td class="ps-4" colspan="4"><span class="float-end text-muted">Subtotal</span></td><td class="text-end pe-4">${{ number_format($order->subtotal, 2) }}</td></tr>
                                <tr><td class="ps-4" colspan="4"><span class="float-end text-muted">Impuesto</span></td><td class="text-end pe-4">${{ number_format($order->tax, 2) }}</td></tr>
                                <tr><td class="ps-4" colspan="4"><span class="float-end text-muted">Descuento</span></td><td class="text-end pe-4">-${{ number_format($order->discount, 2) }}</td></tr>
                                <tr class="table-light"><th class="ps-4" colspan="4"><span class="float-end">Total</span></th><th class="text-end pe-4 fs-5 text-primary">${{ number_format($order->total, 2) }}</th></tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
