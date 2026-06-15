@extends('layouts.app')
@section('title', 'Nueva Devolución')
@section('page')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><i class="bi bi-arrow-return-left text-primary me-2"></i>Nueva devolución</h1>
            @php
                $retCompanyId = auth()->user()?->getCurrentCompany()?->id ?? 0;
                $returnNumber = \App\Models\PurchaseReturn::generateReturnNumber($retCompanyId);
            @endphp
            <p class="text-muted mb-0">Nº: <strong>{{ $returnNumber }}</strong></p>
        </div>
        <a href="{{ route('purchases.returns.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
    </div>

    @if($errors->any())
    <div class="alert alert-danger mb-4"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form action="{{ route('purchases.returns.store') }}" method="POST">
        @csrf
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-2 px-4">
                        <h6 class="fw-bold mb-0"><i class="bi bi-info-circle me-1 text-primary"></i> Información</h6>
                    </div>
                    <div class="card-body p-4 pt-3 row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Proveedor <span class="text-danger">*</span></label>
                            <select name="supplier_id" class="form-select form-select-sm @error('supplier_id') is-invalid @enderror" required>
                                <option value="">Seleccionar...</option>
                                @foreach($suppliers as $s)
                                    <option value="{{ $s->id }}" {{ old('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->display_name }}</option>
                                @endforeach
                            </select>
                            @error('supplier_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Recepción de origen</label>
                            <select name="purchase_reception_id" class="form-select form-select-sm">
                                <option value="">Sin recepción vinculada</option>
                                @foreach($receptions as $rec)
                                    <option value="{{ $rec->id }}" {{ old('purchase_reception_id') == $rec->id ? 'selected' : '' }}>{{ $rec->reception_number }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Fecha devolución <span class="text-danger">*</span></label>
                            <input type="date" name="return_date" class="form-control form-control-sm" value="{{ old('return_date', now()->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Motivo <span class="text-danger">*</span></label>
                            <select name="reason" class="form-select form-select-sm" required>
                                @foreach(\App\Models\PurchaseReturn::REASON_LABELS as $val => $label)
                                    <option value="{{ $val }}" {{ old('reason', 'defectuoso') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Notas</label>
                            <textarea name="notes" class="form-control form-control-sm" rows="2">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-2 px-4 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0"><i class="bi bi-list-ul me-1 text-primary"></i> Ítems a devolver</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addItemBtn"><i class="bi bi-plus-lg me-1"></i> Agregar</button>
                    </div>
                    <div class="card-body p-3">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-2">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:40%">Producto</th>
                                        <th style="width:20%">Cantidad</th>
                                        <th style="width:25%">Precio unit.</th>
                                        <th style="width:10%">Total</th>
                                        <th style="width:5%"></th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody">
                                    @foreach(old('items', [[]]) as $i => $item)
                                    <tr class="item-row">
                                        <td>
                                            <select name="items[{{ $i }}][product_id]" class="form-select form-select-sm" required>
                                                <option value="">Seleccionar...</option>
                                                @foreach($products as $p)
                                                    <option value="{{ $p->id }}" {{ ($item['product_id'] ?? '') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="number" name="items[{{ $i }}][quantity]" class="form-control form-control-sm item-qty" value="{{ $item['quantity'] ?? 1 }}" min="0.01" step="0.01" required></td>
                                        <td><div class="input-group input-group-sm"><span class="input-group-text">$</span><input type="number" name="items[{{ $i }}][unit_price]" class="form-control item-price" value="{{ $item['unit_price'] ?? 0 }}" min="0" step="0.01"></div></td>
                                        <td class="item-total fw-semibold">$0.00</td>
                                        <td><button type="button" class="btn btn-sm btn-outline-danger remove-row-btn"><i class="bi bi-x"></i></button></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="text-end fw-bold mt-2">Total: <span id="grandTotal" class="text-primary">$0.00</span></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="d-flex flex-column gap-2 mt-4 mt-lg-0">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Guardar devolución</button>
                    <a href="{{ route('purchases.returns.index') }}" class="btn btn-light border text-center">Cancelar</a>
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

    function productOptions(sel = '') {
        return '<option value="">Seleccionar...</option>' + products.map(p => `<option value="${p.id}" ${p.id==sel?'selected':''}>${p.name}</option>`).join('');
    }

    function calcAll() {
        let total = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const qty   = parseFloat(row.querySelector('.item-qty').value) || 0;
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            row.querySelector('.item-total').textContent = '$' + (qty * price).toFixed(2);
            total += qty * price;
        });
        document.getElementById('grandTotal').textContent = '$' + total.toFixed(2);
    }

    function bindRow(row) {
        row.querySelectorAll('input, select').forEach(el => el.addEventListener('input', calcAll));
        row.querySelector('.remove-row-btn').addEventListener('click', function() {
            if (document.querySelectorAll('.item-row').length > 1) { row.remove(); calcAll(); }
        });
    }

    document.querySelectorAll('.item-row').forEach(bindRow);
    calcAll();

    document.getElementById('addItemBtn').addEventListener('click', function() {
        const tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.innerHTML = `
            <td><select name="items[${idx}][product_id]" class="form-select form-select-sm" required>${productOptions()}</select></td>
            <td><input type="number" name="items[${idx}][quantity]" class="form-control form-control-sm item-qty" value="1" min="0.01" step="0.01" required></td>
            <td><div class="input-group input-group-sm"><span class="input-group-text">$</span><input type="number" name="items[${idx}][unit_price]" class="form-control item-price" value="0" min="0" step="0.01"></div></td>
            <td class="item-total fw-semibold">$0.00</td>
            <td><button type="button" class="btn btn-sm btn-outline-danger remove-row-btn"><i class="bi bi-x"></i></button></td>`;
        document.getElementById('itemsBody').appendChild(tr);
        bindRow(tr); idx++;
    });
})();
</script>
@endpush
@endsection
