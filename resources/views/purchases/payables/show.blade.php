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
                                <th>Origen</th>
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
                                <td class="small">
                                    @if($pay->source === 'tesoreria')
                                        <span class="badge bg-info-subtle text-info border border-info-subtle"><i class="bi bi-bank me-1"></i>{{ $pay->treasuryAccount?->name ?? 'Tesorería' }}</span>
                                    @elseif($pay->source === 'caja')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-cash-stack me-1"></i>Caja</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
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
                            <tr><td colspan="6" class="text-center text-muted py-3">Sin pagos registrados</td></tr>
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
            <button type="button" class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#payModal">
                <i class="bi bi-plus-circle me-1"></i> Registrar pago
            </button>
            @endif
        </div>
    </div>
</div>

{{-- ── Modal: Registrar pago ──────────────────────────────────────── --}}
@if(!in_array($accountPayable->status, ['pagada','anulada']))
<div class="modal fade" id="payModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('purchases.payables.payments.store', $accountPayable) }}" method="POST">
                @csrf
                <input type="hidden" name="source" id="paySource" value="{{ $openSession ? 'caja' : 'tesoreria' }}">

                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-cash-coin text-success me-2"></i>Registrar pago</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <p class="text-muted small mb-3">Elige de dónde sale el dinero del pago.</p>

                    {{-- Tabs origen --}}
                    <ul class="nav nav-pills nav-justified mb-3 gap-2" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link {{ $openSession ? 'active' : '' }}" id="tabCaja" data-bs-toggle="pill"
                                    data-bs-target="#paneCaja" type="button" {{ !$openSession ? 'disabled' : '' }}>
                                <i class="bi bi-cash-stack me-1"></i> Caja abierta
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link {{ !$openSession ? 'active' : '' }}" id="tabTesoreria" data-bs-toggle="pill"
                                    data-bs-target="#paneTesoreria" type="button">
                                <i class="bi bi-bank me-1"></i> Tesorería
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content mb-3">
                        {{-- Caja --}}
                        <div class="tab-pane fade {{ $openSession ? 'show active' : '' }}" id="paneCaja">
                            @if($openSession)
                                <div class="alert alert-success py-2 mb-0 small">
                                    <i class="bi bi-cash-stack me-1"></i> El pago saldrá de
                                    <strong>{{ $openSession->cashRegister?->name }}</strong>
                                    <div class="text-muted">
                                        Cajero: {{ $openSession->openedBy?->name ?? auth()->user()->name }}
                                        @if($openSession->cashRegister?->branch) · {{ $openSession->cashRegister->branch->name }} @endif
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-warning py-2 mb-0 small">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    No tienes una caja abierta. Abre una caja o paga desde Tesorería.
                                </div>
                            @endif
                        </div>
                        {{-- Tesorería --}}
                        <div class="tab-pane fade {{ !$openSession ? 'show active' : '' }}" id="paneTesoreria">
                            <label class="form-label fw-semibold small">Cuenta de tesorería <span class="text-danger">*</span></label>
                            <select name="treasury_account_id" class="form-select form-select-sm @error('treasury_account_id') is-invalid @enderror">
                                <option value="">Seleccionar cuenta...</option>
                                @foreach($treasuryAccounts as $acc)
                                    <option value="{{ $acc->id }}" {{ old('treasury_account_id') == $acc->id ? 'selected' : '' }}>
                                        {{ $acc->name }} — saldo Bs. {{ number_format($acc->current_balance, 2) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('treasury_account_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            @if($treasuryAccounts->isEmpty())
                                <div class="form-text text-danger">No hay cuentas de tesorería activas. <a href="{{ route('treasury.create') }}">Crear una</a>.</div>
                            @endif
                        </div>
                    </div>

                    {{-- Campos comunes --}}
                    <div class="row g-2">
                        <div class="col-7">
                            <label class="form-label fw-semibold small">Monto <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">Bs.</span>
                                <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror"
                                       value="{{ old('amount', $accountPayable->balance) }}"
                                       min="0.01" step="0.01" max="{{ $accountPayable->balance }}" required>
                                @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-text">Saldo pendiente: Bs. {{ number_format($accountPayable->balance, 2) }}</div>
                        </div>
                        <div class="col-5">
                            <label class="form-label fw-semibold small">Fecha <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control form-control-sm" value="{{ old('payment_date', now()->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Método</label>
                            <select name="payment_method" class="form-select form-select-sm">
                                @foreach(\App\Models\AccountPayablePayment::PAYMENT_METHOD_LABELS as $val => $label)
                                    <option value="{{ $val }}" {{ old('payment_method', 'transferencia') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Referencia</label>
                            <input type="text" name="reference" class="form-control form-control-sm" value="{{ old('reference') }}" placeholder="Nº transf., cheque...">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i> Registrar pago</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var src = document.getElementById('paySource');
    document.getElementById('tabCaja')?.addEventListener('shown.bs.tab', function () { src.value = 'caja'; });
    document.getElementById('tabTesoreria')?.addEventListener('shown.bs.tab', function () { src.value = 'tesoreria'; });
    @if($errors->any())
        new bootstrap.Modal(document.getElementById('payModal')).show();
    @endif
});
</script>
@endpush
@endif
@endsection
