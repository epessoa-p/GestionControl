<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><i class="bi bi-cart3 text-primary me-2"></i>Nueva venta</h1>
            <p class="text-muted mb-0">Registra una nueva venta con detalle de productos</p>
        </div>
        <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="{{ $action }}" method="POST" id="saleForm">
                @csrf
                @if($method !== 'POST') @method($method) @endif

                {{-- Section: Sale info --}}
                <div class="mb-1">
                    <h6 class="fw-bold text-primary"><i class="bi bi-receipt me-1"></i> Datos de la venta</h6>
                    <hr class="mt-2">
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Número</label>
                        <input type="text" class="form-control bg-light" value="{{ $nextNumber }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Fecha <span class="text-danger">*</span></label>
                        <input type="date" name="sale_date" class="form-control" value="{{ old('sale_date', now()->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Método de pago <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-select" required>
                            @foreach(\App\Models\Sale::PAYMENT_LABELS as $val => $label)
                                <option value="{{ $val }}" {{ old('payment_method', 'cash') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Promotor</label>
                        <select name="promoter_id" class="form-select">
                            <option value="">Sin promotor</option>
                            @foreach($promoters as $p)
                                <option value="{{ $p->id }}" {{ (string)old('promoter_id') === (string)$p->id ? 'selected' : '' }}>{{ $p->name }} ({{ $p->commission_rate }}%)</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Section: Client --}}
                <div class="mb-1">
                    <h6 class="fw-bold text-primary"><i class="bi bi-person me-1"></i> Datos del cliente</h6>
                    <hr class="mt-2">
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Cliente</label>
                        <input type="text" name="client_name" class="form-control" value="{{ old('client_name') }}" placeholder="Nombre del cliente">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Teléfono</label>
                        <input type="text" name="client_phone" class="form-control" value="{{ old('client_phone') }}" placeholder="Número de contacto">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Documento</label>
                        <input type="text" name="client_document" class="form-control" value="{{ old('client_document') }}" placeholder="No. de documento">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Sucursal</label>
                        <select name="branch_id" class="form-select">
                            <option value="">—</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ (string)old('branch_id') === (string)$b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Almacén</label>
                        <select name="warehouse_id" class="form-select">
                            <option value="">—</option>
                            @foreach($warehouses as $w)
                                <option value="{{ $w->id }}" {{ (string)old('warehouse_id') === (string)$w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Impuesto</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" min="0" name="tax" class="form-control" value="{{ old('tax', 0) }}" id="taxInput">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Descuento general</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" min="0" name="discount" class="form-control" value="{{ old('discount', 0) }}" id="discountInput">
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Notas</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Observaciones de la venta...">{{ old('notes') }}</textarea>
                    </div>
                </div>

                {{-- Section: Products --}}
                <div class="mb-2">
                    <h6 class="fw-bold text-primary"><i class="bi bi-box-seam me-1"></i> Detalle de productos</h6>
                    <hr class="mt-2">
                </div>
                <div class="row g-2 mb-2 text-muted small">
                    <div class="col-md-4"><strong>Producto</strong></div>
                    <div class="col-md-2"><strong>Cantidad</strong></div>
                    <div class="col-md-2"><strong>Precio unit.</strong></div>
                    <div class="col-md-1"><strong>Desc.</strong></div>
                    <div class="col-md-2"><strong>Total</strong></div>
                    <div class="col-md-1"></div>
                </div>
                <div id="itemsContainer">
                    <div class="row g-2 mb-2 item-row">
                        <div class="col-md-4">
                            <select name="items[0][product_id]" class="form-select form-select-sm prod-sel" required>
                                <option value="">Producto...</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}" data-price="{{ $p->price }}" data-stock="{{ $p->current_stock }}">{{ $p->name }} (Stock: {{ $p->current_stock }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="number" step="0.01" min="0.01" name="items[0][quantity]" class="form-control form-control-sm qty" placeholder="Cantidad" required>
                        </div>
                        <div class="col-md-2">
                            <input type="number" step="0.01" min="0" name="items[0][unit_price]" class="form-control form-control-sm price" placeholder="Precio unit." required>
                        </div>
                        <div class="col-md-1">
                            <input type="number" step="0.01" min="0" name="items[0][discount]" class="form-control form-control-sm disc" placeholder="Desc." value="0">
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control form-control-sm line-total" placeholder="Total" readonly>
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-item" disabled><i class="bi bi-x"></i></button>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-dark mb-3" id="addItem"><i class="bi bi-plus-lg me-1"></i> Agregar producto</button>

                <div class="row justify-content-end mb-4">
                    <div class="col-md-4">
                        <div class="card bg-light border-0">
                            <div class="card-body py-2 px-3">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr><td class="text-muted">Subtotal:</td><td class="text-end fw-semibold" id="subtotalDisplay">$0.00</td></tr>
                                    <tr><td class="text-muted">Impuesto:</td><td class="text-end" id="taxDisplay">$0.00</td></tr>
                                    <tr><td class="text-muted">Descuento:</td><td class="text-end" id="discountDisplay">$0.00</td></tr>
                                    <tr class="border-top"><td class="fw-bold">Total:</td><td class="text-end fw-bold text-primary fs-5" id="totalDisplay">$0.00</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg me-1"></i> Registrar venta</button>
                    <a href="{{ route('sales.index') }}" class="btn btn-light border">Cancelar</a>
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
            else if (el.classList.contains('disc')) el.value = '0';
            else el.value = '';
        });
        row.querySelector('.remove-item').disabled = false;
        container.appendChild(row);
        itemIndex++;
        bindEvents();
    });

    function recalc() {
        let subtotal = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.qty').value) || 0;
            const price = parseFloat(row.querySelector('.price').value) || 0;
            const disc = parseFloat(row.querySelector('.disc').value) || 0;
            const lineTotal = (qty * price) - disc;
            row.querySelector('.line-total').value = lineTotal.toFixed(2);
            subtotal += lineTotal;
        });
        const tax = parseFloat(document.getElementById('taxInput').value) || 0;
        const discount = parseFloat(document.getElementById('discountInput').value) || 0;
        document.getElementById('subtotalDisplay').textContent = '$' + subtotal.toFixed(2);
        document.getElementById('taxDisplay').textContent = '$' + tax.toFixed(2);
        document.getElementById('discountDisplay').textContent = '$' + discount.toFixed(2);
        document.getElementById('totalDisplay').textContent = '$' + (subtotal + tax - discount).toFixed(2);
    }

    function bindEvents() {
        document.querySelectorAll('.remove-item').forEach(btn => {
            btn.onclick = function() { if (!this.disabled) { this.closest('.item-row').remove(); recalc(); } };
        });
        document.querySelectorAll('.qty, .price, .disc').forEach(el => { el.oninput = recalc; });
        document.querySelectorAll('.prod-sel').forEach(sel => {
            sel.onchange = function() {
                const opt = this.options[this.selectedIndex];
                this.closest('.item-row').querySelector('.price').value = opt.dataset.price || '';
                recalc();
            };
        });
    }
    bindEvents();
    document.getElementById('taxInput').oninput = recalc;
    document.getElementById('discountInput').oninput = recalc;
</script>
@endpush
