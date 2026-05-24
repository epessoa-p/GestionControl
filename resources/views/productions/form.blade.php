<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><i class="bi bi-gear text-primary me-2"></i>{{ $production ? 'Editar producción' : 'Nueva producción' }}</h1>
            <p class="text-muted mb-0">Registra una orden de producción con materiales y costos</p>
        </div>
        <a href="{{ route('productions.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="{{ $action }}" method="POST" id="productionForm">
                @csrf
                @if($method !== 'POST') @method($method) @endif

                {{-- Section: General --}}
                <div class="mb-1">
                    <h6 class="fw-bold text-primary"><i class="bi bi-info-circle me-1"></i> Información general</h6>
                    <hr class="mt-2">
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">No. Lote</label>
                        <input type="text" class="form-control bg-light" value="{{ $batchNumber }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Fecha <span class="text-danger">*</span></label>
                        <input type="date" name="production_date" class="form-control @error('production_date') is-invalid @enderror" value="{{ old('production_date', now()->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Producto final <span class="text-danger">*</span></label>
                        <select id="productSelect" name="product_id" class="form-select @error('product_id') is-invalid @enderror" required>
                            <option value="">Seleccionar...</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}" {{ (string)old('product_id') === (string)$p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Selector de receta: aparece dinámicamente al elegir un producto --}}
                    <div class="col-md-3" id="recipeSelectorWrapper" style="display:none">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-journal-text me-1 text-primary"></i> Cargar desde receta
                        </label>
                        <select id="recipeSelector" class="form-select">
                            <option value="">— Seleccionar receta —</option>
                        </select>
                        <div class="form-text">Opcional · rellena los materiales automáticamente.</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Cantidad a producir <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" id="quantityInput" name="quantity_produced" class="form-control @error('quantity_produced') is-invalid @enderror" value="{{ old('quantity_produced') }}" placeholder="0.00" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Notas</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Observaciones de producción...">{{ old('notes') }}</textarea>
                    </div>
                </div>

                {{-- Section: Materials --}}
                <div class="mb-2">
                    <h6 class="fw-bold text-primary"><i class="bi bi-layers me-1"></i> Materias primas</h6>
                    <hr class="mt-2">
                </div>
                <div class="row g-2 mb-2 text-muted small">
                    <div class="col-md-4"><strong>Materia prima</strong></div>
                    <div class="col-md-2"><strong>Cantidad</strong></div>
                    <div class="col-md-2"><strong>Costo unit.</strong></div>
                    <div class="col-md-3"><strong>Total</strong></div>
                    <div class="col-md-1"></div>
                </div>
                <div id="materialsContainer">
                    <div class="row g-2 mb-2 material-row">
                        <div class="col-md-4">
                            <select name="materials[0][product_id]" class="form-select form-select-sm">
                                <option value="">Materia prima...</option>
                                @foreach($rawMaterials as $rm)
                                    <option value="{{ $rm->id }}" data-cost="{{ $rm->cost }}">{{ $rm->name }} (Stock: {{ $rm->current_stock }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="number" step="0.01" min="0.01" name="materials[0][quantity_used]" class="form-control form-control-sm" placeholder="Cantidad">
                        </div>
                        <div class="col-md-2">
                            <input type="number" step="0.01" min="0" name="materials[0][unit_cost]" class="form-control form-control-sm mat-cost" placeholder="Costo unit.">
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control form-control-sm" placeholder="Total" readonly>
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-mat" disabled><i class="bi bi-x"></i></button>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <button type="button" class="btn btn-sm btn-outline-dark" id="addMaterial">
                        <i class="bi bi-plus-lg me-1"></i> Agregar materia prima
                    </button>
                    <div class="text-end">
                        <span class="text-muted small">Subtotal materiales:</span>
                        <strong id="matSubtotal" class="ms-1 text-dark">$0.00</strong>
                    </div>
                </div>

                {{-- Section: Additional costs --}}
                <div class="mb-2">
                    <h6 class="fw-bold text-primary"><i class="bi bi-currency-dollar me-1"></i> Costos adicionales</h6>
                    <hr class="mt-2">
                </div>
                <div class="row g-2 mb-2 text-muted small">
                    <div class="col-md-4"><strong>Concepto</strong></div>
                    <div class="col-md-3"><strong>Tipo</strong></div>
                    <div class="col-md-3"><strong>Monto</strong></div>
                    <div class="col-md-2"></div>
                </div>
                <div id="costsContainer">
                    <div class="row g-2 mb-2 cost-row">
                        <div class="col-md-4">
                            <input type="text" name="costs[0][concept]" class="form-control form-control-sm" placeholder="Concepto">
                        </div>
                        <div class="col-md-3">
                            <select name="costs[0][type]" class="form-select form-select-sm">
                                <option value="direct">Directo</option>
                                <option value="indirect">Indirecto</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="number" step="0.01" min="0" name="costs[0][amount]" class="form-control form-control-sm" placeholder="Monto">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-cost" disabled><i class="bi bi-x"></i></button>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <button type="button" class="btn btn-sm btn-outline-dark" id="addCost">
                        <i class="bi bi-plus-lg me-1"></i> Agregar costo
                    </button>
                    <div class="text-end">
                        <span class="text-muted small">Subtotal costos:</span>
                        <strong id="costSubtotal" class="ms-1 text-dark">$0.00</strong>
                    </div>
                </div>

                {{-- Resumen de totales --}}
                <div class="d-flex justify-content-between align-items-center px-3 py-2 mb-4 rounded-3 border" style="background:#f8f9fa">
                    <div class="d-flex gap-4 text-muted small">
                        <span>Materiales: <strong id="matSummary" class="text-dark">$0.00</strong></span>
                        <span>Costos adicionales: <strong id="costSummary" class="text-dark">$0.00</strong></span>
                    </div>
                    <div>
                        <span class="text-muted small me-2">Total estimado:</span>
                        <strong id="grandTotal" class="fs-5 text-primary">$0.00</strong>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg me-1"></i> Guardar producción</button>
                    <a href="{{ route('productions.index') }}" class="btn btn-light border">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    let matIdx = 1, costIdx = 1;
    let recipeBaseQty = 1; // cantidad_produce de la receta cargada

    const quantityInput         = document.getElementById('quantityInput');
    const productSelect         = document.getElementById('productSelect');
    const recipeSelector        = document.getElementById('recipeSelector');
    const recipeSelectorWrapper = document.getElementById('recipeSelectorWrapper');

    // ── Captura opciones de materias primas antes de cualquier cambio en DOM ──
    const matOptionsHtml = Array.from(
        document.querySelector('.material-row select')?.options ?? []
    ).map(o => `<option value="${o.value}" data-cost="${o.dataset.cost ?? ''}">${o.text}</option>`).join('');

    // ── Calcular total de una fila de material ────────────────────
    function calcRowTotal(row) {
        const qty   = parseFloat(row.querySelector('input[name*="quantity_used"]')?.value) || 0;
        const cost  = parseFloat(row.querySelector('.mat-cost')?.value) || 0;
        const field = row.querySelector('input[readonly]');
        if (field) field.value = '$' + (qty * cost).toFixed(2);
    }

    // ── Actualizar todos los totales del formulario ───────────────
    function calcTotals() {
        let matSum = 0;
        document.querySelectorAll('.material-row').forEach(row => {
            const qty  = parseFloat(row.querySelector('input[name*="quantity_used"]')?.value) || 0;
            const cost = parseFloat(row.querySelector('.mat-cost')?.value) || 0;
            matSum += qty * cost;
        });

        let costSum = 0;
        document.querySelectorAll('.cost-row input[name*="amount"]').forEach(i => {
            costSum += parseFloat(i.value) || 0;
        });

        const fmt = v => '$' + v.toFixed(2);
        document.getElementById('matSubtotal').textContent  = fmt(matSum);
        document.getElementById('matSummary').textContent   = fmt(matSum);
        document.getElementById('costSubtotal').textContent = fmt(costSum);
        document.getElementById('costSummary').textContent  = fmt(costSum);
        document.getElementById('grandTotal').textContent   = fmt(matSum + costSum);
    }

    // ── Enlazar eventos en filas de material ──────────────────────
    function bindMat() {
        document.querySelectorAll('.remove-mat').forEach(b => {
            b.onclick = function () {
                if (!this.disabled) { this.closest('.material-row').remove(); calcTotals(); }
            };
        });
        document.querySelectorAll('.material-row').forEach(row => {
            const sel       = row.querySelector('select');
            const qtyInput  = row.querySelector('input[name*="quantity_used"]');
            const costInput = row.querySelector('.mat-cost');

            sel.onchange = function () {
                costInput.value = this.options[this.selectedIndex].dataset.cost || '';
                calcRowTotal(row); calcTotals();
            };
            qtyInput.oninput  = function () { calcRowTotal(row); calcTotals(); };
            costInput.oninput = function () { calcRowTotal(row); calcTotals(); };
        });
    }

    // ── Enlazar eventos en filas de costo ─────────────────────────
    function bindCost() {
        document.querySelectorAll('.remove-cost').forEach(b => {
            b.onclick = function () {
                if (!this.disabled) { this.closest('.cost-row').remove(); calcTotals(); }
            };
        });
        document.querySelectorAll('.cost-row input[name*="amount"]').forEach(i => {
            i.oninput = calcTotals;
        });
    }

    // ── Agregar fila de material ──────────────────────────────────
    document.getElementById('addMaterial').addEventListener('click', function () {
        const c   = document.getElementById('materialsContainer');
        const row = c.querySelector('.material-row').cloneNode(true);
        row.querySelectorAll('select, input').forEach(el => {
            el.name = el.name.replace(/\[\d+\]/, `[${matIdx}]`);
            if (el.tagName === 'SELECT') el.selectedIndex = 0; else el.value = '';
        });
        row.querySelector('.remove-mat').disabled = false;
        row.querySelector('input[name*="quantity_used"]').removeAttribute('data-base-qty');
        c.appendChild(row);
        matIdx++;
        bindMat(); calcTotals();
    });

    // ── Agregar fila de costo ─────────────────────────────────────
    document.getElementById('addCost').addEventListener('click', function () {
        const c   = document.getElementById('costsContainer');
        const row = c.querySelector('.cost-row').cloneNode(true);
        row.querySelectorAll('select, input').forEach(el => {
            el.name = el.name.replace(/\[\d+\]/, `[${costIdx}]`);
            if (el.tagName === 'SELECT') el.selectedIndex = 0; else el.value = '';
        });
        row.querySelector('.remove-cost').disabled = false;
        c.appendChild(row);
        costIdx++;
        bindCost(); calcTotals();
    });

    // ── Recalcular cantidades al cambiar "Cantidad a producir" ────
    quantityInput?.addEventListener('input', function () {
        const prodQty = parseFloat(this.value) || 0;
        if (!prodQty || recipeBaseQty <= 0) return;
        const factor = prodQty / recipeBaseQty;

        document.querySelectorAll('.material-row').forEach(row => {
            const qtyInput = row.querySelector('input[name*="quantity_used"]');
            const baseQty  = parseFloat(qtyInput?.dataset.baseQty) || 0;
            if (baseQty && qtyInput) {
                qtyInput.value = (baseQty * factor).toFixed(4);
                calcRowTotal(row);
            }
        });
        calcTotals();
    });

    // ── Al cambiar el producto: cargar recetas relacionadas ───────
    productSelect?.addEventListener('change', async function () {
        const productId = this.value;
        recipeSelectorWrapper.style.display = 'none';
        recipeSelector.innerHTML = '<option value="">— Seleccionar receta —</option>';
        if (!productId) return;
        try {
            const recipes = await fetch(`/recipes/by-product/${productId}`).then(r => r.json());
            if (!recipes.length) return;
            recipes.forEach(r => {
                const opt = document.createElement('option');
                opt.value = r.id; opt.textContent = r.name;
                recipeSelector.appendChild(opt);
            });
            recipeSelectorWrapper.style.display = '';
        } catch (e) { console.error('Error al cargar recetas:', e); }
    });

    // ── Al seleccionar receta: rellenar materiales con escala ─────
    recipeSelector?.addEventListener('change', async function () {
        const recipeId = this.value;
        if (!recipeId) return;
        try {
            const data = await fetch(`/recipes/${recipeId}/items`).then(r => r.json());
            if (!data.items?.length) return;

            recipeBaseQty = data.quantity_produced || 1;
            const prodQty = parseFloat(quantityInput?.value) || recipeBaseQty;
            const factor  = prodQty / recipeBaseQty;

            const container = document.getElementById('materialsContainer');
            container.innerHTML = '';
            matIdx = 0;

            data.items.forEach((item, idx) => {
                const scaledQty   = (item.quantity * factor).toFixed(4);
                const rowTotal    = (scaledQty * item.unit_cost).toFixed(2);
                const optSelected = matOptionsHtml.replace(
                    new RegExp(`value="${item.product_id}"`),
                    `value="${item.product_id}" selected`
                );
                container.insertAdjacentHTML('beforeend', `
                <div class="row g-2 mb-2 material-row">
                    <div class="col-md-4">
                        <select name="materials[${idx}][product_id]" class="form-select form-select-sm">
                            ${optSelected}
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="number" step="0.0001" min="0.0001"
                               name="materials[${idx}][quantity_used]"
                               class="form-control form-control-sm"
                               value="${scaledQty}"
                               data-base-qty="${item.quantity}"
                               placeholder="Cantidad">
                    </div>
                    <div class="col-md-2">
                        <input type="number" step="0.01" min="0"
                               name="materials[${idx}][unit_cost]"
                               class="form-control form-control-sm mat-cost"
                               value="${item.unit_cost}" placeholder="Costo unit.">
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control form-control-sm"
                               value="$${rowTotal}" placeholder="Total" readonly>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-mat">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                </div>`);
                matIdx = idx + 1;
            });

            bindMat(); calcTotals();
        } catch (e) { console.error('Error al cargar ingredientes de receta:', e); }
    });

    // ── Inicializar ───────────────────────────────────────────────
    bindMat(); bindCost(); calcTotals();
})();
</script>
@endpush
