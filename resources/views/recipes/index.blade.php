@extends('layouts.app')

@section('title', 'Recetas')

@push('styles')
<style>
    .tab-selector { display: flex; gap: 14px; margin-bottom: 24px; flex-wrap: wrap; }

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
        min-width: 175px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.04);
    }
    .tab-card:hover {
        border-color: #adb5bd;
        color: #343a40;
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0,0,0,0.09);
    }
    .tab-icon {
        width: 44px; height: 44px;
        border-radius: 11px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.25rem;
        background: #f1f3f5;
        color: #868e96;
        flex-shrink: 0;
        transition: all 0.2s ease;
    }
    .tab-label  { font-weight: 600; font-size: 0.9rem; line-height: 1.2; }
    .tab-count  { font-size: 1.3rem; font-weight: 700; line-height: 1; margin-top: 2px; }
    .tab-unit   { font-size: 0.72rem; opacity: 0.65; margin-top: 1px; }

    .tab-card.tab-active-secondary { border-color: #6c757d; background: #f8f9fa; color: #495057; box-shadow: 0 4px 14px rgba(108,117,125,0.14); }
    .tab-card.tab-active-secondary .tab-icon { background: #6c757d; color: #fff; }
    .tab-card.tab-active-secondary .tab-count { color: #495057; }

    .tab-card.tab-active-success { border-color: #198754; background: #f0faf5; color: #198754; box-shadow: 0 4px 14px rgba(25,135,84,0.14); }
    .tab-card.tab-active-success .tab-icon { background: #198754; color: #fff; }
    .tab-card.tab-active-success .tab-count { color: #198754; }

    .tab-card.tab-active-danger { border-color: #dc3545; background: #fff5f5; color: #dc3545; box-shadow: 0 4px 14px rgba(220,53,69,0.13); }
    .tab-card.tab-active-danger .tab-icon { background: #dc3545; color: #fff; }
    .tab-card.tab-active-danger .tab-count { color: #dc3545; }
</style>
@endpush

@section('page')
<div class="container-fluid">

    {{-- Encabezado --}}
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="mb-1"><i class="bi bi-journal-text"></i> Recetas</h1>
            <p class="text-muted mb-0">Catálogo de recetas de producción.</p>
        </div>
        <a href="{{ route('recipes.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Nueva receta
        </a>
    </div>

    {{-- Selectores de estado --}}
    <div class="tab-selector">
        @php
            $tabs = [
                'activa'   => ['icon' => 'bi-check-circle', 'color' => 'success', 'label' => 'Activas',   'unit' => 'recetas'],
                'borrador' => ['icon' => 'bi-pencil-square','color' => 'secondary','label' => 'Borrador', 'unit' => 'recetas'],
                'inactiva' => ['icon' => 'bi-x-circle',     'color' => 'danger',   'label' => 'Inactivas','unit' => 'recetas'],
            ];
        @endphp
        @foreach($tabs as $status => $cfg)
        <a href="{{ route('recipes.index', ['status' => $status]) }}"
           class="tab-card {{ $activeStatus === $status ? 'tab-active-'.$cfg['color'] : '' }}">
            <div class="tab-icon"><i class="bi {{ $cfg['icon'] }}"></i></div>
            <div>
                <div class="tab-label">{{ $cfg['label'] }}</div>
                <div class="tab-count">{{ $counts[$status] }}</div>
                <div class="tab-unit">{{ $cfg['unit'] }}</div>
            </div>
        </a>
        @endforeach
    </div>

    {{-- Tabla --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Número</th>
                            <th>Nombre</th>
                            <th>Producto final</th>
                            <th class="text-center">Ingredientes</th>
                            <th class="text-end">Cant. produce</th>
                            <th class="text-end">Costo estimado</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recipes as $recipe)
                        <tr>
                            <td>
                                <a href="{{ route('recipes.show', $recipe) }}"
                                   class="fw-medium text-decoration-none">
                                    {{ $recipe->recipe_number }}
                                </a>
                            </td>
                            <td class="fw-medium">{{ $recipe->name }}</td>
                            <td>{{ $recipe->product?->name ?? '-' }}</td>
                            <td class="text-center">
                                <span class="badge bg-light text-secondary border">
                                    {{ $recipe->items->count() }}
                                </span>
                            </td>
                            <td class="text-end">{{ number_format($recipe->quantity_produced, 2) }}</td>
                            <td class="text-end">
                                ${{ number_format($recipe->items->sum('total_cost'), 2) }}
                            </td>
                            <td>
                                @php $color = \App\Models\Recipe::STATUS_COLORS[$recipe->status] ?? 'secondary'; @endphp
                                <span class="badge bg-{{ $color }}-subtle text-{{ $color }} border border-{{ $color }}-subtle">
                                    {{ \App\Models\Recipe::STATUS_LABELS[$recipe->status] ?? $recipe->status }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('recipes.show', $recipe) }}"
                                   class="btn btn-sm btn-outline-secondary me-1" title="Ver detalle">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('recipes.edit', $recipe) }}"
                                   class="btn btn-sm btn-outline-primary me-1" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('recipes.destroy', $recipe) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                            title="Eliminar"
                                            onclick="return confirm('¿Eliminar esta receta?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-journal-x fs-1 d-block mb-2 opacity-25"></i>
                                <span class="fw-medium">No hay recetas con estado
                                    "{{ \App\Models\Recipe::STATUS_LABELS[$activeStatus] ?? $activeStatus }}".
                                </span><br>
                                <a href="{{ route('recipes.create') }}" class="btn btn-sm btn-primary mt-3">
                                    <i class="bi bi-plus-lg me-1"></i> Crear receta
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4 d-flex justify-content-center">{{ $recipes->links() }}</div>

</div>
@endsection
