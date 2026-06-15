@extends('layouts.app')
@section('title', 'Nueva Recepción')
@section('page')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><i class="bi bi-box-arrow-in-down-right text-primary me-2"></i>Nueva recepción</h1>
            @php
                $recCompanyId = auth()->user()?->getCurrentCompany()?->id ?? 0;
                $recNumber = \App\Models\PurchaseReception::generateReceptionNumber($recCompanyId);
            @endphp
            <p class="text-muted mb-0">Nº: <strong>{{ $recNumber }}</strong></p>
        </div>
        <a href="{{ route('purchases.receptions.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
    </div>

    @if($errors->any())
    <div class="alert alert-danger mb-4"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form action="{{ route('purchases.receptions.store') }}" method="POST">
        @csrf
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-2 px-4">
                        <h6 class="fw-bold mb-0"><i class="bi bi-info-circle me-1 text-primary"></i> Información</h6>
                    </div>
                    <div class="card-body p-4 pt-3 row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Orden de compra <span class="text-danger">*</span></label>
                            <select name="purchase_order_id" class="form-select form-select-sm @error('purchase_order_id') is-invalid @enderror" id="orderSelect" required>
                                <option value="">Seleccionar orden...</option>
                                @foreach($orders as $ord)
                                    <option value="{{ $ord->id }}"
                                        {{ old('purchase_order_id', request('order_id')) == $ord->id ? 'selected' : '' }}
                                        data-warehouse="{{ $ord->warehouse_id }}"
                                        data-items="{{ json_encode($ord->items->map(fn($i) => ['item_id'=>$i->id,'product_id'=>$i->product_id,'product_name'=>$i->product?->name,'quantity_ordered'=>$i->quantity,'quantity_received'=>$i->quantity_received,'unit_price'=>$i->unit_price])) }}">
                                        {{ $ord->order_number }} — {{ $ord->supplier?->display_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('purchase_order_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Almacén destino <span class="text-danger">*</span></label>
                            <select name="warehouse_id" id="warehouseSelect" class="form-select form-select-sm @error('warehouse_id') is-invalid @enderror" required>
                                <option value="">Seleccionar almacén...</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}" {{ old('warehouse_id', $selectedOrder->warehouse_id ?? '') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                                @endforeach
                            </select>
                            @error('warehouse_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Fecha recepción <span class="text-danger">*</span></label>
                            <input type="date" name="reception_date" class="form-control form-control-sm" value="{{ old('reception_date', now()->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Nº Factura proveedor</label>
                            <input type="text" name="invoice_number" class="form-control form-control-sm" value="{{ old('invoice_number') }}" placeholder="001-001-000001">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Notas</label>
                            <textarea name="notes" class="form-control form-control-sm" rows="2">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm" id="itemsCard">
                    <div class="card-header bg-white border-bottom py-2 px-4">
                        <h6 class="fw-bold mb-0"><i class="bi bi-list-ul me-1 text-primary"></i> Cantidades recibidas</h6>
                    </div>
                    <div class="card-body p-3">
                        <p class="text-muted small" id="selectOrderMsg">Selecciona una orden de compra para ver los ítems pendientes.</p>
                        <div class="table-responsive d-none" id="itemsTableWrap">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Producto</th>
                                        <th class="text-end">Ordenado</th>
                                        <th class="text-end">Pendiente</th>
                                        <th style="width:18%">Recibido ahora</th>
                                        <th style="width:18%">Precio unit.</th>
                                    </tr>
                                </thead>
                                <tbody id="recItemsBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="d-flex flex-column gap-2 mt-4 mt-lg-0">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Guardar recepción</button>
                    <a href="{{ route('purchases.receptions.index') }}" class="btn btn-light border text-center">Cancelar</a>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
(function() {
    const orderSel = document.getElementById('orderSelect');
    const body     = document.getElementById('recItemsBody');
    const wrap     = document.getElementById('itemsTableWrap');
    const msg      = document.getElementById('selectOrderMsg');

    function loadItems(opt) {
        if (!opt || !opt.dataset.items) { wrap.classList.add('d-none'); msg.classList.remove('d-none'); return; }
        const items = JSON.parse(opt.dataset.items);
        body.innerHTML = '';
        items.forEach((item, i) => {
            const pending = Math.max(0, (parseFloat(item.quantity_ordered) || 0) - (parseFloat(item.quantity_received) || 0));
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${item.product_name || '—'}
                    <input type="hidden" name="items[${i}][purchase_order_item_id]" value="${item.item_id}">
                    <input type="hidden" name="items[${i}][product_id]" value="${item.product_id}">
                    <input type="hidden" name="items[${i}][quantity_ordered]" value="${item.quantity_ordered}">
                </td>
                <td class="text-end">${parseFloat(item.quantity_ordered).toFixed(2)}</td>
                <td class="text-end ${pending <= 0 ? 'text-muted' : 'fw-semibold'}">${pending.toFixed(2)}</td>
                <td><input type="number" name="items[${i}][quantity_received]" class="form-control form-control-sm" value="${pending.toFixed(2)}" min="0" step="0.01" max="${pending}"></td>
                <td><div class="input-group input-group-sm"><span class="input-group-text">$</span><input type="number" name="items[${i}][unit_price]" class="form-control" value="${parseFloat(item.unit_price).toFixed(2)}" min="0" step="0.01"></div></td>`;
            body.appendChild(tr);
        });
        wrap.classList.remove('d-none');
        msg.classList.add('d-none');
    }

    const whSel = document.getElementById('warehouseSelect');

    orderSel.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        loadItems(opt);
        // Autoseleccionar el almacén destino de la orden
        if (whSel && opt && opt.dataset.warehouse) {
            whSel.value = opt.dataset.warehouse;
        }
    });

    if (orderSel.value) loadItems(orderSel.options[orderSel.selectedIndex]);
})();
</script>
@endpush
@endsection
