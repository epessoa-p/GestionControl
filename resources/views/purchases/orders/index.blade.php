@extends('layouts.app')
@section('title', 'Órdenes de Compra')
@section('page')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><i class="bi bi-bag-check text-primary me-2"></i>Órdenes de Compra</h1>
            <p class="text-muted mb-0">Gestión de órdenes a proveedores</p>
        </div>
        <a href="{{ route('purchases.orders.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Nueva orden
        </a>
    </div>

    @if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if(session('error'))<div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    <div class="d-flex gap-2 flex-wrap mb-3">
        @foreach(array_merge(['todos' => 'Todos'], \App\Models\PurchaseOrder::STATUS_LABELS) as $val => $label)
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
                        <th>Orden</th>
                        <th>Proveedor</th>
                        <th>Almacén</th>
                        <th>Fecha</th>
                        <th>Entrega esp.</th>
                        <th class="text-end">Total</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td><a href="{{ route('purchases.orders.show', $order) }}" class="fw-semibold text-decoration-none">{{ $order->order_number }}</a></td>
                        <td>{{ $order->supplier?->display_name ?? '—' }}</td>
                        <td>{{ $order->warehouse?->name ?? '—' }}</td>
                        <td>{{ $order->order_date?->format('d/m/Y') }}</td>
                        <td>{{ $order->expected_date?->format('d/m/Y') ?? '—' }}</td>
                        <td class="text-end fw-semibold">${{ number_format($order->total, 2) }}</td>
                        <td><span class="badge bg-{{ \App\Models\PurchaseOrder::STATUS_COLORS[$order->status] }}-subtle text-{{ \App\Models\PurchaseOrder::STATUS_COLORS[$order->status] }}">{{ \App\Models\PurchaseOrder::STATUS_LABELS[$order->status] }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('purchases.orders.show', $order) }}" class="btn btn-sm btn-outline-secondary me-1"><i class="bi bi-eye"></i></a>
                            @if(in_array($order->status, ['borrador','cancelada']))
                            <form action="{{ route('purchases.orders.destroy', $order) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar esta orden?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-5 text-muted">
                        <i class="bi bi-bag-check display-4 d-block mb-2 opacity-25"></i>
                        No hay órdenes registradas.
                        <a href="{{ route('purchases.orders.create') }}" class="d-block mt-2">Crear la primera</a>
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())<div class="card-footer bg-white">{{ $orders->links() }}</div>@endif
    </div>
</div>
@endsection
