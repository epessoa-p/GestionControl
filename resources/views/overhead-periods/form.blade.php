<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">
                <i class="bi bi-calendar2-week text-primary me-2"></i>
                {{ $period ? 'Editar período' : 'Nuevo período de gastos' }}
            </h1>
            <p class="text-muted mb-0">Define el período y los costos indirectos que lo componen.</p>
        </div>
        <a href="{{ route('overhead-periods.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="{{ $action }}" method="POST" id="overheadForm">
                @csrf
                @if($method !== 'POST') @method($method) @endif

                {{-- Información general --}}
                <div class="mb-1">
                    <h6 class="fw-bold text-primary"><i class="bi bi-info-circle me-1"></i> Información del período</h6>
                    <hr class="mt-2">
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Nombre del período <span class="text-danger">*</span></label>
                        <input type="text" name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $period?->name) }}"
                               placeholder="Ej: Mayo 2026" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Fecha inicio <span class="text-danger">*</span></label>
                        <input type="date" name="period_start"
                               class="form-control @error('period_start') is-invalid @enderror"
                               value="{{ old('period_start', $period?->period_start?->format('Y-m-d') ?? now()->startOfMonth()->format('Y-m-d')) }}"
                               required>
                        @error('period_start')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Fecha fin <span class="text-danger">*</span></label>
                        <input type="date" name="period_end"
                               class="form-control @error('period_end') is-invalid @enderror"
                               value="{{ old('period_end', $period?->period_end?->format('Y-m-d') ?? now()->endOfMonth()->format('Y-m-d')) }}"
                               required>
                        @error('period_end')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- Ítems de gasto --}}
                <div class="mb-2">
                    <h6 class="fw-bold text-primary"><i class="bi bi-list-ul me-1"></i> Ítems de gasto</h6>
                    <hr class="mt-2">
                </div>
                <div class="row g-2 mb-2 text-muted small px-1">
                    <div class="col-md-5"><strong>Concepto</strong></div>
                    <div class="col-md-3"><strong>Categoría</strong></div>
                    <div class="col-md-3"><strong>Monto</strong></div>
                    <div class="col-md-1"></div>
                </div>

                <div id="itemsContainer">
                    @if($period && $period->items->count())
                        @foreach($period->items as $i => $item)
                        <div class="row g-2 mb-2 item-row">
                            <div class="col-md-5">
                                <input type="text" name="items[{{ $i }}][concept]"
                                       class="form-control form-control-sm"
                                       value="{{ $item->concept }}" placeholder="Concepto">
                            </div>
                            <div class="col-md-3">
                                <select name="items[{{ $i }}][category]" class="form-select form-select-sm">
                                    @foreach(\App\Models\OverheadItem::CATEGORY_LABELS as $val => $label)
                                        <option value="{{ $val }}" {{ $item->category === $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" min="0"
                                           name="items[{{ $i }}][amount]"
                                           class="form-control form-control-sm item-amount"
                                           value="{{ $item->amount }}" placeholder="0.00">
                                </div>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-sm btn-outline-danger remove-item"
                                        {{ $i === 0 && $period->items->count() === 1 ? 'disabled' : '' }}>
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    @else
                    <div class="row g-2 mb-2 item-row">
                        <div class="col-md-5">
                            <input type="text" name="items[0][concept]"
                                   class="form-control form-control-sm"
                                   placeholder="Ej: Electricidad, Mano de obra, etc.">
                        </div>
                        <div class="col-md-3">
                            <select name="items[0][category]" class="form-select form-select-sm">
                                @foreach(\App\Models\OverheadItem::CATEGORY_LABELS as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" min="0"
                                       name="items[0][amount]"
                                       class="form-control form-control-sm item-amount"
                                       placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-item" disabled>
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>
                    @endif
                </div>

                <div class="d-flex align-items-center gap-3 mb-4">
                    <button type="button" class="btn btn-sm btn-outline-dark" id="addItem">
                        <i class="bi bi-plus-lg me-1"></i> Agregar ítem
                    </button>
                    <span class="text-muted small">
                        Total estimado:
                        <strong class="text-dark" id="grandTotal">$0.00</strong>
                    </span>
                </div>

                <div class="d-flex gap-2 pt-2 border-top">
                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-check-lg me-1"></i> Guardar período
                    </button>
                    <a href="{{ route('overhead-periods.index') }}" class="btn btn-light border">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    let itemIdx = {{ $period && $period->items->count() ? $period->items->count() : 1 }};

    // ── Opciones de categoría para las filas nuevas ───────────────
    const catOptions = `@foreach(\App\Models\OverheadItem::CATEGORY_LABELS as $val => $label)<option value="{{ $val }}">{{ $label }}</option>@endforeach`;

    // ── Plantilla de fila ─────────────────────────────────────────
    function buildRow(idx) {
        return `
        <div class="row g-2 mb-2 item-row">
            <div class="col-md-5">
                <input type="text" name="items[${idx}][concept]"
                       class="form-control form-control-sm"
                       placeholder="Concepto">
            </div>
            <div class="col-md-3">
                <select name="items[${idx}][category]" class="form-select form-select-sm">
                    ${catOptions}
                </select>
            </div>
            <div class="col-md-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" min="0"
                           name="items[${idx}][amount]"
                           class="form-control form-control-sm item-amount"
                           placeholder="0.00">
                </div>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-sm btn-outline-danger remove-item">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        </div>`;
    }

    // ── Totalizar ─────────────────────────────────────────────────
    function updateTotal() {
        let sum = 0;
        document.querySelectorAll('.item-amount').forEach(i => { sum += parseFloat(i.value) || 0; });
        document.getElementById('grandTotal').textContent = '$' + sum.toFixed(2);
    }

    // ── Agregar fila ──────────────────────────────────────────────
    document.getElementById('addItem').addEventListener('click', function () {
        document.getElementById('itemsContainer').insertAdjacentHTML('beforeend', buildRow(itemIdx++));
        bindRows();
        updateTotal();
    });

    // ── Enlazar eventos ───────────────────────────────────────────
    function bindRows() {
        document.querySelectorAll('.item-amount').forEach(i => { i.oninput = updateTotal; });
        document.querySelectorAll('.remove-item').forEach(btn => {
            btn.onclick = function () {
                if (!this.disabled) {
                    this.closest('.item-row').remove();
                    updateRemoveBtns();
                    updateTotal();
                }
            };
        });
    }

    function updateRemoveBtns() {
        const rows = document.querySelectorAll('.item-row');
        rows.forEach(r => { r.querySelector('.remove-item').disabled = rows.length === 1; });
    }

    bindRows();
    updateTotal();
})();
</script>
@endpush
