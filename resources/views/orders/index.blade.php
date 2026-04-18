@extends('layouts.app')
@section('title', 'Órdenes y Pedidos')
@section('page')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><i class="bi bi-clipboard2-data text-primary me-2"></i>Órdenes</h1>
            <p class="text-muted mb-0">Gestión de órdenes y pedidos</p>
        </div>
        <a href="{{ route('orders.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Nueva orden</a>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-auto">
                    <input type="text" name="search" class="form-control form-control-sm" value="{{ $filters['search'] ?? '' }}" placeholder="Buscar...">
                </div>
                <div class="col-auto">
                    <select name="order_type" class="form-select form-select-sm">
                        <option value="">Tipo</option>
                        @foreach(\App\Models\Order::ORDER_TYPE_LABELS as $val => $label)
                            <option value="{{ $val }}" {{ ($filters['order_type'] ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Estado</option>
                        @foreach(\App\Models\Order::STATUS_LABELS as $val => $label)
                            <option value="{{ $val }}" {{ ($filters['status'] ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <select name="priority" class="form-select form-select-sm">
                        <option value="">Prioridad</option>
                        @foreach(\App\Models\Order::PRIORITY_LABELS as $val => $label)
                            <option value="{{ $val }}" {{ ($filters['priority'] ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto"><input type="date" name="from" class="form-control form-control-sm" value="{{ $filters['from'] ?? '' }}"></div>
                <div class="col-auto"><input type="date" name="to" class="form-control form-control-sm" value="{{ $filters['to'] ?? '' }}"></div>
                <div class="col-auto"><button class="btn btn-sm btn-dark" type="submit"><i class="bi bi-search"></i></button></div>
                <div class="col-auto"><a href="{{ route('orders.index') }}" class="btn btn-sm btn-outline-secondary">Limpiar</a></div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Número</th>
                            <th>Tipo</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Prioridad</th>
                            <th class="text-end">Total</th>
                            <th>Estado</th>
                            <th>Entrega esperada</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td>
                                    <a href="{{ route('orders.show', $order) }}" class="text-decoration-none fw-semibold">{{ $order->order_number }}</a>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $order->order_type === 'customer' ? 'primary' : 'dark' }}">
                                        {{ \App\Models\Order::ORDER_TYPE_LABELS[$order->order_type] ?? $order->order_type }}
                                    </span>
                                </td>
                                <td>{{ $order->order_date?->format('d/m/Y') }}</td>
                                <td>{{ $order->client_name ?: '—' }}</td>
                                <td>
                                    <span class="badge bg-{{ \App\Models\Order::PRIORITY_COLORS[$order->priority] ?? 'secondary' }}">
                                        {{ \App\Models\Order::PRIORITY_LABELS[$order->priority] ?? $order->priority }}
                                    </span>
                                </td>
                                <td class="text-end fw-semibold">${{ number_format($order->total, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ \App\Models\Order::STATUS_COLORS[$order->status] ?? 'secondary' }}">
                                        {{ \App\Models\Order::STATUS_LABELS[$order->status] ?? $order->status }}
                                    </span>
                                </td>
                                <td>{{ $order->expected_date?->format('d/m/Y') ?? '—' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('orders.show', $order) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-clipboard2-data fs-1 d-block mb-2"></i>
                                    No hay órdenes registradas
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="mt-4 d-flex justify-content-center">{{ $orders->links() }}</div>
</div>
@endsection
