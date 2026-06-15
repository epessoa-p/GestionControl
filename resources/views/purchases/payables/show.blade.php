@extends('layouts.app')
@section('title', 'CXP ' . $accountPayable->ap_number)
@section('page')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h1 class="mb-0"><i class="bi bi-credit-card-2-front text-primary me-2"></i>{{ $accountPayable->ap_number }}</h1>
                <span class="badge bg-{{ \App\Models\AccountPayable::STATUS_COLORS[$accountPayable->status] }}-subtle text-{{ \App\Models\AccountPayable::STATUS_COLORS[$accountPayable->status] }} fs-6">
                    {{ \App\Models\AccountPayable::STATUS_LABELS[$accountPayable->status] }}
                </span>
            </div>
            <p class="text-muted mb-0">
                {{ $accountPayable->supplier?->display_name }}
                @if($accountPayable->purchaseOrder)
                · OC: <a href="{{ route('purchases.orders.show', $accountPayable->purchaseOrder) }}">{{ $accountPayable->purchaseOrder->order_number }}</a>
                @endif
            </p>
        </div>
        <a href="{{ route('purchases.payables.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Volver</a>
    </div>

    @if(session('success'))<div class="alert alert-success alert-dismissible fade show mb-3"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if(session('error'))<div class="alert alert-danger alert-dismissible fade show mb-3"><i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    {{-- KPIs --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="text-muted small">Monto total</div>
                <div class="fw-bold fs-5">${{ number_format($accountPayable->amount, 2) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="text-muted small">Pagado</div>
                <div class="fw-bold fs-5 text-success">${{ number_format($accountPayable->amount_paid, 2) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="text-muted small">Saldo pendiente</div>
                <div class="fw-bold fs-5 {{ $accountPayable->balance > 0 ? 'text-danger' : 'text-success' }}">${{ number_format($accountPayable->balance, 2) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="text-muted small">Vencimiento</div>
                <div class="fw-bold fs-5 {{ $accountPayable->due_date < now() && !in_array($accountPayable->status, ['pagada','anulada']) ? 'text-danger' : '' }}">
                    {{ $accountPayable->due_date?->format('d/m/Y') }}
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            {{-- Historial de pagos --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-2 px-4">
                    <h6 class="fw-bold mb-0"><i class="bi bi-clock-history me-1 text-primary"></i> Historial de pagos</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Fecha</th>
                                <th>Método</th>
                                <th>Referencia</th>
                                <th class="text-end">Monto</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($accountPayable->payments as $pay)
                            <tr>
                                <td>{{ $pay->payment_date?->format('d/m/Y') }}</td>
                                <td>
                                    <i class="bi bi-{{ \App\Models\AccountPayablePayment::PAYMENT_METHOD_ICONS[$pay->payment_method] ?? 'cash' }} me-1"></i>
                                    {{ \App\Models\AccountPayablePayment::PAYMENT_METHOD_LABELS[$pay->payment_method] ?? $pay->payment_method }}
                                </td>
                                <td class="text-muted small">{{ $pay->reference ?? '—' }}</td>
                                <td class="text-end fw-semibold text-success">${{ number_format($pay->amount, 2) }}</td>
                                <td>
                                    @if(!in_array($accountPayable->status, ['pagada','anulada']))
                                    <form action="{{ route('purchases.payables.payments.destroy', [$accountPayable, $pay]) }}" method="POST"
                                          onsubmit="return confirm('¿Eliminar este pago?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-link text-danger p-0"><i class="bi bi-trash"></i></button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">Sin pagos registrados</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            {{-- Datos generales --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    @if($accountPayable->invoice_number)
                    <div class="mb-3">
                        <small class="text-muted d-block">Nº Factura</small>
                        <strong>{{ $accountPayable->invoice_number }}</strong>
                    </div>
                    @endif
                    <div class="mb-3">
                        <small class="text-muted d-block">Fecha factura</small>
                        <strong>{{ $accountPayable->invoice_date?->format('d/m/Y') }}</strong>
                    </div>
                    @if($accountPayable->notes)
                    <div>
                        <small class="text-muted d-block">Notas</small>
                        <p class="mb-0">{{ $accountPayable->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Registrar pago --}}
            @if(!in_array($accountPayable->status, ['pagada','anulada']))
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-2 px-4">
                    <h6 class="fw-bold mb-0 small"><i class="bi bi-plus-circle me-1 text-success"></i> Registrar pago</h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('purchases.payables.payments.store', $accountPayable) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Monto <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">$</span>
                                <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror"
                                       value="{{ old('amount', number_format($accountPayable->balance, 2)) }}"
                                       min="0.01" step="0.01" max="{{ $accountPayable->balance }}" required>
                                @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Fecha <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control form-control-sm" value="{{ old('payment_date', now()->format('Y-m-d')) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Método</label>
                            <select name="payment_method" class="form-select form-select-sm">
                                @foreach(\App\Models\AccountPayablePayment::PAYMENT_METHOD_LABELS as $val => $label)
                                    <option value="{{ $val }}" {{ old('payment_method', 'transferencia') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Referencia</label>
                            <input type="text" name="reference" class="form-control form-control-sm" value="{{ old('reference') }}" placeholder="Nº transferencia, cheque...">
                        </div>
                        <button type="submit" class="btn btn-success w-100"><i class="bi bi-check-lg me-1"></i> Registrar pago</button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
