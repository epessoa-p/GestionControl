@extends('layouts.app')

@section('title', 'Devolución ' . $return->return_number)

@include('layouts._compact_style')

@section('page')
<div class="view-compact container-fluid">
    <div class="d-flex justify-content-between align-items-start mb-3 page-head">
        <div>
            <h1 class="mb-1"><i class="bi bi-arrow-return-left text-warning me-2"></i>{{ $return->return_number }}
                <span class="badge bg-{{ \App\Models\SalesReturn::STATUS_COLORS[$return->status] ?? 'secondary' }} ms-1">{{ \App\Models\SalesReturn::STATUS_LABELS[$return->status] ?? $return->status }}</span>
            </h1>
            <p class="text-muted mb-0">{{ \App\Models\SalesReturn::REASON_LABELS[$return->reason] ?? $return->reason }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('sales-returns.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
            @if($return->status === 'borrador')
                <form action="{{ route('sales-returns.confirm', $return) }}" method="POST" onsubmit="return confirm('¿Confirmar devolución? Se reingresará el inventario.')">
                    @csrf<button class="btn btn-sm btn-success"><i class="bi bi-check-lg"></i> Confirmar</button>
                </form>
                <form action="{{ route('sales-returns.destroy', $return) }}" method="POST" onsubmit="return confirm('¿Eliminar devolución?')">
                    @csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
            @elseif($return->status === 'confirmada')
                <form action="{{ route('sales-returns.cancel', $return) }}" method="POST" onsubmit="return confirm('¿Cancelar devolución? Se revertirá el inventario reingresado.')">
                    @csrf<button class="btn btn-sm btn-outline-danger"><i class="bi bi-slash-circle"></i> Cancelar</button>
                </form>
            @endif
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><strong>Productos</strong></div>
                <div class="card-body p-0">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light"><tr><th>Producto</th><th class="text-end">Cantidad</th><th class="text-end">Precio</th><th class="text-end">Total</th></tr></thead>
                        <tbody>
                            @foreach($return->items as $it)
                                <tr><td>{{ $it->product?->name }}</td><td class="text-end">{{ number_format($it->quantity, 2) }}</td><td class="text-end">${{ number_format($it->unit_price, 2) }}</td><td class="text-end">${{ number_format($it->total, 2) }}</td></tr>
                            @endforeach
                        </tbody>
                        <tfoot><tr><td colspan="3" class="text-end fw-bold">Total</td><td class="text-end fw-bold text-success">${{ number_format($return->total, 2) }}</td></tr></tfoot>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><strong>Datos</strong></div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-5 text-muted">Venta</dt><dd class="col-7">{{ $return->sale?->sale_number ?? '—' }}</dd>
                        <dt class="col-5 text-muted">Cliente</dt><dd class="col-7">{{ $return->client?->display_name ?? $return->client_name ?? '—' }}</dd>
                        <dt class="col-5 text-muted">Almacén</dt><dd class="col-7">{{ $return->warehouse?->name ?? '—' }}</dd>
                        <dt class="col-5 text-muted">Fecha</dt><dd class="col-7">{{ $return->return_date?->format('d/m/Y') }}</dd>
                        <dt class="col-5 text-muted">Creada por</dt><dd class="col-7">{{ $return->createdBy?->name }}</dd>
                        @if($return->notes)<dt class="col-5 text-muted">Notas</dt><dd class="col-7">{{ $return->notes }}</dd>@endif
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
