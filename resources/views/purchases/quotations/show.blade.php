@extends('layouts.app')
@section('title', 'Cotización ' . $quotation->quotation_number)
@section('page')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h1 class="mb-0"><i class="bi bi-file-earmark-text text-primary me-2"></i>{{ $quotation->quotation_number }}</h1>
                <span class="badge bg-{{ \App\Models\PurchaseQuotation::STATUS_COLORS[$quotation->status] }}-subtle text-{{ \App\Models\PurchaseQuotation::STATUS_COLORS[$quotation->status] }} fs-6">
                    {{ \App\Models\PurchaseQuotation::STATUS_LABELS[$quotation->status] }}
                </span>
            </div>
            <p class="text-muted mb-0">
                {{ $quotation->supplier?->display_name ?? 'Sin proveedor' }}
                @if($quotation->purchaseRequest)
                · Solicitud: <a href="{{ route('purchases.requests.show', $quotation->purchaseRequest) }}">{{ $quotation->purchaseRequest->request_number }}</a>
                @endif
            </p>
        </div>
        <div class="d-flex gap-2">
            @if(in_array($quotation->status, ['borrador','enviada','recibida']))
            <a href="{{ route('purchases.quotations.edit', $quotation) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil me-1"></i> Editar</a>
            @endif
            <a href="{{ route('purchases.quotations.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Volver</a>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success alert-dismissible fade show mb-3"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if(session('error'))<div class="alert alert-danger alert-dismissible fade show mb-3"><i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    <div class="row g-4">
        <div class="col-lg-8">
            {{-- Ítems --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-2 px-4">
                    <h6 class="fw-bold mb-0"><i class="bi bi-list-ul me-1 text-primary"></i> Ítems</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Producto</th>
                                <th class="text-end">Cantidad</th>
                                <th class="text-end">Precio unit.</th>
                                <th class="text-end">Descuento</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($quotation->items as $item)
                            <tr>
                                <td>{{ $item->product?->name ?? '—' }}</td>
                                <td class="text-end">{{ number_format($item->quantity, 2) }}</td>
                                <td class="text-end">${{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-end">${{ number_format($item->discount, 2) }}</td>
                                <td class="text-end fw-semibold">${{ number_format($item->total, 2) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">Sin ítems</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light">
                            <tr><td colspan="4" class="text-end">Subtotal:</td><td class="text-end">${{ number_format($quotation->subtotal, 2) }}</td></tr>
                            @if($quotation->discount > 0)
                            <tr><td colspan="4" class="text-end text-muted">Descuento:</td><td class="text-end text-muted">-${{ number_format($quotation->discount, 2) }}</td></tr>
                            @endif
                            <tr><td colspan="4" class="text-end">IVA (12%):</td><td class="text-end">${{ number_format($quotation->tax, 2) }}</td></tr>
                            <tr><td colspan="4" class="text-end fw-bold fs-6">Total:</td><td class="text-end fw-bold fs-6 text-primary">${{ number_format($quotation->total, 2) }}</td></tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            @if($quotation->notes)
            <div class="card border-0 shadow-sm">
                <div class="card-body"><small class="text-muted d-block mb-1">Notas</small>{{ $quotation->notes }}</div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            {{-- Fechas --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <small class="text-muted d-block">Fecha cotización</small>
                        <strong>{{ $quotation->quotation_date?->format('d/m/Y') }}</strong>
                    </div>
                    @if($quotation->valid_until)
                    <div>
                        <small class="text-muted d-block">Válida hasta</small>
                        <strong class="{{ $quotation->valid_until < now() && $quotation->status !== 'aprobada' ? 'text-danger' : '' }}">
                            {{ $quotation->valid_until->format('d/m/Y') }}
                        </strong>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Transiciones --}}
            @php
                $transitions = [
                    'borrador' => ['enviada' => ['Marcar enviada', 'primary']],
                    'enviada'  => ['recibida' => ['Marcar recibida', 'info'], 'cancelada' => ['Cancelar', 'warning']],
                    'recibida' => ['aprobada' => ['Aprobar', 'success'], 'rechazada' => ['Rechazar', 'danger']],
                ];
                $avail = $transitions[$quotation->status] ?? [];
            @endphp
            @if($avail)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-2 px-4">
                    <h6 class="fw-bold mb-0 small"><i class="bi bi-arrow-right-circle me-1 text-primary"></i> Cambiar estado</h6>
                </div>
                <div class="card-body p-3 d-flex flex-column gap-2">
                    @foreach($avail as $ns => [$label, $color])
                    <form action="{{ route('purchases.quotations.update-status', $quotation) }}" method="POST">
                        @csrf <input type="hidden" name="status" value="{{ $ns }}">
                        <button class="btn btn-{{ $color }} btn-sm w-100">{{ $label }}</button>
                    </form>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Aprobar / crear OC --}}
            @if($quotation->status === 'aprobada')
            <a href="{{ route('purchases.orders.create', ['quotation_id' => $quotation->id]) }}"
               class="btn btn-success w-100 mb-2">
                <i class="bi bi-bag-check me-1"></i> Crear Orden de Compra
            </a>
            @elseif($quotation->status === 'recibida')
            <form action="{{ route('purchases.quotations.approve', $quotation) }}" method="POST" class="mb-2">
                @csrf
                <button class="btn btn-success w-100"><i class="bi bi-check-circle me-1"></i> Aprobar cotización</button>
            </form>
            @endif

            {{-- Eliminar --}}
            @if(in_array($quotation->status, ['borrador','rechazada','cancelada']))
            <form action="{{ route('purchases.quotations.destroy', $quotation) }}" method="POST" onsubmit="return confirm('¿Eliminar esta cotización?')">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger w-100"><i class="bi bi-trash me-1"></i> Eliminar</button>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection
