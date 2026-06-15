@extends('layouts.app')

@section('title', 'POS')

@include('layouts._compact_style')

@section('page')
<div class="view-compact container-fluid" id="posApp">

    <div class="d-flex justify-content-between align-items-center mb-2 page-head">
        <div>
            <h1 class="mb-0"><i class="bi bi-upc-scan text-success me-2"></i>Punto de venta</h1>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if($session)
                <span class="badge bg-success"><i class="bi bi-cash-stack me-1"></i> Caja abierta: {{ $session->cashRegister?->name }}</span>
            @else
                <span class="badge bg-secondary"><i class="bi bi-cash-stack me-1"></i> Sin caja abierta</span>
            @endif
            <a href="{{ route('sales.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-list"></i> Ventas</a>
        </div>
    </div>

    <form action="{{ route('pos.store') }}" method="POST" id="posForm">
        @csrf
        <div class="row g-3">
            {{-- Productos --}}
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <input type="text" id="prodSearch" class="form-control mb-2" placeholder="Buscar producto por nombre o SKU...">
                        <div class="row g-2" id="prodGrid" style="max-height:62vh;overflow-y:auto;">
                            @foreach($products as $p)
                                <div class="col-6 col-md-4 prod-cell" data-name="{{ strtolower($p->name) }}" data-sku="{{ strtolower($p->sku) }}">
                                    <button type="button" class="prod-card w-100 text-start"
                                            data-id="{{ $p->id }}" data-pname="{{ $p->name }}" data-price="{{ $p->price }}" data-stock="{{ $p->current_stock }}">
                                        <div class="fw-semibold text-truncate">{{ $p->name }}</div>
                                        <div class="d-flex justify-content-between align-items-center mt-1">
                                            <span class="text-success fw-bold">${{ number_format($p->price, 2) }}</span>
                                            <span class="badge bg-light text-secondary border">{{ number_format($p->current_stock, 0) }} {{ $p->unit }}</span>
                                        </div>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Carrito --}}
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        {{-- Cliente / meta --}}
                        <div class="row g-2 mb-2">
                            <div class="col-12">
                                <label class="form-label">Cliente</label>
                                <select name="client_id" class="form-select form-select-sm">
                                    <option value="">Cliente ocasional</option>
                                    @foreach($clients as $c)
                                        <option value="{{ $c->id }}">{{ $c->display_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Sucursal</label>
                                <select name="branch_id" class="form-select form-select-sm">
                                    <option value="">—</option>
                                    @foreach($branches as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Almacén</label>
                                <select name="warehouse_id" class="form-select form-select-sm">
                                    <option value="">—</option>
                                    @foreach($warehouses as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach
                                </select>
                            </div>
                        </div>

                        <div class="table-responsive mb-2" style="max-height:32vh;overflow-y:auto;">
                            <table class="table table-sm align-middle mb-0" id="cartTable">
                                <thead class="table-light"><tr><th>Producto</th><th style="width:80px">Cant.</th><th class="text-end">Importe</th><th></th></tr></thead>
                                <tbody id="cartBody">
                                    <tr id="cartEmpty"><td colspan="4" class="text-center text-muted py-4">Carrito vacío</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="row g-2 align-items-center mb-2">
                            <div class="col-6">
                                <label class="form-label mb-0">Descuento</label>
                                <input type="number" step="0.01" min="0" name="discount" id="discount" class="form-control form-control-sm" value="0">
                            </div>
                            <div class="col-6">
                                <label class="form-label mb-0">Impuesto</label>
                                <input type="number" step="0.01" min="0" name="tax" id="tax" class="form-control form-control-sm" value="0">
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center border-top pt-2 mb-2">
                            <span class="fw-semibold">Total</span>
                            <span class="fs-4 fw-bold text-success" id="totalLabel">$0.00</span>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Método de pago</label>
                            <div class="btn-group w-100" role="group">
                                @foreach(['cash'=>'Efectivo','card'=>'Tarjeta','transfer'=>'Transf.','other'=>'Otro'] as $pm => $lbl)
                                    <input type="radio" class="btn-check" name="payment_method" id="pm_{{ $pm }}" value="{{ $pm }}" {{ $pm==='cash'?'checked':'' }}>
                                    <label class="btn btn-outline-success btn-sm" for="pm_{{ $pm }}">{{ $lbl }}</label>
                                @endforeach
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success w-100 btn-lg" id="cobrarBtn" disabled>
                            <i class="bi bi-check2-circle me-1"></i> Cobrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('styles')
<style>
    #posApp .prod-card { border:1px solid #e3e6ea; border-radius:9px; background:#fff; padding:9px 10px; transition:all .12s; }
    #posApp .prod-card:hover { border-color:#198754; box-shadow:0 3px 10px rgba(25,135,84,.15); transform:translateY(-1px); }
    #posApp .prod-card:active { transform:scale(.98); }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const cart = new Map();
    const cartBody  = document.getElementById('cartBody');
    const cartEmpty = document.getElementById('cartEmpty');
    const fmt = n => '$' + n.toLocaleString('es', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    function render() {
        cartBody.querySelectorAll('tr[data-row]').forEach(r => r.remove());
        if (cart.size === 0) { cartEmpty.style.display = ''; }
        else { cartEmpty.style.display = 'none'; }

        let subtotal = 0;
        let idx = 0;
        cart.forEach((it, id) => {
            const lineTotal = it.qty * it.price;
            subtotal += lineTotal;
            const tr = document.createElement('tr');
            tr.setAttribute('data-row', id);
            tr.innerHTML = `
                <td>
                    <div class="fw-semibold text-truncate" style="max-width:150px">${it.name}</div>
                    <input type="hidden" name="items[${idx}][product_id]" value="${id}">
                    <input type="hidden" name="items[${idx}][unit_price]" value="${it.price}">
                    <small class="text-muted">${fmt(it.price)}</small>
                </td>
                <td><input type="number" min="0.01" step="0.01" max="${it.stock}" class="form-control form-control-sm qty-inp"
                        name="items[${idx}][quantity]" value="${it.qty}" data-id="${id}"></td>
                <td class="text-end">${fmt(lineTotal)}</td>
                <td><button type="button" class="btn btn-sm btn-link text-danger p-0 rm-btn" data-id="${id}"><i class="bi bi-x-lg"></i></button></td>`;
            cartBody.appendChild(tr);
            idx++;
        });

        const discount = parseFloat(document.getElementById('discount').value) || 0;
        const tax      = parseFloat(document.getElementById('tax').value) || 0;
        const total    = Math.max(0, subtotal + tax - discount);
        document.getElementById('totalLabel').textContent = fmt(total);
        document.getElementById('cobrarBtn').disabled = cart.size === 0;
    }

    document.querySelectorAll('.prod-card').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            const stock = parseFloat(btn.dataset.stock) || 0;
            if (stock <= 0) { alert('Sin stock disponible'); return; }
            if (cart.has(id)) {
                const it = cart.get(id);
                if (it.qty + 1 <= stock) it.qty++;
            } else {
                cart.set(id, { name: btn.dataset.pname, price: parseFloat(btn.dataset.price) || 0, qty: 1, stock });
            }
            render();
        });
    });

    cartBody.addEventListener('input', e => {
        if (e.target.classList.contains('qty-inp')) {
            const id = e.target.dataset.id;
            let v = parseFloat(e.target.value) || 0;
            const it = cart.get(id);
            if (it) { it.qty = v; }
            // recompute totals only (avoid full re-render to keep focus)
            let subtotal = 0;
            cart.forEach(x => subtotal += x.qty * x.price);
            const discount = parseFloat(document.getElementById('discount').value) || 0;
            const tax      = parseFloat(document.getElementById('tax').value) || 0;
            document.getElementById('totalLabel').textContent = fmt(Math.max(0, subtotal + tax - discount));
            const row = e.target.closest('tr');
            if (row) row.querySelector('td.text-end').textContent = fmt((it?.qty || 0) * (it?.price || 0));
        }
    });

    cartBody.addEventListener('click', e => {
        const btn = e.target.closest('.rm-btn');
        if (btn) { cart.delete(btn.dataset.id); render(); }
    });

    document.getElementById('discount').addEventListener('input', render);
    document.getElementById('tax').addEventListener('input', render);
    document.getElementById('prodSearch').addEventListener('input', e => {
        const q = e.target.value.toLowerCase();
        document.querySelectorAll('.prod-cell').forEach(c => {
            c.style.display = (c.dataset.name.includes(q) || c.dataset.sku.includes(q)) ? '' : 'none';
        });
    });

    document.getElementById('posForm').addEventListener('submit', e => {
        if (cart.size === 0) { e.preventDefault(); alert('El carrito está vacío'); }
    });
});
</script>
@endpush
@endsection
