<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">
                <i class="bi bi-journal-text text-primary me-2"></i>
                {{ $recipe ? 'Editar receta' : 'Nueva receta' }}
            </h1>
            <p class="text-muted mb-0">Define el producto final, los ingredientes y las cantidades necesarias.</p>
        </div>
        <a href="{{ route('recipes.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="{{ $action }}" method="POST" id="recipeForm">
                @csrf
                @if($method !== 'POST') @method($method) @endif

                {{-- ── Sección: Información general ── --}}
                <div class="mb-1">
                    <h6 class="fw-bold text-primary"><i class="bi bi-info-circle me-1"></i> Información general</h6>
                    <hr class="mt-2">
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Nº Receta</label>
                        <input type="text" class="form-control bg-light" value="{{ $recipeNumber }}" readonly>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $recipe?->name) }}"
                               placeholder="Ej: Receta estándar de crema hidratante"
                               required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Estado <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            @foreach(\App\Models\Recipe::STATUS_LABELS as $val => $label)
                                <option value="{{ $val }}" {{ old('status', $recipe?->status ?? 'borrador') === $val ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Producto final <span class="text-danger">*</span></label>
                        <select name="product_id" class="form-select @error('product_id') is-invalid @enderror" required>
                            <option value="">Seleccionar producto...</option>
                            @foreach($finalProducts as $p)
                                <option value="{{ $p->id }}"
                                    {{ (string) old('product_id', $recipe?->product_id) === (string) $p->id ? 'selected' : '' }}>
                                    {{ $p->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('product_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Cantidad que produce <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="quantity_produced"
                               class="form-control @error('quantity_produced') is-invalid @enderror"
                               value="{{ old('quantity_produced', $recipe?->quantity_produced ?? 1) }}"
                               placeholder="1.00" required>
                        @error('quantity_produced')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        {{-- Espacio reservado para mantener alineación del grid --}}
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Descripción</label>
                        <textarea name="description" class="form-control" rows="2"
                                  placeholder="Notas o instrucciones generales de la receta...">{{ old('description', $recipe?->description) }}</textarea>
                    </div>
                </div>

                {{-- ── Sección: Ingredientes (materias primas) ── --}}
                <div class="mb-2">
                    <h6 class="fw-bold text-primary"><i class="bi bi-layers me-1"></i> Ingredientes (materias primas)</h6>
                    <hr class="mt-2">
                </div>
                <div class="row g-2 mb-2 text-muted small px-1">
                    <div class="col-md-5"><strong>Materia prima</strong></div>
                    <div class="col-md-2"><strong>Cantidad</strong></div>
                    <div class="col-md-2"><strong>Costo unit.</strong></div>
                    <div class="col-md-2"><strong>Total</strong></div>
                    <div class="col-md-1"></div>
                </div>

                <div id="itemsContainer">
                    @if($recipe && $recipe->items->count())
                        @foreach($recipe->items as $i => $item)
                        <div class="row g-2 mb-2 item-row">
                            <div class="col-md-5">
                                <select name="items[{{ $i }}][product_id]" class="form-select form-select-sm item-product-select">
                                    <option value="">Materia prima...</option>
                                    @foreach($rawMaterials as $rm)
                                        <option value="{{ $rm->id }}"
                                                data-cost="{{ $rm->cost }}"
                                                {{ (string) $item->product_id === (string) $rm->id ? 'selected' : '' }}>
                                            {{ $rm->name }} — {{ $rm->measurementUnit?->symbol ?? $rm->unit }} (Stock: {{ number_format($rm->current_stock, 2) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="number" step="0.01" min="0.01"
                                       name="items[{{ $i }}][quantity]"
                                       class="form-control form-control-sm item-quantity"
                                       value="{{ $item->quantity }}" placeholder="Cantidad">
                            </div>
                            <div class="col-md-2">
                                <input type="number" step="0.01" min="0"
                                       name="items[{{ $i }}][unit_cost]"
                                       class="form-control form-control-sm item-unit-cost"
                                       value="{{ $item->unit_cost }}" placeholder="Costo unit.">
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control form-control-sm item-total bg-light"
                                       value="${{ number_format($item->total_cost, 2) }}" readonly>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-sm btn-outline-danger remove-item"
                                        {{ $i === 0 && $recipe->items->count() === 1 ? 'disabled' : '' }}>
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    @else
                    <div class="row g-2 mb-2 item-row">
                        <div class="col-md-5">
                            <select name="items[0][product_id]" class="form-select form-select-sm item-product-select">
                                <option value="">Materia prima...</option>
                                @foreach($rawMaterials as $rm)
                                    <option value="{{ $rm->id }}" data-cost="{{ $rm->cost }}">
                                        {{ $rm->name }} — {{ $rm->measurementUnit?->symbol ?? $rm->unit }} (Stock: {{ number_format($rm->current_stock, 2) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="number" step="0.01" min="0.01"
                                   name="items[0][quantity]"
                                   class="form-control form-control-sm item-quantity"
                                   placeholder="Cantidad">
                        </div>
                        <div class="col-md-2">
                            <input type="number" step="0.01" min="0"
                                   name="items[0][unit_cost]"
                                   class="form-control form-control-sm item-unit-cost"
                                   placeholder="Costo unit.">
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control form-control-sm item-total bg-light"
                                   placeholder="Total" readonly>
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
                        <i class="bi bi-plus-lg me-1"></i> Agregar ingrediente
                    </button>
                    <span class="text-muted small">
                        Costo estimado total:
                        <strong class="text-dark" id="grandTotal">$0.00</strong>
                    </span>
                </div>

                {{-- ── Acciones ── --}}
                <div class="d-flex gap-2 pt-2 border-top">
                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-check-lg me-1"></i> Guardar receta
                    </button>
                    <a href="{{ route('recipes.index') }}" class="btn btn-light border">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    let itemIdx = {{ $recipe && $recipe->items->count() ? $recipe->items->count() : 1 }};

    // ── Plantilla de fila de ingrediente ──────────────────────────
    function buildRow(idx) {
        const options = Array.from(
            document.querySelector('#itemsContainer .item-product-select').options
        ).map(o => `<option value="${o.value}" data-cost="${o.dataset.cost ?? ''}">${o.text}</option>`).join('');

        return `
        <div class="row g-2 mb-2 item-row">
            <div class="col-md-5">
                <select name="items[${idx}][product_id]" class="form-select form-select-sm item-product-select">
                    ${options}
                </select>
            </div>
            <div class="col-md-2">
                <input type="number" step="0.01" min="0.01" name="items[${idx}][quantity]"
                       class="form-control form-control-sm item-quantity" placeholder="Cantidad">
            </div>
            <div class="col-md-2">
                <input type="number" step="0.01" min="0" name="items[${idx}][unit_cost]"
                       class="form-control form-control-sm item-unit-cost" placeholder="Costo unit.">
            </div>
            <div class="col-md-2">
                <input type="text" class="form-control form-control-sm item-total bg-light" placeholder="Total" readonly>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-sm btn-outline-danger remove-item">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        </div>`;
    }

    // ── Agregar fila ──────────────────────────────────────────────
    document.getElementById('addItem').addEventListener('click', function () {
        document.getElementById('itemsContainer').insertAdjacentHTML('beforeend', buildRow(itemIdx++));
        bindRows();
        updateGrandTotal();
    });

    // ── Calcular total de una fila ────────────────────────────────
    function calcRowTotal(row) {
        const qty  = parseFloat(row.querySelector('.item-quantity')?.value)   || 0;
        const cost = parseFloat(row.querySelector('.item-unit-cost')?.value)  || 0;
        const total = qty * cost;
        row.querySelector('.item-total').value = '$' + total.toFixed(2);
    }

    // ── Totalizar todas las filas ─────────────────────────────────
    function updateGrandTotal() {
        let sum = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const qty  = parseFloat(row.querySelector('.item-quantity')?.value)  || 0;
            const cost = parseFloat(row.querySelector('.item-unit-cost')?.value) || 0;
            sum += qty * cost;
        });
        document.getElementById('grandTotal').textContent = '$' + sum.toFixed(2);
    }

    // ── Enlazar eventos en todas las filas ───────────────────────
    function bindRows() {
        document.querySelectorAll('.item-row').forEach(row => {
            // Auto-rellenar costo unitario al seleccionar materia prima
            const sel = row.querySelector('.item-product-select');
            sel.onchange = function () {
                const opt = this.options[this.selectedIndex];
                const costField = row.querySelector('.item-unit-cost');
                if (opt.dataset.cost) {
                    costField.value = opt.dataset.cost;
                }
                calcRowTotal(row);
                updateGrandTotal();
            };

            // Recalcular al cambiar cantidad o costo
            row.querySelector('.item-quantity').oninput  = () => { calcRowTotal(row); updateGrandTotal(); };
            row.querySelector('.item-unit-cost').oninput = () => { calcRowTotal(row); updateGrandTotal(); };

            // Eliminar fila
            const removeBtn = row.querySelector('.remove-item');
            removeBtn.onclick = function () {
                if (!this.disabled) {
                    row.remove();
                    updateRemoveButtons();
                    updateGrandTotal();
                }
            };
        });
    }

    // ── Deshabilitar botón eliminar cuando solo queda 1 fila ─────
    function updateRemoveButtons() {
        const rows = document.querySelectorAll('.item-row');
        rows.forEach((r, i) => {
            r.querySelector('.remove-item').disabled = (rows.length === 1);
        });
    }

    // ── Inicializar ───────────────────────────────────────────────
    bindRows();
    updateGrandTotal();
})();
</script>
@endpush
