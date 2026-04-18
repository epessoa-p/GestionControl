<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><i class="bi bi-arrow-left-right text-primary me-2"></i>Nuevo traspaso</h1>
            <p class="text-muted mb-0">Transfiere productos entre almacenes</p>
        </div>
        <a href="{{ route('transfers.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('transfers.store') }}" method="POST" id="transferForm">
                @csrf

                {{-- Header --}}
                <div class="mb-1">
                    <h6 class="fw-bold text-primary"><i class="bi bi-info-circle me-1"></i> Datos del traspaso</h6>
                    <hr class="mt-2">
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Número</label>
                        <input type="text" class="form-control bg-light" value="{{ $nextNumber }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Fecha <span class="text-danger">*</span></label>
                        <input type="date" name="transfer_date" class="form-control" value="{{ old('transfer_date', now()->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Almacén Origen <span class="text-danger">*</span></label>
                        <select name="from_warehouse_id" class="form-select" required id="fromWarehouse">
                            <option value="">Seleccionar...</option>
                            @foreach($warehouses as $w)
                                <option value="{{ $w->id }}" {{ (string)old('from_warehouse_id') === (string)$w->id ? 'selected' : '' }}>{{ $w->name }} ({{ $w->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Almacén Destino <span class="text-danger">*</span></label>
                        <select name="to_warehouse_id" class="form-select" required id="toWarehouse">
                            <option value="">Seleccionar...</option>
                            @foreach($warehouses as $w)
                                <option value="{{ $w->id }}" {{ (string)old('to_warehouse_id') === (string)$w->id ? 'selected' : '' }}>{{ $w->name }} ({{ $w->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Notas</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Motivo o referencia del traspaso...">{{ old('notes') }}</textarea>
                    </div>
                </div>

                {{-- Products --}}
                <div class="mb-2">
                    <h6 class="fw-bold text-primary"><i class="bi bi-box-seam me-1"></i> Productos a traspasar</h6>
                    <hr class="mt-2">
                </div>
                <div class="row g-2 mb-2 text-muted small">
                    <div class="col-md-5"><strong>Producto</strong></div>
                    <div class="col-md-2"><strong>Stock actual</strong></div>
                    <div class="col-md-3"><strong>Cantidad a traspasar</strong></div>
                    <div class="col-md-2"></div>
                </div>
                <div id="itemsContainer">
                    <div class="row g-2 mb-2 item-row">
                        <div class="col-md-5">
                            <select name="items[0][product_id]" class="form-select form-select-sm prod-sel" required>
                                <option value="">Producto...</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}" data-stock="{{ $p->current_stock }}">{{ $p->name }} ({{ $p->sku }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control form-control-sm bg-light stock-display" readonly placeholder="—">
                        </div>
                        <div class="col-md-3">
                            <input type="number" step="0.01" min="0.01" name="items[0][quantity]" class="form-control form-control-sm qty" placeholder="Cantidad" required>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-item" disabled><i class="bi bi-x"></i></button>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-dark mb-4" id="addItem"><i class="bi bi-plus-lg me-1"></i> Agregar producto</button>

                {{-- Summary --}}
                <div class="row justify-content-end mb-4">
                    <div class="col-md-4">
                        <div class="card bg-light border-0">
                            <div class="card-body py-3 px-3 text-center">
                                <small class="text-muted d-block">Total de productos</small>
                                <h3 class="fw-bold text-primary mb-0" id="totalItems">0</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg me-1"></i> Crear traspaso</button>
                    <a href="{{ route('transfers.index') }}" class="btn btn-light border">Cancelar</a>
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
        row.querySelector('.stock-display').value = '';
        row.querySelector('.remove-item').disabled = false;
        container.appendChild(row);
        itemIndex++;
        bindEvents();
        recalc();
    });

    function recalc() {
        const rows = document.querySelectorAll('.item-row');
        document.getElementById('totalItems').textContent = rows.length;
    }

    function bindEvents() {
        document.querySelectorAll('.remove-item').forEach(btn => {
            btn.onclick = function() { if (!this.disabled) { this.closest('.item-row').remove(); recalc(); } };
        });
        document.querySelectorAll('.prod-sel').forEach(sel => {
            sel.onchange = function() {
                const opt = this.options[this.selectedIndex];
                this.closest('.item-row').querySelector('.stock-display').value = opt.dataset.stock ? parseFloat(opt.dataset.stock).toFixed(2) : '';
            };
        });
    }

    // Validate warehouses are different
    document.getElementById('transferForm').addEventListener('submit', function(e) {
        const from = document.getElementById('fromWarehouse').value;
        const to = document.getElementById('toWarehouse').value;
        if (from && to && from === to) {
            e.preventDefault();
            alert('El almacén origen y destino no pueden ser iguales.');
        }
    });

    bindEvents();
    recalc();
</script>
@endpush
