@extends('layouts.app')

@section('title', 'Productos')

@push('styles')
<style>
    .tab-selector { display: flex; gap: 14px; margin-bottom: 24px; }

    .tab-card {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 22px;
        border-radius: 14px;
        border: 2px solid #e9ecef;
        background: #fff;
        text-decoration: none;
        color: #6c757d;
        transition: all 0.2s ease;
        min-width: 210px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.04);
    }
    .tab-card:hover {
        border-color: #adb5bd;
        color: #343a40;
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0,0,0,0.09);
    }
    .tab-icon {
        width: 46px;
        height: 46px;
        border-radius: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        background: #f1f3f5;
        color: #868e96;
        flex-shrink: 0;
        transition: all 0.2s ease;
    }
    .tab-label { font-weight: 600; font-size: 0.92rem; line-height: 1.2; }
    .tab-count { font-size: 1.35rem; font-weight: 700; line-height: 1; margin-top: 2px; }
    .tab-unit  { font-size: 0.72rem; opacity: 0.65; margin-top: 1px; }

    /* Producto Final — azul */
    .tab-card.tab-active-primary {
        border-color: #0d6efd;
        background: #f0f5ff;
        color: #0d6efd;
        box-shadow: 0 4px 16px rgba(13,110,253,0.15);
    }
    .tab-card.tab-active-primary .tab-icon {
        background: #0d6efd;
        color: #fff;
    }
    .tab-card.tab-active-primary .tab-count { color: #0d6efd; }

    /* Materia Prima — verde */
    .tab-card.tab-active-success {
        border-color: #198754;
        background: #f0faf5;
        color: #198754;
        box-shadow: 0 4px 16px rgba(25,135,84,0.13);
    }
    .tab-card.tab-active-success .tab-icon {
        background: #198754;
        color: #fff;
    }
    .tab-card.tab-active-success .tab-count { color: #198754; }

    /* Indicador activo bajo la tab-card */
    .tab-card.tab-active-primary::after,
    .tab-card.tab-active-success::after {
        content: '';
    }
</style>
@endpush

@section('page')
<div class="container-fluid">

    {{-- Encabezado --}}
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="mb-1"><i class="bi bi-box-seam"></i> Productos</h1>
            <p class="text-muted mb-0">Catálogo de productos por categoría.</p>
        </div>
        <a href="{{ route('products.create', ['category' => $activeTab]) }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>
            Nuevo {{ $activeTab === 'MATERIA PRIMA' ? 'materia prima' : 'producto final' }}
        </a>
    </div>

    {{-- Selectores de categoría --}}
    <div class="tab-selector">
        {{-- Producto Final --}}
        <a href="{{ route('products.index', ['tab' => 'PRODUCTO FINAL']) }}"
           class="tab-card {{ $activeTab === 'PRODUCTO FINAL' ? 'tab-active-primary' : '' }}">
            <div class="tab-icon"><i class="bi bi-box-seam"></i></div>
            <div>
                <div class="tab-label">Producto Final</div>
                <div class="tab-count">{{ $counts['PRODUCTO FINAL'] }}</div>
                <div class="tab-unit">productos</div>
            </div>
        </a>

        {{-- Materia Prima --}}
        <a href="{{ route('products.index', ['tab' => 'MATERIA PRIMA']) }}"
           class="tab-card {{ $activeTab === 'MATERIA PRIMA' ? 'tab-active-success' : '' }}">
            <div class="tab-icon"><i class="bi bi-layers"></i></div>
            <div>
                <div class="tab-label">Materia Prima</div>
                <div class="tab-count">{{ $counts['MATERIA PRIMA'] }}</div>
                <div class="tab-unit">ítems</div>
            </div>
        </a>
    </div>

    {{-- Tabla --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Producto</th>
                            <th>SKU</th>
                            <th>Empresa</th>
                            <th>Unidad</th>
                            <th class="text-end">Costo</th>
                            <th class="text-end">Precio</th>
                            <th class="text-end">Stock</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                        <tr>
                            <td class="fw-medium">{{ $product->name }}</td>
                            <td><span class="badge bg-light text-secondary border">{{ $product->sku }}</span></td>
                            <td>{{ $product->company?->name ?: '-' }}</td>
                            <td>{{ $product->measurementUnit?->symbol ?? $product->unit }}</td>
                            <td class="text-end">${{ number_format($product->cost, 2) }}</td>
                            <td class="text-end">${{ number_format($product->price, 2) }}</td>
                            <td class="text-end">
                                {{ number_format($product->current_stock, 2) }}
                                @if($product->isLowStock())
                                    <span class="badge bg-danger ms-1">Bajo</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('products.edit', $product) }}"
                                   class="btn btn-sm btn-outline-primary me-1">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('¿Eliminar este producto?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                                <span class="fw-medium">
                                    No hay {{ $activeTab === 'MATERIA PRIMA' ? 'materias primas' : 'productos finales' }} registrados.
                                </span><br>
                                <a href="{{ route('products.create', ['category' => $activeTab]) }}"
                                   class="btn btn-sm btn-primary mt-3">
                                    <i class="bi bi-plus-lg me-1"></i>
                                    Agregar {{ $activeTab === 'MATERIA PRIMA' ? 'materia prima' : 'producto final' }}
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4 d-flex justify-content-center">{{ $products->links() }}</div>

</div>
@endsection
