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

                {{-- Sale Type Tabs --}}
                <ul class="nav nav-tabs nav-fill mb-4" id="saleTypeTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold" id="cash-tab" data-bs-toggle="tab" data-bs-target="#cashPanel" type="button" role="tab" onclick="setSaleType('cash')">
                            <i class="bi bi-cash-coin me-2"></i>Venta de Contado
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold" id="credit-tab" data-bs-toggle="tab" data-bs-target="#creditPanel" type="button" role="tab" onclick="setSaleType('credit')">
                            <i class="bi bi-credit-card-2-front me-2"></i>Venta a Crédito
                        </button>
                    </li>
                </ul>
                <input type="hidden" name="sale_type" id="saleTypeInput" value="{{ old('sale_type', 'cash') }}">

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

                @php
                    $oldClientMode = old('client_mode', 'registered');
                    $oldClientId   = old('client_id', '');
                    $oldClientName = old('client_name', '');
                    $oldClientPhone= old('client_phone', '');
                    $oldClientDoc  = old('client_document', '');
                @endphp

                {{-- Campos ocultos: siempre se envían --}}
                <input type="hidden" name="client_mode"     id="clientModeInput"  value="{{ $oldClientMode }}">
                <input type="hidden" name="client_id"       id="clientIdInput"    value="{{ $oldClientId }}">
                <input type="hidden" name="client_name"     id="clientNameInput"  value="{{ $oldClientName }}">
                <input type="hidden" name="client_phone"    id="clientPhoneInput" value="{{ $oldClientPhone }}">
                <input type="hidden" name="client_document" id="clientDocInput"   value="{{ $oldClientDoc }}">

                {{-- Toggle de modo --}}
                <div class="d-flex align-items-center flex-wrap gap-3 mb-3">
                    <div class="btn-group shadow-sm" role="group" aria-label="Modo de cliente">
                        <button type="button" id="btnModeRegistered"
                                class="btn btn-sm {{ $oldClientMode !== 'quick' ? 'btn-primary' : 'btn-outline-secondary' }}"
                                onclick="setClientMode('registered')">
                            <i class="bi bi-person-check me-1"></i> Cliente registrado
                        </button>
                        <button type="button" id="btnModeQuick"
                                class="btn btn-sm {{ $oldClientMode === 'quick' ? 'btn-warning text-dark' : 'btn-outline-secondary' }}"
                                onclick="setClientMode('quick')">
                            <i class="bi bi-lightning-charge me-1"></i> Venta rápida
                        </button>
                    </div>
                    <small id="clientModeDesc" class="text-muted fst-italic">
                        {{ $oldClientMode === 'quick' ? 'Solo se guarda el nombre (sin vinculación al CRM)' : 'Busca y vincula un cliente del CRM' }}
                    </small>
                </div>

                {{-- ── MODO: Cliente registrado ── --}}
                <div id="clientRegisteredSection"{{ $oldClientMode === 'quick' ? ' style="display:none;"' : '' }}>
                    <div class="row g-3 mb-2">
                        <div class="col-md-7 col-lg-6">
                            <label class="form-label fw-semibold small text-muted text-uppercase" style="letter-spacing:.04em;">Buscar cliente en el CRM</label>
                            <div class="position-relative" id="clientSearchWrapper">
                                <div class="input-group">
                                    <span class="input-group-text bg-white">
                                        <i class="bi bi-search text-muted" id="searchSpinIcon"></i>
                                    </span>
                                    <input type="text"
                                           id="clientSearchInput"
                                           class="form-control border-start-0 ps-0"
                                           placeholder="Nombre, RUC, cédula, teléfono..."
                                           autocomplete="off"
                                           value="{{ $oldClientMode !== 'quick' && $oldClientName ? $oldClientName : '' }}">
                                    <button type="button"
                                            class="btn btn-outline-secondary"
                                            id="clientSearchClearBtn"
                                            onclick="clearClientSelection()"
                                            style="{{ $oldClientId ? '' : 'display:none;' }}"
                                            title="Quitar cliente seleccionado">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                                {{-- Dropdown de resultados --}}
                                <div id="clientDropdown"
                                     style="display:none; position:absolute; top:100%; left:0; right:0; z-index:1055; max-height:300px; overflow-y:auto;"
                                     class="border rounded-3 shadow-lg bg-white mt-1">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tarjeta de cliente seleccionado --}}
                    <div id="clientSelectedCard" class="{{ $oldClientId ? '' : 'd-none' }} mb-2">
                        <div class="d-flex align-items-center gap-3 p-3 rounded-3"
                             style="background:linear-gradient(135deg,#f0f6ff 0%,#e8f0fe 100%);border-left:4px solid #1a73e8;">
                            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white flex-shrink-0"
                                 id="clientCardAvatar"
                                 style="width:44px;height:44px;font-size:1.1rem;background:#1a73e8;">
                                {{ $oldClientName ? strtoupper(substr($oldClientName, 0, 1)) : '?' }}
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-semibold" id="clientCardName">{{ $oldClientName ?: '—' }}</div>
                                <div class="small text-muted" id="clientCardMeta">
                                    @if($oldClientPhone)<i class="bi bi-telephone me-1"></i>{{ $oldClientPhone }}@endif
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                <span class="badge bg-success bg-opacity-15 text-success border border-success border-opacity-25">
                                    <i class="bi bi-check-circle me-1"></i>Vinculado
                                </span>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearClientSelection()">
                                    <i class="bi bi-arrow-repeat me-1"></i>Cambiar
                                </button>
                            </div>
                        </div>
                    </div>
                    <div id="clientSearchHint" class="{{ $oldClientId ? 'd-none' : '' }} mb-3">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Escribe para buscar un cliente. Si no existe, usa <strong>Venta rápida</strong> o
                            <a href="{{ route('crm.clients.create') }}" target="_blank" class="text-decoration-none"><i class="bi bi-plus-circle me-1"></i>regístralo en el CRM</a>.
                        </small>
                    </div>
                </div>

                {{-- ── MODO: Venta rápida ── --}}
                <div id="clientQuickSection"{{ $oldClientMode !== 'quick' ? ' style="display:none;"' : '' }}>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Nombre del cliente</label>
                            <input type="text" id="quickName" class="form-control"
                                   placeholder="Nombre o empresa..."
                                   value="{{ $oldClientMode === 'quick' ? $oldClientName : '' }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Teléfono</label>
                            <input type="text" id="quickPhone" class="form-control"
                                   placeholder="Número de contacto"
                                   value="{{ $oldClientMode === 'quick' ? $oldClientPhone : '' }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Documento</label>
                            <input type="text" id="quickDoc" class="form-control"
                                   placeholder="Cédula / RUC / Pasaporte"
                                   value="{{ $oldClientMode === 'quick' ? $oldClientDoc : '' }}">
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="bi bi-lightning-charge text-warning me-1"></i>
                        Solo se guardará el nombre en la venta.
                        <a href="{{ route('crm.clients.create') }}" target="_blank" class="text-decoration-none ms-1">
                            <i class="bi bi-plus-circle me-1"></i>Registrar cliente en el CRM
                        </a>
                    </small>
                </div>

                <hr class="my-3">

                {{-- Configuración de la venta --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Sucursal</label>
                        <select name="branch_id" id="branchSelect" class="form-select">
                            <option value="">—</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}"
                                        data-warehouse="{{ $b->warehouse_id }}"
                                        {{ (string)old('branch_id') === (string)$b->id ? 'selected' : '' }}>
                                    {{ $b->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Almacén</label>
                        <select name="warehouse_id" id="warehouseSelect" class="form-select">
                            <option value="">—</option>
                            @foreach($warehouses as $w)
                                <option value="{{ $w->id }}" {{ (string)old('warehouse_id') === (string)$w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Impuesto</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" min="0" name="tax" class="form-control" value="{{ old('tax', 0) }}" id="taxInput">
                        </div>
                    </div>
                    <div class="col-md-3">
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

                {{-- Credit Installments Section (hidden by default) --}}
                <div id="creditSection" style="display:none;">
                    <div class="mb-2">
                        <h6 class="fw-bold text-success"><i class="bi bi-calendar3 me-1"></i> Plan de cuotas</h6>
                        <hr class="mt-2">
                    </div>
                    <div class="alert alert-info d-flex align-items-center py-2 mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        <small>Define las cuotas por monto o porcentaje. La suma de porcentajes debe ser 100% y la suma de montos debe igualar el total de la venta.</small>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Tipo de distribución</label>
                            <select class="form-select form-select-sm" id="installmentMode">
                                <option value="percentage">Por porcentaje</option>
                                <option value="amount">Por monto</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold small">Nro. cuotas</label>
                            <input type="number" min="1" max="60" class="form-control form-control-sm" id="numInstallments" value="3">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-sm btn-outline-success" id="generateInstallments"><i class="bi bi-magic me-1"></i> Generar</button>
                        </div>
                        <div class="col-md-5 d-flex align-items-end justify-content-end">
                            <div class="text-end">
                                <small class="text-muted">Porcentaje asignado: </small>
                                <span class="fw-bold" id="percentageSum">0%</span>
                                <span class="mx-2">|</span>
                                <small class="text-muted">Monto asignado: </small>
                                <span class="fw-bold" id="amountSum">$0.00</span>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle" id="installmentsTable">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width:60px;">#</th>
                                    <th>Fecha vencimiento</th>
                                    <th>Porcentaje (%)</th>
                                    <th>Monto ($)</th>
                                    <th style="width:60px;"></th>
                                </tr>
                            </thead>
                            <tbody id="installmentsBody">
                                {{-- Dynamic rows --}}
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-dark mb-3" id="addInstallment"><i class="bi bi-plus-lg me-1"></i> Agregar cuota</button>

                    <div id="installmentValidation" class="mb-3" style="display:none;">
                        <div class="alert alert-danger py-2 mb-0">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            <span id="installmentValidationMsg"></span>
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

@push('styles')
<style>
    /* ── Client search ─────────────────────────────── */
    #clientSearchWrapper .input-group:focus-within {
        box-shadow: 0 0 0 .2rem rgba(13,110,253,.15);
        border-radius: .375rem;
    }
    .client-result-item {
        cursor: pointer;
        transition: background .12s;
        border-bottom: 1px solid #f0f0f0;
    }
    .client-result-item:last-child { border-bottom: none; }
    .client-result-item:hover { background: #f5f8ff; }

    /* Spin animation for search icon */
    @keyframes spin { to { transform: rotate(360deg); } }
    .client-spin { animation: spin .7s linear infinite; display: inline-block; }
</style>
@endpush

@push('scripts')
<script>
    let itemIndex = 1;
    let installmentIndex = 0;

    // ─── Sale type toggle ───
    function setSaleType(type) {
        document.getElementById('saleTypeInput').value = type;
        document.getElementById('creditSection').style.display = type === 'credit' ? 'block' : 'none';
    }

    // Restore tab on validation error
    @if(old('sale_type') === 'credit')
        document.getElementById('credit-tab').click();
    @endif

    // ─── Product items ───
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

    function getGrandTotal() {
        const text = document.getElementById('totalDisplay').textContent;
        return parseFloat(text.replace('$', '').replace(',', '')) || 0;
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

    // ─── Installments logic ───
    function addInstallmentRow(num, dueDate, percentage, amount) {
        const tbody = document.getElementById('installmentsBody');
        const idx = installmentIndex;
        const tr = document.createElement('tr');
        tr.className = 'installment-row';
        tr.innerHTML = `
            <td class="text-center fw-semibold text-muted">${num}</td>
            <td><input type="date" name="installments[${idx}][due_date]" class="form-control form-control-sm inst-date" value="${dueDate}" required></td>
            <td><input type="number" step="0.01" min="0" max="100" name="installments[${idx}][percentage]" class="form-control form-control-sm inst-pct" value="${percentage}" placeholder="%"></td>
            <td><input type="number" step="0.01" min="0" name="installments[${idx}][amount]" class="form-control form-control-sm inst-amt" value="${amount}" placeholder="$"></td>
            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-installment"><i class="bi bi-x"></i></button></td>
        `;
        tbody.appendChild(tr);
        installmentIndex++;
        bindInstallmentEvents();
    }

    function bindInstallmentEvents() {
        document.querySelectorAll('.remove-installment').forEach(btn => {
            btn.onclick = function() {
                this.closest('.installment-row').remove();
                renumberInstallments();
                validateInstallments();
            };
        });
        document.querySelectorAll('.inst-pct').forEach(el => {
            el.oninput = function() {
                const mode = document.getElementById('installmentMode').value;
                if (mode === 'percentage') {
                    const pct = parseFloat(this.value) || 0;
                    const total = getGrandTotal();
                    this.closest('tr').querySelector('.inst-amt').value = ((pct / 100) * total).toFixed(2);
                }
                validateInstallments();
            };
        });
        document.querySelectorAll('.inst-amt').forEach(el => {
            el.oninput = function() {
                const mode = document.getElementById('installmentMode').value;
                if (mode === 'amount') {
                    const amt = parseFloat(this.value) || 0;
                    const total = getGrandTotal();
                    this.closest('tr').querySelector('.inst-pct').value = total > 0 ? ((amt / total) * 100).toFixed(2) : 0;
                }
                validateInstallments();
            };
        });
    }

    function renumberInstallments() {
        document.querySelectorAll('.installment-row').forEach((row, idx) => {
            row.querySelector('td:first-child').textContent = idx + 1;
        });
    }

    function validateInstallments() {
        let totalPct = 0, totalAmt = 0;
        document.querySelectorAll('.installment-row').forEach(row => {
            totalPct += parseFloat(row.querySelector('.inst-pct').value) || 0;
            totalAmt += parseFloat(row.querySelector('.inst-amt').value) || 0;
        });

        const pctEl = document.getElementById('percentageSum');
        const amtEl = document.getElementById('amountSum');
        pctEl.textContent = totalPct.toFixed(2) + '%';
        amtEl.textContent = '$' + totalAmt.toFixed(2);

        pctEl.className = Math.abs(totalPct - 100) < 0.01 ? 'fw-bold text-success' : 'fw-bold text-danger';

        const total = getGrandTotal();
        amtEl.className = Math.abs(totalAmt - total) < 0.01 ? 'fw-bold text-success' : 'fw-bold text-danger';

        const valDiv = document.getElementById('installmentValidation');
        const valMsg = document.getElementById('installmentValidationMsg');

        if (document.querySelectorAll('.installment-row').length > 0) {
            if (Math.abs(totalPct - 100) > 0.01) {
                valDiv.style.display = 'block';
                valMsg.textContent = 'La suma de porcentajes debe ser exactamente 100%. Actual: ' + totalPct.toFixed(2) + '%';
            } else if (Math.abs(totalAmt - total) > 0.01) {
                valDiv.style.display = 'block';
                valMsg.textContent = 'La suma de montos ($' + totalAmt.toFixed(2) + ') no coincide con el total de la venta ($' + total.toFixed(2) + ').';
            } else {
                valDiv.style.display = 'none';
            }
        } else {
            valDiv.style.display = 'none';
        }
    }

    document.getElementById('generateInstallments').addEventListener('click', function() {
        const n = parseInt(document.getElementById('numInstallments').value) || 3;
        const total = getGrandTotal();
        const mode = document.getElementById('installmentMode').value;

        // Clear existing
        document.getElementById('installmentsBody').innerHTML = '';
        installmentIndex = 0;

        const pctEach = Math.floor((10000 / n)) / 100; // even split
        const amtEach = Math.floor((total / n) * 100) / 100;

        for (let i = 0; i < n; i++) {
            const date = new Date();
            date.setMonth(date.getMonth() + i + 1);
            const dateStr = date.toISOString().split('T')[0];

            let pct = pctEach, amt = amtEach;
            // Last installment absorbs rounding difference
            if (i === n - 1) {
                if (mode === 'percentage') {
                    pct = Math.round((100 - pctEach * (n - 1)) * 100) / 100;
                    amt = Math.round((total - amtEach * (n - 1)) * 100) / 100;
                } else {
                    amt = Math.round((total - amtEach * (n - 1)) * 100) / 100;
                    pct = total > 0 ? Math.round((amt / total) * 10000) / 100 : 0;
                }
            }
            addInstallmentRow(i + 1, dateStr, pct.toFixed(2), amt.toFixed(2));
        }
        validateInstallments();
    });

    document.getElementById('addInstallment').addEventListener('click', function() {
        const rows = document.querySelectorAll('.installment-row');
        const num = rows.length + 1;
        const date = new Date();
        date.setMonth(date.getMonth() + num);
        addInstallmentRow(num, date.toISOString().split('T')[0], '', '');
    });

    // ─── Client search ──────────────────────────────────────────────────────
    const CLIENT_SEARCH_URL    = '{{ route("crm.clients.search") }}';
    const CLIENT_TYPE_COLORS   = { persona_natural: '#1a73e8', empresa: '#e67e22' };
    let   clientSearchTimer    = null;

    /** Cambia entre modo "cliente registrado" y "venta rápida" */
    function setClientMode(mode) {
        document.getElementById('clientModeInput').value = mode;
        const isReg = mode === 'registered';

        document.getElementById('clientRegisteredSection').style.display = isReg ? '' : 'none';
        document.getElementById('clientQuickSection').style.display      = isReg ? 'none' : '';

        document.getElementById('btnModeRegistered').className =
            'btn btn-sm ' + (isReg ? 'btn-primary' : 'btn-outline-secondary');
        document.getElementById('btnModeQuick').className =
            'btn btn-sm ' + (!isReg ? 'btn-warning text-dark' : 'btn-outline-secondary');

        document.getElementById('clientModeDesc').textContent = isReg
            ? 'Busca y vincula un cliente del CRM'
            : 'Solo se guarda el nombre (sin vinculación al CRM)';

        if (!isReg) {
            // Al cambiar a venta rápida, limpiamos la vinculación
            document.getElementById('clientIdInput').value = '';
            document.getElementById('quickName').focus();
        } else {
            document.getElementById('clientSearchInput').focus();
        }
    }

    /** Limpia la selección del cliente registrado */
    function clearClientSelection() {
        document.getElementById('clientIdInput').value    = '';
        document.getElementById('clientNameInput').value  = '';
        document.getElementById('clientPhoneInput').value = '';
        document.getElementById('clientDocInput').value   = '';
        document.getElementById('clientSearchInput').value = '';
        document.getElementById('clientSearchClearBtn').style.display = 'none';
        document.getElementById('clientSelectedCard').classList.add('d-none');
        document.getElementById('clientSearchHint').classList.remove('d-none');
        document.getElementById('clientDropdown').style.display = 'none';
        document.getElementById('clientSearchInput').focus();
    }

    /** Rellena el formulario con el cliente seleccionado */
    function selectClient(client) {
        document.getElementById('clientIdInput').value    = client.id;
        document.getElementById('clientNameInput').value  = client.display_name;
        document.getElementById('clientPhoneInput').value = client.phone  || '';
        document.getElementById('clientDocInput').value   = client.document || '';

        // Avatar y tarjeta
        const color  = CLIENT_TYPE_COLORS[client.type] || '#6c757d';
        const avatar = document.getElementById('clientCardAvatar');
        avatar.style.background = color;
        avatar.textContent = client.display_name.charAt(0).toUpperCase();

        document.getElementById('clientCardName').textContent = client.display_name;

        const meta = [];
        if (client.client_number) meta.push(client.client_number);
        if (client.phone)         meta.push('📞 ' + client.phone);
        if (client.document)      meta.push(client.document);
        document.getElementById('clientCardMeta').textContent = meta.join(' · ');

        document.getElementById('clientSelectedCard').classList.remove('d-none');
        document.getElementById('clientSearchHint').classList.add('d-none');
        document.getElementById('clientSearchClearBtn').style.display = '';
        document.getElementById('clientSearchInput').value = client.display_name;
        document.getElementById('clientDropdown').style.display = 'none';
    }

    /** Renderiza los resultados en el dropdown */
    function renderClientResults(clients) {
        const dd = document.getElementById('clientDropdown');
        if (clients.length === 0) {
            dd.innerHTML = `
                <div class="py-3 px-4 text-center text-muted small">
                    <i class="bi bi-person-x me-1"></i>Sin resultados.
                    <a href="{{ route('crm.clients.create') }}" target="_blank" class="ms-1">
                        <i class="bi bi-plus-circle me-1"></i>Registrar cliente
                    </a>
                </div>`;
        } else {
            dd.innerHTML = clients.map(c => {
                const color   = CLIENT_TYPE_COLORS[c.type] || '#6c757d';
                const initial = c.display_name.charAt(0).toUpperCase();
                const meta    = [c.client_number, c.phone].filter(Boolean).join(' · ');
                const badge   = c.status === 'prospecto'
                    ? '<span class="badge bg-warning bg-opacity-15 text-warning ms-1" style="font-size:.68rem;">Prospecto</span>'
                    : '';
                // JSON.stringify for onclick — escape properly
                const clientJson = JSON.stringify(c).replace(/'/g, "\\'");
                return `<div class="client-result-item d-flex align-items-center gap-3 px-3 py-2"
                             onclick='selectClient(${clientJson})'>
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                         style="width:36px;height:36px;font-size:.9rem;background:${color};">${initial}</div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-semibold small text-truncate">${c.display_name}${badge}</div>
                        <div class="text-muted" style="font-size:.76rem;">${meta}</div>
                    </div>
                    <i class="bi bi-chevron-right text-muted opacity-50 flex-shrink-0"></i>
                </div>`;
            }).join('');
        }
        dd.style.display = '';
    }

    // Evento de búsqueda con debounce
    document.getElementById('clientSearchInput').addEventListener('input', function () {
        clearTimeout(clientSearchTimer);
        const q = this.value.trim();

        if (q.length < 1) {
            document.getElementById('clientDropdown').style.display = 'none';
            return;
        }

        document.getElementById('searchSpinIcon').className = 'bi bi-arrow-clockwise client-spin';
        clientSearchTimer = setTimeout(() => {
            fetch(CLIENT_SEARCH_URL + '?q=' + encodeURIComponent(q))
                .then(r => r.json())
                .then(data => {
                    document.getElementById('searchSpinIcon').className = 'bi bi-search text-muted';
                    renderClientResults(data);
                })
                .catch(() => {
                    document.getElementById('searchSpinIcon').className = 'bi bi-search text-muted';
                });
        }, 280);
    });

    // Cerrar dropdown al hacer clic fuera
    document.addEventListener('click', function (e) {
        const wrapper = document.getElementById('clientSearchWrapper');
        if (wrapper && !wrapper.contains(e.target)) {
            document.getElementById('clientDropdown').style.display = 'none';
        }
    });

    // Sincronizar campos de venta rápida antes del envío
    document.getElementById('saleForm').addEventListener('submit', function () {
        const mode = document.getElementById('clientModeInput').value;
        if (mode === 'quick') {
            document.getElementById('clientIdInput').value    = '';
            document.getElementById('clientNameInput').value  = document.getElementById('quickName').value;
            document.getElementById('clientPhoneInput').value = document.getElementById('quickPhone').value;
            document.getElementById('clientDocInput').value   = document.getElementById('quickDoc').value;
        }
    }, true); // capture: true → fires before the validation listener

    // ─── Branch → Warehouse auto-select ────────────────────────────────────
    (function () {
        const branchSel   = document.getElementById('branchSelect');
        const warehouseSel = document.getElementById('warehouseSelect');

        function applyWarehouse(force) {
            const opt = branchSel.options[branchSel.selectedIndex];
            const wId = opt?.dataset?.warehouse;
            if (wId && (force || !warehouseSel.value)) {
                warehouseSel.value = wId;
            }
        }

        branchSel.addEventListener('change', () => applyWarehouse(true));

        // On page load: auto-select only if warehouse not already chosen (e.g. old())
        applyWarehouse(false);
    })();

    // ─── Form validation ───
    document.getElementById('saleForm').addEventListener('submit', function(e) {
        const type = document.getElementById('saleTypeInput').value;
        if (type === 'credit') {
            const rows = document.querySelectorAll('.installment-row');
            if (rows.length === 0) {
                e.preventDefault();
                alert('Debe agregar al menos una cuota para ventas a crédito.');
                return;
            }
            let totalPct = 0;
            rows.forEach(r => { totalPct += parseFloat(r.querySelector('.inst-pct').value) || 0; });
            if (Math.abs(totalPct - 100) > 0.01) {
                e.preventDefault();
                alert('La suma de porcentajes de cuotas debe ser exactamente 100%.');
                return;
            }
        }
    });
</script>
@endpush
