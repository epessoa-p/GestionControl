@extends('layouts.app')
@section('title', 'Sesión de caja')
@section('page')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><i class="bi bi-cash-stack text-primary me-2"></i>{{ $session->cashRegister?->name }}</h1>
            <p class="text-muted mb-0">
                Sesión abierta {{ $session->opened_at?->format('d/m/Y H:i') }}
                — <span class="badge bg-{{ $session->isOpen() ? 'success' : 'secondary' }}"><i class="bi bi-{{ $session->isOpen() ? 'unlock' : 'lock' }} me-1"></i>{{ $session->isOpen() ? 'Abierta' : 'Cerrada' }}</span>
            </p>
        </div>
        <div class="d-flex gap-2">
            @if($session->isOpen())
                <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#closeModal"><i class="bi bi-lock me-1"></i> Cerrar caja</button>
            @endif
            <a href="{{ route('cash-registers.show', $session->cashRegister) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
        </div>
    </div>

    {{-- KPI cards --}}
    @php
        $incomeTotal = $session->movements->whereIn('type', ['sale', 'income'])->sum('amount');
        $expenseTotal = $session->movements->whereIn('type', ['expense', 'withdrawal'])->sum('amount');
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary text-white" style="width:48px;height:48px;flex-shrink:0;"><i class="bi bi-cash fs-5"></i></div>
                    <div>
                        <small class="text-muted d-block">Apertura</small>
                        <strong>${{ number_format($session->opening_amount, 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-success text-white" style="width:48px;height:48px;flex-shrink:0;"><i class="bi bi-arrow-down-left fs-5"></i></div>
                    <div>
                        <small class="text-muted d-block">Ingresos</small>
                        <strong class="text-success">${{ number_format($incomeTotal, 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-danger text-white" style="width:48px;height:48px;flex-shrink:0;"><i class="bi bi-arrow-up-right fs-5"></i></div>
                    <div>
                        <small class="text-muted d-block">Egresos</small>
                        <strong class="text-danger">${{ number_format($expenseTotal, 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            @if(!$session->isOpen())
                <div class="card border-0 shadow-sm {{ $session->difference < 0 ? 'bg-danger' : ($session->difference > 0 ? 'bg-warning' : 'bg-success') }} bg-opacity-10">
                    <div class="card-body text-center py-3">
                        <small class="text-muted d-block">Diferencia</small>
                        <h4 class="fw-bold mb-0 {{ $session->difference < 0 ? 'text-danger' : ($session->difference > 0 ? 'text-warning' : 'text-success') }}">${{ number_format($session->difference, 2) }}</h4>
                    </div>
                </div>
            @else
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center bg-info text-white" style="width:48px;height:48px;flex-shrink:0;"><i class="bi bi-receipt fs-5"></i></div>
                        <div>
                            <small class="text-muted d-block">Movimientos</small>
                            <strong>{{ $session->movements->count() }}</strong>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="row g-4">
        {{-- Info sidebar --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold mb-0"><i class="bi bi-info-circle me-2 text-primary"></i>Información</h5>
                </div>
                <div class="card-body px-4 pt-3">
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted"><i class="bi bi-person-badge me-1 small"></i> Personal</span><span>{{ $session->personal?->full_name }}</span></li>
                        <li class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted"><i class="bi bi-person me-1 small"></i> Abierto por</span><span>{{ $session->openedBy?->name }}</span></li>
                        <li class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted"><i class="bi bi-calendar me-1 small"></i> Apertura</span><span>{{ $session->opened_at?->format('d/m/Y H:i') }}</span></li>
                        @if(!$session->isOpen())
                            <li class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted"><i class="bi bi-cash-coin me-1 small"></i> Monto cierre</span><strong>${{ number_format($session->closing_amount, 2) }}</strong></li>
                            <li class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted"><i class="bi bi-calculator me-1 small"></i> Esperado</span><span>${{ number_format($session->expected_amount, 2) }}</span></li>
                            <li class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted"><i class="bi bi-person me-1 small"></i> Cerrado por</span><span>{{ $session->closedBy?->name }}</span></li>
                            <li class="d-flex justify-content-between py-2"><span class="text-muted"><i class="bi bi-calendar-x me-1 small"></i> Cerrado</span><span>{{ $session->closed_at?->format('d/m/Y H:i') }}</span></li>
                        @endif
                    </ul>
                </div>
            </div>

            @if($session->isOpen())
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 px-4">
                        <h5 class="fw-bold mb-0"><i class="bi bi-plus-circle me-2 text-success"></i>Nuevo movimiento</h5>
                    </div>
                    <div class="card-body px-4 pt-3">
                        <form action="{{ route('cash-sessions.add-movement', $session) }}" method="POST" class="row g-2">
                            @csrf
                            <div class="col-12">
                                <label class="form-label small text-muted mb-1">Tipo</label>
                                <select name="type" id="mvType" class="form-select form-select-sm @error('type') is-invalid @enderror" required>
                                    @foreach(\App\Models\CashMovement::TYPE_LABELS as $val => $label)
                                        <option value="{{ $val }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small text-muted mb-1">Categoría</label>
                                <select name="category" id="mvCategory" class="form-select form-select-sm">
                                    <option value="">— Sin categoría —</option>
                                    @foreach(\App\Models\CashMovement::CATEGORIES as $key => $cat)
                                        <option value="{{ $key }}" data-type="{{ $cat['type'] }}">{{ $cat['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small text-muted mb-1">Concepto</label>
                                <input type="text" name="concept" class="form-control form-control-sm @error('concept') is-invalid @enderror" placeholder="Descripción del movimiento" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small text-muted mb-1">Monto</label>
                                <input type="number" step="0.01" min="0.01" name="amount" class="form-control form-control-sm @error('amount') is-invalid @enderror" placeholder="0.00" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small text-muted mb-1">Método pago</label>
                                <select name="payment_method" class="form-select form-select-sm" required>
                                    @foreach(\App\Models\CashMovement::PAYMENT_LABELS as $val => $label)
                                        <option value="{{ $val }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small text-muted mb-1">Referencia</label>
                                <input type="text" name="reference" class="form-control form-control-sm" placeholder="Opcional">
                            </div>
                            <div class="col-12 mt-2">
                                <button class="btn btn-sm btn-dark w-100" type="submit"><i class="bi bi-plus-lg me-1"></i> Registrar</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>

        {{-- Movements table --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold mb-0"><i class="bi bi-arrow-left-right me-2 text-primary"></i>Movimientos</h5>
                </div>
                <div class="card-body p-0 pt-3">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Tipo</th>
                                    <th>Categoría</th>
                                    <th>Concepto</th>
                                    <th>Método</th>
                                    <th class="text-end">Monto</th>
                                    <th>Referencia</th>
                                    <th class="pe-4">Registrado por</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($session->movements as $m)
                                    <tr>
                                        <td class="ps-4"><span class="badge bg-{{ \App\Models\CashMovement::TYPE_COLORS[$m->type] ?? 'secondary' }}">{{ \App\Models\CashMovement::TYPE_LABELS[$m->type] ?? $m->type }}</span></td>
                                        <td><small class="text-muted">{{ \App\Models\CashMovement::CATEGORIES[$m->category]['label'] ?? ($m->category ?: '—') }}</small></td>
                                        <td>{{ $m->concept }}</td>
                                        <td><small class="text-muted">{{ \App\Models\CashMovement::PAYMENT_LABELS[$m->payment_method] ?? $m->payment_method }}</small></td>
                                        <td class="text-end fw-semibold">${{ number_format($m->amount, 2) }}</td>
                                        <td><small class="text-muted">{{ $m->reference ?: '—' }}</small></td>
                                        <td class="pe-4">{{ $m->createdBy?->name }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-1 d-block mb-2"></i>No hay movimientos registrados</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($session->isOpen())
<div class="modal fade" id="closeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('cash-sessions.close', $session) }}" method="POST" id="closeForm">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-lock me-2 text-warning"></i>Cerrar caja</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    <div class="alert alert-warning bg-opacity-10 border-0 mb-3">
                        <small><i class="bi bi-exclamation-triangle me-1"></i> Una vez cerrada la sesión, no se podrán agregar más movimientos.</small>
                    </div>

                    {{-- Resumen esperado --}}
                    @php
                        $expectedNow = $session->calculateExpectedAmount();
                        $incomesTotal = $session->movements->where('type','income')->sum('amount');
                        $expensesTotal = $session->movements->where('type','expense')->sum('amount');
                    @endphp
                    <div class="row g-2 mb-4 text-center">
                        <div class="col-4">
                            <div class="bg-light rounded p-2">
                                <small class="text-muted d-block">Apertura</small>
                                <strong>${{ number_format($session->opening_amount, 2) }}</strong>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="bg-success bg-opacity-10 rounded p-2">
                                <small class="text-muted d-block">+ Ingresos</small>
                                <strong class="text-success">${{ number_format($incomesTotal, 2) }}</strong>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="bg-danger bg-opacity-10 rounded p-2">
                                <small class="text-muted d-block">− Egresos</small>
                                <strong class="text-danger">${{ number_format($expensesTotal, 2) }}</strong>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="bg-primary bg-opacity-10 rounded p-2">
                                <small class="text-muted d-block">Saldo esperado en caja</small>
                                <strong class="text-primary fs-5">${{ number_format($expectedNow, 2) }}</strong>
                            </div>
                        </div>
                    </div>

                    {{-- Modo de ingreso: total directo o desglose --}}
                    <div class="mb-3">
                        <div class="d-flex gap-3 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="close_mode" id="modeTotal" value="total" checked>
                                <label class="form-check-label" for="modeTotal">Ingresar total</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="close_mode" id="modeBreakdown" value="breakdown">
                                <label class="form-check-label" for="modeBreakdown">Contar por denominaciones</label>
                            </div>
                        </div>

                        {{-- Monto total directo --}}
                        <div id="sectionTotal">
                            <label class="form-label fw-semibold">Monto de cierre <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" min="0" name="closing_amount" id="closingAmountDirect" class="form-control" placeholder="0.00">
                            </div>
                        </div>

                        {{-- Desglose por denominaciones --}}
                        <div id="sectionBreakdown" style="display:none;">
                            @php
                                $denoms = [
                                    'b200' => '$200', 'b100' => '$100', 'b50' => '$50',
                                    'b20'  => '$20',  'b10'  => '$10',
                                    'm5'   => '$5',   'm2'   => '$2',   'm1'   => '$1',
                                    'c50'  => '$0.50','c20'  => '$0.20','c10'  => '$0.10',
                                ];
                                $denomValues = ['b200'=>200,'b100'=>100,'b50'=>50,'b20'=>20,'b10'=>10,'m5'=>5,'m2'=>2,'m1'=>1,'c50'=>0.5,'c20'=>0.2,'c10'=>0.1];
                            @endphp
                            <div class="row g-2">
                                @foreach($denoms as $key => $label)
                                    <div class="col-6 col-md-4">
                                        <label class="form-label small mb-1">{{ $label }}</label>
                                        <div class="input-group input-group-sm">
                                            <input type="number" min="0" value="0"
                                                name="closing_breakdown[{{ $key }}]"
                                                class="form-control denom-input"
                                                data-value="{{ $denomValues[$key] }}"
                                                placeholder="0">
                                            <span class="input-group-text text-muted small denom-subtotal" id="sub_{{ $key }}">$0.00</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-3 p-3 bg-light rounded d-flex justify-content-between align-items-center">
                                <span class="fw-semibold">Total contado:</span>
                                <span class="fs-5 fw-bold text-primary" id="breakdownTotal">$0.00</span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-semibold">Notas de cierre</label>
                        <textarea name="closing_notes" class="form-control" rows="2" placeholder="Observaciones opcionales"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-warning" type="submit"><i class="bi bi-lock me-1"></i> Cerrar caja</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// ─── Auto-abrir modal de cierre si URL contiene #cerrar ──────────────────────
if (window.location.hash === '#cerrar') {
    document.addEventListener('DOMContentLoaded', function () {
        var el = document.getElementById('closeModal');
        if (el) bootstrap.Modal.getOrCreateInstance(el).show();
    });
}

// ─── Categoría auto-selecciona tipo ──────────────────────────────────────────
document.getElementById('mvCategory')?.addEventListener('change', function () {
    const typeEl = document.getElementById('mvType');
    const opt = this.options[this.selectedIndex];
    const t = opt.dataset.type;
    if (t && typeEl) {
        typeEl.value = t;
    }
});

// ─── Modal cierre: modo total vs denominaciones ───────────────────────────────
document.querySelectorAll('[name="close_mode"]').forEach(r => r.addEventListener('change', function () {
    const isBreakdown = this.value === 'breakdown';
    document.getElementById('sectionTotal').style.display     = isBreakdown ? 'none' : '';
    document.getElementById('sectionBreakdown').style.display = isBreakdown ? '' : 'none';
    document.getElementById('closingAmountDirect').required   = !isBreakdown;
}));

// ─── Cálculo en tiempo real del desglose ─────────────────────────────────────
function recalcBreakdown() {
    let total = 0;
    document.querySelectorAll('.denom-input').forEach(input => {
        const qty   = parseFloat(input.value) || 0;
        const val   = parseFloat(input.dataset.value) || 0;
        const sub   = qty * val;
        total += sub;
        const subtotalEl = input.closest('.input-group').querySelector('.denom-subtotal');
        if (subtotalEl) subtotalEl.textContent = '$' + sub.toFixed(2);
    });
    const el = document.getElementById('breakdownTotal');
    if (el) el.textContent = '$' + total.toFixed(2);
}
document.querySelectorAll('.denom-input').forEach(i => i.addEventListener('input', recalcBreakdown));
</script>
@endif
@endsection
