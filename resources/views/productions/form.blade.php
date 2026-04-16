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
                        <select name="product_id" class="form-select @error('product_id') is-invalid @enderror" required>
                            <option value="">Seleccionar...</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}" {{ (string)old('product_id') === (string)$p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Cantidad a producir <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="quantity_produced" class="form-control @error('quantity_produced') is-invalid @enderror" value="{{ old('quantity_produced') }}" placeholder="0.00" required>
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
                <button type="button" class="btn btn-sm btn-outline-dark mb-4" id="addMaterial"><i class="bi bi-plus-lg me-1"></i> Agregar materia prima</button>

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
                <button type="button" class="btn btn-sm btn-outline-dark mb-4" id="addCost"><i class="bi bi-plus-lg me-1"></i> Agregar costo</button>

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
    let matIdx = 1, costIdx = 1;

    document.getElementById('addMaterial').addEventListener('click', function() {
        const c = document.getElementById('materialsContainer');
        const row = c.querySelector('.material-row').cloneNode(true);
        row.querySelectorAll('select, input').forEach(el => {
            el.name = el.name.replace(/\[\d+\]/, `[${matIdx}]`);
            if (el.tagName === 'SELECT') el.selectedIndex = 0; else el.value = '';
        });
        row.querySelector('.remove-mat').disabled = false;
        c.appendChild(row);
        matIdx++;
        bindMat();
    });

    document.getElementById('addCost').addEventListener('click', function() {
        const c = document.getElementById('costsContainer');
        const row = c.querySelector('.cost-row').cloneNode(true);
        row.querySelectorAll('select, input').forEach(el => {
            el.name = el.name.replace(/\[\d+\]/, `[${costIdx}]`);
            if (el.tagName === 'SELECT') el.selectedIndex = 0; else el.value = '';
        });
        row.querySelector('.remove-cost').disabled = false;
        c.appendChild(row);
        costIdx++;
        bindCost();
    });

    function bindMat() {
        document.querySelectorAll('.remove-mat').forEach(b => { b.onclick = function() { if (!this.disabled) this.closest('.material-row').remove(); }; });
        document.querySelectorAll('.material-row select').forEach(sel => {
            sel.onchange = function() {
                const cost = this.options[this.selectedIndex].dataset.cost || '';
                this.closest('.material-row').querySelector('.mat-cost').value = cost;
            };
        });
    }
    function bindCost() {
        document.querySelectorAll('.remove-cost').forEach(b => { b.onclick = function() { if (!this.disabled) this.closest('.cost-row').remove(); }; });
    }
    bindMat(); bindCost();
</script>
@endpush
