@extends('layouts.app')
@section('title', 'Reporte de Inventario')
@section('page')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">Reporte de Inventario</h1>
            <p class="text-muted mb-0">Estado actual del inventario de productos.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('reports.inventory', ['export' => 'xlsx']) }}" class="btn btn-success"><i class="bi bi-file-earmark-excel"></i> Exportar Excel</a>
            <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">Volver</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary bg-opacity-10">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Total productos</h6>
                    <h3 class="fw-bold text-primary mb-0">{{ $products->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-danger bg-opacity-10">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Stock bajo</h6>
                    <h3 class="fw-bold text-danger mb-0">{{ $products->filter(fn($p) => $p->isLowStock())->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success bg-opacity-10">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Valorización total</h6>
                    <h3 class="fw-bold text-success mb-0">${{ number_format($products->sum(fn($p) => $p->current_stock * $p->cost), 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>SKU</th>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Unidad</th>
                            <th class="text-end">Costo</th>
                            <th class="text-end">Precio</th>
                            <th class="text-end">Stock actual</th>
                            <th class="text-end">Stock mín.</th>
                            <th class="text-end">Valorización</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $i => $p)
                            <tr class="{{ $p->isLowStock() ? 'table-danger' : '' }}">
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $p->sku }}</td>
                                <td>
                                    {{ $p->name }}
                                    @if($p->isLowStock())
                                        <span class="badge bg-danger ms-1">Stock bajo</span>
                                    @endif
                                </td>
                                <td>{{ $p->category ?: '-' }}</td>
                                <td>{{ $p->unit }}</td>
                                <td class="text-end">${{ number_format($p->cost, 2) }}</td>
                                <td class="text-end">${{ number_format($p->price, 2) }}</td>
                                <td class="text-end">{{ number_format($p->current_stock, 2) }}</td>
                                <td class="text-end">{{ number_format($p->min_stock, 2) }}</td>
                                <td class="text-end">${{ number_format($p->current_stock * $p->cost, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="text-center text-muted py-4">No hay productos registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
