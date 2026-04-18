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
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center {{ $sale->sale_type === 'credit' ? 'bg-warning' : 'bg-success' }} text-white" style="width:48px;height:48px;flex-shrink:0;">
                        <i class="bi bi-{{ $sale->sale_type === 'credit' ? 'credit-card-2-front' : 'cash-coin' }} fs-5"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Tipo</small>
                        <strong>{{ \App\Models\Sale::SALE_TYPE_LABELS[$sale->sale_type ?? 'cash'] ?? 'Contado' }}</strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-info text-white" style="width:48px;height:48px;flex-shrink:0;"><i class="bi bi-credit-card fs-5"></i></div>
                    <div>
                        <small class="text-muted d-block">Pago</small>
                        <strong>{{ \App\Models\Sale::PAYMENT_LABELS[$sale->payment_method] ?? $sale->payment_method }}</strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
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

    {{-- Credit progress bar --}}
    @if(($sale->sale_type ?? 'cash') === 'credit')
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            @php
                $paidPct = $sale->total > 0 ? min(100, ($sale->credit_paid_amount / $sale->total) * 100) : 0;
                $creditColor = match($sale->credit_status) {
                    'paid' => 'success', 'partial' => 'info', 'overdue' => 'danger', default => 'warning',
                };
            @endphp
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <i class="bi bi-credit-card-2-front me-1 text-{{ $creditColor }}"></i>
                    <strong>Crédito:</strong>
                    <span class="badge bg-{{ $creditColor }}">{{ \App\Models\Sale::CREDIT_STATUS_LABELS[$sale->credit_status ?? 'pending'] ?? 'Pendiente' }}</span>
                </div>
                <div class="text-end">
                    <span class="text-muted">Pagado:</span>
                    <strong>${{ number_format($sale->credit_paid_amount, 2) }}</strong>
                    <span class="text-muted">de</span>
                    <strong>${{ number_format($sale->total, 2) }}</strong>
                </div>
            </div>
            <div class="progress" style="height: 8px;">
                <div class="progress-bar bg-{{ $creditColor }}" style="width: {{ $paidPct }}%"></div>
            </div>
        </div>
    </div>
    @endif

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

            {{-- Installments for credit --}}
            @if(($sale->sale_type ?? 'cash') === 'credit' && $sale->installments->count())
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold mb-0"><i class="bi bi-calendar3 me-2 text-success"></i>Plan de cuotas</h5>
                </div>
                <div class="card-body p-0 pt-3">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4 text-center">#</th>
                                    <th>Vencimiento</th>
                                    <th class="text-end">%</th>
                                    <th class="text-end">Monto</th>
                                    <th class="text-end">Pagado</th>
                                    <th class="text-end">Pendiente</th>
                                    <th>Estado</th>
                                    <th class="text-end pe-4">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sale->installments as $inst)
                                    @php
                                        $isOverdue = $inst->status !== 'paid' && $inst->due_date && $inst->due_date->isPast();
                                    @endphp
                                    <tr class="{{ $isOverdue ? 'table-danger' : '' }}">
                                        <td class="ps-4 text-center fw-semibold">{{ $inst->installment_number }}</td>
                                        <td>
                                            {{ $inst->due_date?->format('d/m/Y') }}
                                            @if($isOverdue && $inst->status !== 'paid')
                                                <i class="bi bi-exclamation-triangle-fill text-danger ms-1" title="Vencida"></i>
                                            @endif
                                        </td>
                                        <td class="text-end">{{ number_format($inst->percentage, 2) }}%</td>
                                        <td class="text-end fw-semibold">${{ number_format($inst->amount, 2) }}</td>
                                        <td class="text-end">${{ number_format($inst->paid_amount, 2) }}</td>
                                        <td class="text-end">${{ number_format($inst->remaining, 2) }}</td>
                                        <td>
                                            <span class="badge bg-{{ \App\Models\SaleInstallment::STATUS_COLORS[$inst->status] ?? 'secondary' }}">
                                                {{ \App\Models\SaleInstallment::STATUS_LABELS[$inst->status] ?? $inst->status }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            @if($inst->status !== 'paid')
                                                <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#payModal{{ $inst->id }}">
                                                    <i class="bi bi-cash me-1"></i>Pagar
                                                </button>
                                            @else
                                                <small class="text-success"><i class="bi bi-check-circle"></i> {{ $inst->paid_at?->format('d/m/Y') }}</small>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Payment modals --}}
            @foreach($sale->installments->where('status', '!=', 'paid') as $inst)
            <div class="modal fade" id="payModal{{ $inst->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header bg-success bg-opacity-10 border-0">
                            <h5 class="modal-title fw-bold"><i class="bi bi-cash me-2"></i>Pagar Cuota #{{ $inst->installment_number }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="{{ route('sales.pay-installment', [$sale, $inst]) }}" method="POST">
                            @csrf
                            <div class="modal-body">
                                <div class="row g-3 mb-3">
                                    <div class="col-6">
                                        <div class="bg-light rounded p-3 text-center">
                                            <small class="text-muted d-block">Monto cuota</small>
                                            <strong class="fs-5">${{ number_format($inst->amount, 2) }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="bg-light rounded p-3 text-center">
                                            <small class="text-muted d-block">Pendiente</small>
                                            <strong class="fs-5 text-danger">${{ number_format($inst->remaining, 2) }}</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Monto a pagar <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" min="0.01" max="{{ $inst->remaining }}" name="pay_amount" class="form-control" value="{{ number_format($inst->remaining, 2, '.', '') }}" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Método de pago <span class="text-danger">*</span></label>
                                    <select name="pay_method" class="form-select" required>
                                        <option value="cash">Efectivo</option>
                                        <option value="card">Tarjeta</option>
                                        <option value="transfer">Transferencia</option>
                                        <option value="other">Otro</option>
                                    </select>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label fw-semibold">Notas</label>
                                    <input type="text" name="pay_notes" class="form-control" placeholder="Referencia o comprobante...">
                                </div>
                            </div>
                            <div class="modal-footer border-0">
                                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Registrar pago</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
            @endif
        </div>
    </div>
</div>
@endsection
