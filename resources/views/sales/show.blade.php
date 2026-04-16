@extends('layouts.app')
@section('title', $sale->sale_number)
@section('page')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><i class="bi bi-cart3 text-primary me-2"></i>{{ $sale->sale_number }}</h1>
            <p class="text-muted mb-0">Venta — {{ $sale->sale_date?->format('d/m/Y') }}</p>
        </div>
        <div class="d-flex gap-2">
            @if($sale->status === 'pending')
                <form action="{{ route('sales.complete', $sale) }}" method="POST">@csrf
                    <button class="btn btn-success" onclick="return confirm('¿Completar venta y descontar inventario?')"><i class="bi bi-check-lg me-1"></i> Completar</button>
                </form>
            @endif
            @if($sale->status !== 'cancelled')
                <form action="{{ route('sales.cancel', $sale) }}" method="POST">@csrf
                    <button class="btn btn-warning" onclick="return confirm('¿Cancelar venta?')"><i class="bi bi-x-lg me-1"></i> Cancelar</button>
                </form>
            @endif
            <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
        </div>
    </div>

    {{-- KPI summary --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;flex-shrink:0;background:var(--bs-{{ \App\Models\Sale::STATUS_COLORS[$sale->status] ?? 'secondary' }});color:#fff;"><i class="bi bi-circle-fill fs-5"></i></div>
                    <div>
                        <small class="text-muted d-block">Estado</small>
                        <span class="badge bg-{{ \App\Models\Sale::STATUS_COLORS[$sale->status] ?? 'secondary' }}">{{ \App\Models\Sale::STATUS_LABELS[$sale->status] ?? $sale->status }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-info text-white" style="width:48px;height:48px;flex-shrink:0;"><i class="bi bi-credit-card fs-5"></i></div>
                    <div>
                        <small class="text-muted d-block">Método de pago</small>
                        <strong>{{ \App\Models\Sale::PAYMENT_LABELS[$sale->payment_method] ?? $sale->payment_method }}</strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-secondary text-white" style="width:48px;height:48px;flex-shrink:0;"><i class="bi bi-box-seam fs-5"></i></div>
                    <div>
                        <small class="text-muted d-block">Productos</small>
                        <strong>{{ $sale->details->count() }} ítem(s)</strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary bg-opacity-10">
                <div class="card-body text-center py-3">
                    <small class="text-muted d-block">Total</small>
                    <h3 class="fw-bold text-primary mb-0">${{ number_format($sale->total, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Info sidebar --}}
        <div class="col-lg-4">
            {{-- Client info --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold mb-0"><i class="bi bi-person-circle me-2 text-primary"></i>Cliente</h5>
                </div>
                <div class="card-body px-4 pt-3">
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted">Nombre</span><strong>{{ $sale->client_name ?: '—' }}</strong></li>
                        <li class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted">Teléfono</span><span>{{ $sale->client_phone ?: '—' }}</span></li>
                        <li class="d-flex justify-content-between py-2"><span class="text-muted">Documento</span><span>{{ $sale->client_document ?: '—' }}</span></li>
                    </ul>
                </div>
            </div>

            {{-- Sale info --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold mb-0"><i class="bi bi-info-circle me-2 text-primary"></i>Información</h5>
                </div>
                <div class="card-body px-4 pt-3">
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted"><i class="bi bi-megaphone me-1 small"></i> Promotor</span><span>{{ $sale->promoter?->name ?? '—' }}</span></li>
                        <li class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted"><i class="bi bi-geo-alt me-1 small"></i> Sucursal</span><span>{{ $sale->branch?->name ?? '—' }}</span></li>
                        <li class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted"><i class="bi bi-building me-1 small"></i> Almacén</span><span>{{ $sale->warehouse?->name ?? '—' }}</span></li>
                        <li class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted"><i class="bi bi-person me-1 small"></i> Creado por</span><span>{{ $sale->createdBy?->name }}</span></li>
                        <li class="d-flex justify-content-between py-2"><span class="text-muted"><i class="bi bi-chat-text me-1 small"></i> Notas</span><span class="text-end" style="max-width:60%;">{{ $sale->notes ?: '—' }}</span></li>
                    </ul>
                </div>
            </div>

            {{-- Commissions --}}
            @if($sale->commissions->count())
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 px-4">
                        <h5 class="fw-bold mb-0"><i class="bi bi-percent me-2 text-warning"></i>Comisiones</h5>
                    </div>
                    <div class="card-body p-0 pt-3">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr><th class="ps-4">Promotor</th><th class="text-end">Monto</th><th class="text-end pe-4">Estado</th></tr>
                            </thead>
                            <tbody>
                                @foreach($sale->commissions as $comm)
                                    <tr>
                                        <td class="ps-4">{{ $comm->promoter?->name }}</td>
                                        <td class="text-end">${{ number_format($comm->amount, 2) }}</td>
                                        <td class="text-end pe-4"><span class="badge bg-{{ $comm->status === 'paid' ? 'success' : 'warning' }}">{{ $comm->status === 'paid' ? 'Pagada' : 'Pendiente' }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        {{-- Products table --}}
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
                                @foreach($sale->details as $d)
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
                                <tr class="border-top"><td class="ps-4" colspan="4" class="text-end"><span class="float-end text-muted">Subtotal</span></td><td class="text-end pe-4">${{ number_format($sale->subtotal, 2) }}</td></tr>
                                <tr><td class="ps-4" colspan="4"><span class="float-end text-muted">Impuesto</span></td><td class="text-end pe-4">${{ number_format($sale->tax, 2) }}</td></tr>
                                <tr><td class="ps-4" colspan="4"><span class="float-end text-muted">Descuento</span></td><td class="text-end pe-4">-${{ number_format($sale->discount, 2) }}</td></tr>
                                <tr class="table-light"><th class="ps-4" colspan="4"><span class="float-end">Total</span></th><th class="text-end pe-4 fs-5 text-primary">${{ number_format($sale->total, 2) }}</th></tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
