@extends('layouts.app')

@section('title', 'Nueva devolución de venta')

@include('layouts._compact_style')

@section('page')
<div class="view-compact container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3 page-head">
        <h1 class="mb-0"><i class="bi bi-arrow-return-left text-warning me-2"></i>Nueva devolución de venta</h1>
        <a href="{{ route('sales-returns.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form action="{{ $action }}" method="POST" id="returnForm">
        @csrf
        @if($method !== 'POST') @method($method) @endif

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-3">
                        <label class="form-label">Número</label>
                        <input type="text" class="form-control bg-light" value="{{ $nextNumber }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Venta relacionada</label>
                        <select name="sale_id" class="form-select">
                            <option value="">— Sin venta —</option>
                            @foreach($sales as $s)
                                <option value="{{ $s->id }}" {{ old('sale_id') == $s->id ? 'selected' : '' }}>{{ $s->sale_number }} · ${{ number_format($s->total,2) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Cliente</label>
                        <select name="client_id" class="form-select">
                            <option value="">Cliente ocasional</option>
                            @foreach($clients as $c)<option value="{{ $c->id }}" {{ old('client_id') == $c->id ? 'selected' : '' }}>{{ $c->display_name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Almacén (reingreso) <span class="text-danger">*</span></label>
                        <select name="warehouse_id" class="form-select" required>
                            <option value="">Seleccionar...</option>
                            @foreach($warehouses as $w)<option value="{{ $w->id }}" {{ old('warehouse_id') == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fecha <span class="text-danger">*</span></label>
                        <input type="date" name="return_date" class="form-control" value="{{ old('return_date', now()->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Motivo <span class="text-danger">*</span></label>
                        <select name="reason" class="form-select" required>
                            @foreach(\App\Models\SalesReturn::REASON_LABELS as $val => $lbl)
                                <option value="{{ $val }}" {{ old('reason') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Notas</label>
                        <input type="text" name="notes" class="form-control" value="{{ old('notes') }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold mb-0"><i class="bi bi-list-ul me-1"></i> Productos devueltos</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addRow"><i class="bi bi-plus-lg"></i> Agregar</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle" id="itemsTable">
                        <thead class="table-light"><tr><th>Producto</th><th style="width:120px">Cantidad</th><th style="width:130px">Precio</th><th class="text-end" style="width:130px">Total</th><th></th></tr></thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-end fw-bold">Total: <span id="totalLbl" class="text-success ms-2">$0.00</span></div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('sales-returns.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Guardar</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const products = @json($products->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'price' => (float)$p->price]));
    const preload  = @json(old('items', []));
    const tbody = document.querySelector('#itemsTable tbody');
    let idx = 0;
    const fmt = n => '$' + (n||0).toLocaleString('es', { minimumFractionDigits:2, maximumFractionDigits:2 });

    function options(sel) {
        return '<option value="">Seleccionar...</option>' + products.map(p =>
            `<option value="${p.id}" data-price="${p.price}" ${String(sel)===String(p.id)?'selected':''}>${p.name}</option>`).join('');
    }
    function addRow(data = {}) {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><select name="items[${idx}][product_id]" class="form-select form-select-sm prod" required>${options(data.product_id)}</select></td>
            <td><input type="number" step="0.01" min="0.01" name="items[${idx}][quantity]" class="form-control form-control-sm qty" value="${data.quantity||1}" required></td>
            <td><input type="number" step="0.01" min="0" name="items[${idx}][unit_price]" class="form-control form-control-sm price" value="${data.unit_price||0}" required></td>
            <td class="text-end line-total">$0.00</td>
            <td><button type="button" class="btn btn-sm btn-link text-danger rm p-0"><i class="bi bi-x-lg"></i></button></td>`;
        tbody.appendChild(tr); idx++; recalc();
    }
    function recalc() {
        let total = 0;
        tbody.querySelectorAll('tr').forEach(tr => {
            const qty = parseFloat(tr.querySelector('.qty').value) || 0;
            const price = parseFloat(tr.querySelector('.price').value) || 0;
            const lt = qty*price;
            tr.querySelector('.line-total').textContent = fmt(lt);
            total += lt;
        });
        document.getElementById('totalLbl').textContent = fmt(total);
    }
    tbody.addEventListener('input', e => {
        if (e.target.classList.contains('prod')) {
            const opt = e.target.selectedOptions[0];
            const priceInp = e.target.closest('tr').querySelector('.price');
            if (opt && opt.dataset.price && (!priceInp.value || priceInp.value == '0')) priceInp.value = opt.dataset.price;
        }
        recalc();
    });
    tbody.addEventListener('click', e => { if (e.target.closest('.rm')) { e.target.closest('tr').remove(); recalc(); } });
    document.getElementById('addRow').addEventListener('click', () => addRow());
    if (preload && preload.length) { preload.forEach(d => addRow(d)); } else { addRow(); }
});
</script>
@endpush
@endsection
