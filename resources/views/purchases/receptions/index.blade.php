@extends('layouts.app')
@section('title', 'Recepciones')
@section('page')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><i class="bi bi-box-arrow-in-down-right text-primary me-2"></i>Recepciones</h1>
            <p class="text-muted mb-0">Registro de mercancía recibida</p>
        </div>
        <a href="{{ route('purchases.receptions.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Nueva recepción
        </a>
    </div>

    @if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if(session('error'))<div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    <div class="d-flex gap-2 flex-wrap mb-3">
        @foreach(array_merge(['todos' => 'Todos'], \App\Models\PurchaseReception::STATUS_LABELS) as $val => $label)
            <a href="{{ request()->fullUrlWithQuery(['status' => $val]) }}"
               class="btn btn-sm {{ $status === $val ? 'btn-primary' : 'btn-outline-secondary' }}">
                {{ $label }}
                <span class="badge {{ $status === $val ? 'bg-white text-primary' : 'bg-secondary' }} ms-1">{{ $counts[$val] ?? 0 }}</span>
            </a>
        @endforeach
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Recepción</th>
                        <th>Orden de compra</th>
                        <th>Proveedor</th>
                        <th>Fecha</th>
                        <th>Factura</th>
                        <th class="text-end">Total</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($receptions as $rec)
                    <tr>
                        <td><a href="{{ route('purchases.receptions.show', $rec) }}" class="fw-semibold text-decoration-none">{{ $rec->reception_number }}</a></td>
                        <td>
                            @if($rec->purchaseOrder)
                            <a href="{{ route('purchases.orders.show', $rec->purchaseOrder) }}" class="small">{{ $rec->purchaseOrder->order_number }}</a>
                            @else —
                            @endif
                        </td>
                        <td>{{ $rec->purchaseOrder?->supplier?->display_name ?? '—' }}</td>
                        <td>{{ $rec->reception_date?->format('d/m/Y') }}</td>
                        <td>{{ $rec->invoice_number ?? '—' }}</td>
                        <td class="text-end fw-semibold">${{ number_format($rec->total, 2) }}</td>
                        <td><span class="badge bg-{{ \App\Models\PurchaseReception::STATUS_COLORS[$rec->status] }}-subtle text-{{ \App\Models\PurchaseReception::STATUS_COLORS[$rec->status] }}">{{ \App\Models\PurchaseReception::STATUS_LABELS[$rec->status] }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('purchases.receptions.show', $rec) }}" class="btn btn-sm btn-outline-secondary me-1"><i class="bi bi-eye"></i></a>
                            @if($rec->status === 'borrador')
                            <form action="{{ route('purchases.receptions.destroy', $rec) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar esta recepción?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-5 text-muted">
                        <i class="bi bi-box-arrow-in-down-right display-4 d-block mb-2 opacity-25"></i>
                        No hay recepciones registradas.
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($receptions->hasPages())<div class="card-footer bg-white">{{ $receptions->links() }}</div>@endif
    </div>
</div>
@endsection
