@extends('layouts.app')
@section('title', 'Ventas')
@section('page')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h1 class="mb-1"><i class="bi bi-cart3 text-primary me-2"></i>Ventas</h1><p class="text-muted mb-0">Registro y gestión de ventas</p></div>
        <a href="{{ route('sales.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Nueva venta</a>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-auto">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Estado</option>
                        @foreach(\App\Models\Sale::STATUS_LABELS as $val => $label)
                            <option value="{{ $val }}" {{ ($filters['status'] ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <select name="promoter_id" class="form-select form-select-sm">
                        <option value="">Promotor</option>
                        @foreach($promoters as $p)
                            <option value="{{ $p->id }}" {{ ($filters['promoter_id'] ?? '') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto"><input type="date" name="from" class="form-control form-control-sm" value="{{ $filters['from'] ?? '' }}"></div>
                <div class="col-auto"><input type="date" name="to" class="form-control form-control-sm" value="{{ $filters['to'] ?? '' }}"></div>
                <div class="col-auto"><button class="btn btn-sm btn-dark" type="submit"><i class="bi bi-search"></i></button></div>
                <div class="col-auto"><a href="{{ route('sales.index') }}" class="btn btn-sm btn-outline-secondary">Limpiar</a></div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Numero</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Promotor</th>
                            <th>Tipo</th>
                            <th>Pago</th>
                            <th class="text-end">Total</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                            <tr>
                                <td><a href="{{ route('sales.show', $sale) }}" class="text-decoration-none fw-semibold">{{ $sale->sale_number }}</a></td>
                                <td>{{ $sale->sale_date?->format('d/m/Y') }}</td>
                                <td>{{ $sale->client_name ?: '-' }}</td>
                                <td>{{ $sale->promoter?->name ?? '-' }}</td>
                                <td><span class="badge bg-{{ ($sale->sale_type ?? 'cash') === 'credit' ? 'warning text-dark' : 'info' }}">{{ \App\Models\Sale::SALE_TYPE_LABELS[$sale->sale_type ?? 'cash'] ?? 'Contado' }}</span></td>
                                <td><span class="badge bg-secondary">{{ \App\Models\Sale::PAYMENT_LABELS[$sale->payment_method] ?? $sale->payment_method }}</span></td>
                                <td class="text-end fw-semibold">${{ number_format($sale->total, 2) }}</td>
                                <td><span class="badge bg-{{ \App\Models\Sale::STATUS_COLORS[$sale->status] ?? 'secondary' }}">{{ \App\Models\Sale::STATUS_LABELS[$sale->status] ?? $sale->status }}</span></td>
                                <td class="text-end">
                                    @if($sale->status === 'pending')
                                        <form action="{{ route('sales.complete', $sale) }}" method="POST" class="d-inline">@csrf
                                            <button class="btn btn-sm btn-outline-success" onclick="return confirm('¿Completar venta?')"><i class="bi bi-check-lg"></i></button>
                                        </form>
                                    @endif
                                    @if($sale->status !== 'cancelled')
                                        <form action="{{ route('sales.cancel', $sale) }}" method="POST" class="d-inline">@csrf
                                            <button class="btn btn-sm btn-outline-warning" onclick="return confirm('¿Cancelar venta?')"><i class="bi bi-x-lg"></i></button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-1 d-block mb-2"></i>No hay ventas registradas</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="mt-4 d-flex justify-content-center">{{ $sales->links() }}</div>
</div>
@endsection
