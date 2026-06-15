<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><i class="bi bi-file-earmark-text text-primary me-2"></i>{{ isset($quotation) ? 'Editar cotización' : 'Nueva cotización' }}</h1>
            <p class="text-muted mb-0">Nº: <strong>{{ $quotationNumber }}</strong></p>
        </div>
        <a href="{{ route('purchases.quotations.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
    </div>

    @if($errors->any())
    <div class="alert alert-danger mb-4"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form action="{{ isset($quotation) ? route('purchases.quotations.update', $quotation) : route('purchases.quotations.store') }}" method="POST" id="quotForm">
        @csrf
        @if(isset($quotation)) @method('PUT') @endif

        <div class="row g-4">
            <div class="col-lg-8">
                {{-- Encabezado --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-2 px-4">
                        <h6 class="fw-bold mb-0"><i class="bi bi-info-circle me-1 text-primary"></i> Información</h6>
                    </div>
                    <div class="card-body p-4 pt-3 row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Proveedor</label>
                            <select name="supplier_id" class="form-select form-select-sm">
                                <option value="">Sin proveedor...</option>
                                @foreach($suppliers as $s)
                                    <option value="{{ $s->id }}" {{ old('supplier_id', $quotation->supplier_id ?? '') == $s->id ? 'selected' : '' }}>{{ $s->display_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Solicitud de compra</label>
                            <select name="purchase_request_id" class="form-select form-select-sm">
                                <option value="">Sin solicitud vinculada</option>
                                @foreach($purchaseRequests as $pr)
                                    <option value="{{ $pr->id }}" {{ old('purchase_request_id', $quotation->purchase_request_id ?? request('request_id')) == $pr->id ? 'selected' : '' }}>{{ $pr->request_number }} — {{ $pr->requestedBy?->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Fecha cotización <span class="text-danger">*</span></label>
                            <input type="date" name="quotation_date" class="form-control form-control-sm" value="{{ old('quotation_date', ($quotation->quotation_date ?? now())->format('Y-m-d') ) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Válida hasta</label>
                            <input type="date" name="valid_until" class="form-control form-control-sm" value="{{ old('valid_until', $quotation->valid_until?->format('Y-m-d') ?? '') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Estado</label>
                            <select name="status" class="form-select form-select-sm">
                                @foreach(\App\Models\PurchaseQuotation::STATUS_LABELS as $val => $label)
                                    <option value="{{ $val }}" {{ old('status', $quotation->status ?? 'borrador') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Descuento global ($)</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">$</span>
                                <input type="number" name="discount" id="discountInput" class="form-control" value="{{ old('discount', $quotation->discount ?? 0) }}" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Notas</label>
                            <textarea name="notes" class="form-control form-control-sm" rows="2">{{ old('notes', $quotation->notes ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Ítems --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-2 px-4 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0"><i class="bi bi-list-ul me-1 text-primary"></i> Ítems</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addItemBtn"><i class="bi bi-plus-lg me-1"></i> Agregar</button>
                    </div>
                    <div class="card-body p-3">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-2">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:35%">Producto</th>
                                        <th style="width:15%">Cantidad</th>
                                        <th style="width:18%">Precio unit.</th>
                                        <th style="width:15%">Descuento</th>
                                        <th style="width:12%">Total</th>
                                        <th style="width:5%"></th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody">
                                    @php $existingItems = old('items', isset($quotation) ? $quotation->items->map(fn($i) => ['product_id'=>$i->product_id,'quantity'=>$i->quantity,'unit_price'=>$i->unit_price,'discount'=>$i->discount])->toArray() : [[]]); @endphp
                                    @foreach($existingItems as $idx => $item)
                                    <tr class="item-row">
                                        <td>
                                            <select name="items[{{ $idx }}][product_id]" class="form-select form-select-sm" required>
                                                <option value="">Seleccionar...</option>
                                                @foreach($products as $p)
                                                    <option value="{{ $p->id }}" {{ ($item['product_id'] ?? '') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="number" name="items[{{ $idx }}][quantity]" class="form-control form-control-sm item-qty" value="{{ $item['quantity'] ?? 1 }}" min="0.01" step="0.01" required></td>
                                        <td><div class="input-group input-group-sm"><span class="input-group-text">$</span><input type="number" name="items[{{ $idx }}][unit_price]" class="form-control item-price" value="{{ $item['unit_price'] ?? 0 }}" min="0" step="0.01"></div></td>
                                        <td><div class="input-group input-group-sm"><span class="input-group-text">$</span><input type="number" name="items[{{ $idx }}][discount]" class="form-control item-disc" value="{{ $item['discount'] ?? 0 }}" min="0" step="0.01"></div></td>
                                        <td class="item-total fw-semibold">$0.00</td>
                                        <td><button type="button" class="btn btn-sm btn-outline-danger remove-row-btn"><i class="bi bi-x"></i></button></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="text-end mt-2 small">
                            <span class="text-muted me-3">Subtotal: <span id="dispSubtotal">$0.00</span></span>
                            <span class="text-muted me-3">IVA (12%): <span id="dispTax">$0.00</span></span>
                            <span class="fw-bold fs-6">Total: <span id="dispTotal" class="text-primary">$0.00</span></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="d-flex flex-column gap-2 mt-4 mt-lg-0">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Guardar cotización</button>
                    <a href="{{ route('purchases.quotations.index') }}" class="btn btn-light border text-center">Cancelar</a>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
(function() {
    const TAX_RATE = 0.12;
    let idx = document.querySelectorAll('.item-row').length;
    const products = @json($products->map(fn($p) => ['id' => $p->id, 'name' => $p->name]));

    function productOptions(sel = '') {
        return '<option value="">Seleccionar...</option>' + products.map(p => `<option value="${p.id}" ${p.id==sel?'selected':''}>${p.name}</option>`).join('');
    }

    function calcAll() {
        let subtotal = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const qty   = parseFloat(row.querySelector('.item-qty').value) || 0;
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            const disc  = parseFloat(row.querySelector('.item-disc').value) || 0;
            const total = Math.max(0, qty * price - disc);
            row.querySelector('.item-total').textContent = '$' + total.toFixed(2);
            subtotal += total;
        });
        const globalDisc = parseFloat(document.getElementById('discountInput').value) || 0;
        const discounted = Math.max(0, subtotal - globalDisc);
        const tax = discounted * TAX_RATE;
        const total = discounted + tax;
        document.getElementById('dispSubtotal').textContent = '$' + subtotal.toFixed(2);
        document.getElementById('dispTax').textContent      = '$' + tax.toFixed(2);
        document.getElementById('dispTotal').textContent    = '$' + total.toFixed(2);
    }

    function bindRow(row) {
        row.querySelectorAll('input, select').forEach(el => el.addEventListener('input', calcAll));
        row.querySelector('.remove-row-btn').addEventListener('click', function() {
            if (document.querySelectorAll('.item-row').length > 1) { row.remove(); calcAll(); }
        });
    }

    document.querySelectorAll('.item-row').forEach(bindRow);
    document.getElementById('discountInput').addEventListener('input', calcAll);
    calcAll();

    document.getElementById('addItemBtn').addEventListener('click', function() {
        const tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.innerHTML = `
            <td><select name="items[${idx}][product_id]" class="form-select form-select-sm" required>${productOptions()}</select></td>
            <td><input type="number" name="items[${idx}][quantity]" class="form-control form-control-sm item-qty" value="1" min="0.01" step="0.01" required></td>
            <td><div class="input-group input-group-sm"><span class="input-group-text">$</span><input type="number" name="items[${idx}][unit_price]" class="form-control item-price" value="0" min="0" step="0.01"></div></td>
            <td><div class="input-group input-group-sm"><span class="input-group-text">$</span><input type="number" name="items[${idx}][discount]" class="form-control item-disc" value="0" min="0" step="0.01"></div></td>
            <td class="item-total fw-semibold">$0.00</td>
            <td><button type="button" class="btn btn-sm btn-outline-danger remove-row-btn"><i class="bi bi-x"></i></button></td>`;
        document.getElementById('itemsBody').appendChild(tr);
        bindRow(tr); idx++;
    });
})();
</script>
@endpush
