@extends('layouts.app')

@section('title', 'Cotización ' . $quotation->quotation_number)

@include('layouts._compact_style')

@section('page')
<div class="view-compact container-fluid">
    <div class="d-flex justify-content-between align-items-start mb-3 page-head">
        <div>
            <h1 class="mb-1"><i class="bi bi-file-earmark-text text-primary me-2"></i>{{ $quotation->quotation_number }}
                <span class="badge bg-{{ \App\Models\SalesQuotation::STATUS_COLORS[$quotation->status] ?? 'secondary' }} ms-1">{{ \App\Models\SalesQuotation::STATUS_LABELS[$quotation->status] ?? $quotation->status }}</span>
            </h1>
            <p class="text-muted mb-0">{{ $quotation->client?->display_name ?? $quotation->client_name ?? 'Cliente ocasional' }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('sales-quotations.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
            @if($quotation->status !== 'convertida')
                <a href="{{ route('sales-quotations.edit', $quotation) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i> Editar</a>
                <form action="{{ route('sales-quotations.convert', $quotation) }}" method="POST" onsubmit="return confirm('¿Convertir esta cotización en venta?')">
                    @csrf
                    <button class="btn btn-sm btn-success"><i class="bi bi-cart-plus"></i> Convertir a venta</button>
                </form>
            @else
                <a href="{{ route('sales.show', $quotation->sale_id) }}" class="btn btn-sm btn-primary"><i class="bi bi-receipt"></i> Ver venta</a>
            @endif
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><strong>Productos</strong></div>
                <div class="card-body p-0">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light"><tr><th>Producto</th><th class="text-end">Cantidad</th><th class="text-end">Precio</th><th class="text-end">Descuento</th><th class="text-end">Total</th></tr></thead>
                        <tbody>
                            @foreach($quotation->items as $it)
                                <tr>
                                    <td>{{ $it->product?->name }}</td>
                                    <td class="text-end">{{ number_format($it->quantity, 2) }}</td>
                                    <td class="text-end">${{ number_format($it->unit_price, 2) }}</td>
                                    <td class="text-end">${{ number_format($it->discount, 2) }}</td>
                                    <td class="text-end">${{ number_format($it->total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr><td colspan="4" class="text-end text-muted">Subtotal</td><td class="text-end">${{ number_format($quotation->subtotal, 2) }}</td></tr>
                            <tr><td colspan="4" class="text-end text-muted">Impuesto</td><td class="text-end">${{ number_format($quotation->tax, 2) }}</td></tr>
                            <tr><td colspan="4" class="text-end fw-bold">Total</td><td class="text-end fw-bold text-success">${{ number_format($quotation->total, 2) }}</td></tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><strong>Datos</strong></div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-5 text-muted">Fecha</dt><dd class="col-7">{{ $quotation->quotation_date?->format('d/m/Y') }}</dd>
                        <dt class="col-5 text-muted">Válida hasta</dt><dd class="col-7">{{ $quotation->valid_until?->format('d/m/Y') ?? '—' }}</dd>
                        <dt class="col-5 text-muted">Creada por</dt><dd class="col-7">{{ $quotation->createdBy?->name }}</dd>
                        @if($quotation->notes)<dt class="col-5 text-muted">Notas</dt><dd class="col-7">{{ $quotation->notes }}</dd>@endif
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
