<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><i class="bi bi-file-earmark-plus text-primary me-2"></i>Nueva solicitud de compra</h1>
            <p class="text-muted mb-0">Nº: <strong>{{ $requestNumber }}</strong></p>
        </div>
        <a href="{{ route('purchases.requests.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
    </div>

    @if($errors->any())
    <div class="alert alert-danger mb-4"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form action="{{ route('purchases.requests.store') }}" method="POST" id="requestForm">
        @csrf
        <div class="row g-4">
            <div class="col-lg-8">
                {{-- Encabezado --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-2 px-4">
                        <h6 class="fw-bold mb-0"><i class="bi bi-info-circle me-1 text-primary"></i> Información</h6>
                    </div>
                    <div class="card-body p-4 pt-3 row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Solicitante <span class="text-danger">*</span></label>
                            <select name="requested_by" class="form-select form-select-sm @error('requested_by') is-invalid @enderror" required>
                                <option value="">Seleccionar...</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" {{ old('requested_by', auth()->id()) == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                @endforeach
                            </select>
                            @error('requested_by')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Departamento</label>
                            <input type="text" name="department" class="form-control form-control-sm" value="{{ old('department') }}" placeholder="Ej: Producción">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Prioridad <span class="text-danger">*</span></label>
                            <select name="priority" class="form-select form-select-sm" required>
                                @foreach(\App\Models\PurchaseRequest::PRIORITY_LABELS as $val => $label)
                                    <option value="{{ $val }}" {{ old('priority', 'media') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Fecha esperada</label>
                            <input type="date" name="expected_date" class="form-control form-control-sm" value="{{ old('expected_date') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Notas</label>
                            <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="Observaciones...">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Ítems --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-2 px-4 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0"><i class="bi bi-list-ul me-1 text-primary"></i> Productos solicitados</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addItemBtn"><i class="bi bi-plus-lg me-1"></i> Agregar</button>
                    </div>
                    <div class="card-body p-3">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-2" id="itemsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:40%">Producto</th>
                                        <th style="width:18%">Cantidad</th>
                                        <th style="width:22%">Costo estimado</th>
                                        <th style="width:15%">Subtotal</th>
                                        <th style="width:5%"></th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody">
                                    @foreach(old('items', [[]]) as $i => $item)
                                    <tr class="item-row">
                                        <td>
                                            <select name="items[{{ $i }}][product_id]" class="form-select form-select-sm product-select" required>
                                                <option value="">Seleccionar producto...</option>
                                                @foreach($products as $p)
                                                    <option value="{{ $p->id }}" {{ ($item['product_id'] ?? '') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="number" name="items[{{ $i }}][quantity]" class="form-control form-control-sm item-qty" value="{{ $item['quantity'] ?? 1 }}" min="0.01" step="0.01" required></td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">$</span>
                                                <input type="number" name="items[{{ $i }}][estimated_unit_cost]" class="form-control item-price" value="{{ $item['estimated_unit_cost'] ?? 0 }}" min="0" step="0.01">
                                            </div>
                                        </td>
                                        <td class="item-total fw-semibold">$0.00</td>
                                        <td><button type="button" class="btn btn-sm btn-outline-danger remove-row-btn"><i class="bi bi-x"></i></button></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="text-end fw-bold mt-2">Total estimado: <span id="grandTotal" class="text-primary">$0.00</span></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="d-flex flex-column gap-2 mt-4 mt-lg-0">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Crear solicitud</button>
                    <a href="{{ route('purchases.requests.index') }}" class="btn btn-light border text-center">Cancelar</a>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
(function() {
    let idx = document.querySelectorAll('.item-row').length;
    const products = @json($products->map(fn($p) => ['id' => $p->id, 'name' => $p->name]));

    function productOptions(selected = '') {
        return '<option value="">Seleccionar producto...</option>' +
            products.map(p => `<option value="${p.id}" ${p.id == selected ? 'selected' : ''}>${p.name}</option>`).join('');
    }

    function calcRow(row) {
        const qty   = parseFloat(row.querySelector('.item-qty').value) || 0;
        const price = parseFloat(row.querySelector('.item-price').value) || 0;
        row.querySelector('.item-total').textContent = '$' + (qty * price).toFixed(2);
    }

    function calcAll() {
        let total = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            calcRow(row);
            const qty   = parseFloat(row.querySelector('.item-qty').value) || 0;
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            total += qty * price;
        });
        document.getElementById('grandTotal').textContent = '$' + total.toFixed(2);
    }

    function bindRow(row) {
        row.querySelectorAll('input, select').forEach(el => el.addEventListener('input', calcAll));
        row.querySelector('.remove-row-btn').addEventListener('click', function() {
            if (document.querySelectorAll('.item-row').length > 1) {
                row.remove(); calcAll();
            }
        });
    }

    document.querySelectorAll('.item-row').forEach(bindRow);
    calcAll();

    document.getElementById('addItemBtn').addEventListener('click', function() {
        const tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.innerHTML = `
            <td><select name="items[${idx}][product_id]" class="form-select form-select-sm product-select" required>${productOptions()}</select></td>
            <td><input type="number" name="items[${idx}][quantity]" class="form-control form-control-sm item-qty" value="1" min="0.01" step="0.01" required></td>
            <td><div class="input-group input-group-sm"><span class="input-group-text">$</span><input type="number" name="items[${idx}][estimated_unit_cost]" class="form-control item-price" value="0" min="0" step="0.01"></div></td>
            <td class="item-total fw-semibold">$0.00</td>
            <td><button type="button" class="btn btn-sm btn-outline-danger remove-row-btn"><i class="bi bi-x"></i></button></td>`;
        document.getElementById('itemsBody').appendChild(tr);
        bindRow(tr); idx++;
    });
})();
</script>
@endpush
