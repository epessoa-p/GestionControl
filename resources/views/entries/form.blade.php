<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><i class="bi bi-box-arrow-in-down text-primary me-2"></i>{{ $entry ? 'Editar entrada' : 'Nueva entrada' }}</h1>
            <p class="text-muted mb-0">Registra ingreso de mercancía al almacén</p>
        </div>
        <a href="{{ route('entries.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="{{ $action }}" method="POST" id="entryForm">
                @csrf
                @if($method !== 'POST') @method($method) @endif

                {{-- Section: General info --}}
                <div class="mb-1">
                    <h6 class="fw-bold text-primary"><i class="bi bi-info-circle me-1"></i> Información general</h6>
                    <hr class="mt-2">
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Número</label>
                        <input type="text" class="form-control bg-light" value="{{ $nextNumber }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Fecha <span class="text-danger">*</span></label>
                        <input type="date" name="entry_date" class="form-control @error('entry_date') is-invalid @enderror" value="{{ old('entry_date', $entry?->entry_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
                        @error('entry_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Almacén <span class="text-danger">*</span></label>
                        <select name="warehouse_id" class="form-select @error('warehouse_id') is-invalid @enderror" required>
                            <option value="">Seleccionar...</option>
                            @foreach($warehouses as $w)
                                <option value="{{ $w->id }}" {{ (string)old('warehouse_id', $entry?->warehouse_id) === (string)$w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                            @endforeach
                        </select>
                        @error('warehouse_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Proveedor</label>
                        <input type="text" name="supplier" class="form-control" value="{{ old('supplier', $entry?->supplier) }}" placeholder="Nombre del proveedor">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Notas</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Observaciones adicionales...">{{ old('notes', $entry?->notes) }}</textarea>
                    </div>
                </div>

                {{-- Section: Products --}}
                <div class="mb-2">
                    <h6 class="fw-bold text-primary"><i class="bi bi-box-seam me-1"></i> Detalle de productos</h6>
                    <hr class="mt-2">
                </div>
                <div class="row g-2 mb-2 text-muted small">
                    <div class="col-md-5"><strong>Producto</strong></div>
                    <div class="col-md-2"><strong>Cantidad</strong></div>
                    <div class="col-md-2"><strong>Costo unit.</strong></div>
                    <div class="col-md-2"><strong>Total</strong></div>
                    <div class="col-md-1"></div>
                </div>
                <div id="itemsContainer">
                    <div class="row g-2 mb-2 item-row">
                        <div class="col-md-5">
                            <select name="items[0][product_id]" class="form-select form-select-sm" required>
                                <option value="">Producto...</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}" data-cost="{{ $p->cost }}">{{ $p->name }} ({{ $p->sku }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="number" step="0.01" min="0.01" name="items[0][quantity]" class="form-control form-control-sm qty" placeholder="Cantidad" required>
                        </div>
                        <div class="col-md-2">
                            <input type="number" step="0.01" min="0" name="items[0][unit_cost]" class="form-control form-control-sm cost" placeholder="Costo unit." required>
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control form-control-sm line-total" placeholder="Total" readonly>
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-item" disabled><i class="bi bi-x"></i></button>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-dark mb-4" id="addItem"><i class="bi bi-plus-lg me-1"></i> Agregar producto</button>

                <div class="d-flex gap-2">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg me-1"></i> Guardar entrada</button>
                    <a href="{{ route('entries.index') }}" class="btn btn-light border">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let itemIndex = 1;
    document.getElementById('addItem').addEventListener('click', function() {
        const container = document.getElementById('itemsContainer');
        const row = container.querySelector('.item-row').cloneNode(true);
        row.querySelectorAll('select, input').forEach(el => {
            el.name = el.name.replace(/\[\d+\]/, `[${itemIndex}]`);
            if (el.tagName === 'SELECT') el.selectedIndex = 0;
            else el.value = '';
        });
        row.querySelector('.remove-item').disabled = false;
        container.appendChild(row);
        itemIndex++;
        bindEvents();
    });

    function bindEvents() {
        document.querySelectorAll('.remove-item').forEach(btn => {
            btn.onclick = function() { if (!this.disabled) this.closest('.item-row').remove(); };
        });
        document.querySelectorAll('.qty, .cost').forEach(el => {
            el.oninput = function() {
                const row = this.closest('.item-row');
                const qty = parseFloat(row.querySelector('.qty').value) || 0;
                const cost = parseFloat(row.querySelector('.cost').value) || 0;
                row.querySelector('.line-total').value = (qty * cost).toFixed(2);
            };
        });
        document.querySelectorAll('.item-row select').forEach(sel => {
            sel.onchange = function() {
                const option = this.options[this.selectedIndex];
                const cost = option.dataset.cost || '';
                this.closest('.item-row').querySelector('.cost').value = cost;
            };
        });
    }
    bindEvents();
</script>
@endpush
