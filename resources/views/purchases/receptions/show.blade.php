@extends('layouts.app')
@section('title', 'Recepción ' . $reception->reception_number)
@section('page')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h1 class="mb-0"><i class="bi bi-box-arrow-in-down-right text-primary me-2"></i>{{ $reception->reception_number }}</h1>
                <span class="badge bg-{{ \App\Models\PurchaseReception::STATUS_COLORS[$reception->status] }}-subtle text-{{ \App\Models\PurchaseReception::STATUS_COLORS[$reception->status] }} fs-6">
                    {{ \App\Models\PurchaseReception::STATUS_LABELS[$reception->status] }}
                </span>
            </div>
            <p class="text-muted mb-0">
                OC: <a href="{{ route('purchases.orders.show', $reception->purchaseOrder) }}">{{ $reception->purchaseOrder?->order_number }}</a>
                · {{ $reception->purchaseOrder?->supplier?->display_name }}
                · {{ $reception->reception_date?->format('d/m/Y') }}
            </p>
        </div>
        <a href="{{ route('purchases.receptions.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Volver</a>
    </div>

    @if(session('success'))<div class="alert alert-success alert-dismissible fade show mb-3"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if(session('error'))<div class="alert alert-danger alert-dismissible fade show mb-3"><i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-2 px-4">
                    <h6 class="fw-bold mb-0"><i class="bi bi-list-ul me-1 text-primary"></i> Ítems recibidos</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Producto</th>
                                <th class="text-end">Ordenado</th>
                                <th class="text-end">Recibido</th>
                                <th class="text-end">P. Unit.</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reception->items as $item)
                            <tr>
                                <td>{{ $item->product?->name ?? '—' }}</td>
                                <td class="text-end">{{ number_format($item->quantity_ordered, 2) }}</td>
                                <td class="text-end fw-semibold text-success">{{ number_format($item->quantity_received, 2) }}</td>
                                <td class="text-end">${{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-end fw-semibold">${{ number_format($item->total, 2) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">Sin ítems</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="4" class="text-end fw-bold fs-6">Total recibido:</td>
                                <td class="text-end fw-bold fs-6 text-primary">${{ number_format($reception->total, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            @if($reception->notes)
            <div class="card border-0 shadow-sm">
                <div class="card-body"><small class="text-muted d-block mb-1">Notas</small>{{ $reception->notes }}</div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    @if($reception->invoice_number)
                    <div class="mb-3">
                        <small class="text-muted d-block">Nº Factura</small>
                        <strong>{{ $reception->invoice_number }}</strong>
                    </div>
                    @endif
                    <div class="mb-3">
                        <small class="text-muted d-block">Almacén</small>
                        <strong>{{ $reception->warehouse?->name ?? '—' }}</strong>
                    </div>
                </div>
            </div>

            @if($reception->status === 'borrador')
            <div class="d-flex flex-column gap-2">
                <form action="{{ route('purchases.receptions.confirm', $reception) }}" method="POST" onsubmit="return confirm('¿Confirmar recepción? Se actualizará el stock y se creará la cuenta por pagar.')">
                    @csrf
                    <button class="btn btn-success w-100"><i class="bi bi-check-circle me-1"></i> Confirmar recepción</button>
                </form>
                <form action="{{ route('purchases.receptions.cancel', $reception) }}" method="POST" onsubmit="return confirm('¿Cancelar esta recepción?')">
                    @csrf
                    <button class="btn btn-outline-warning w-100">Cancelar recepción</button>
                </form>
                <form action="{{ route('purchases.receptions.destroy', $reception) }}" method="POST" onsubmit="return confirm('¿Eliminar esta recepción?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger w-100"><i class="bi bi-trash me-1"></i> Eliminar</button>
                </form>
            </div>
            @endif

            @if($reception->status === 'confirmada' && $reception->accountPayable)
            <a href="{{ route('purchases.payables.show', $reception->accountPayable) }}" class="btn btn-outline-primary w-100">
                <i class="bi bi-credit-card-2-front me-1"></i> Ver cuenta por pagar
            </a>
            @endif
        </div>
    </div>
</div>
@endsection
