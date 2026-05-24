@extends('layouts.app')
@section('title', 'Detalle de receta')

@section('page')
@php
    $statusColor = \App\Models\Recipe::STATUS_COLORS[$recipe->status] ?? 'secondary';
    $statusLabel = \App\Models\Recipe::STATUS_LABELS[$recipe->status] ?? $recipe->status;
    $totalCost   = $recipe->items->sum('total_cost');
@endphp
<div class="container-fluid">

    {{-- ── Encabezado ── --}}
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h1 class="mb-0"><i class="bi bi-journal-text text-primary me-2"></i>{{ $recipe->name }}</h1>
                <span class="badge bg-{{ $statusColor }}-subtle text-{{ $statusColor }} border border-{{ $statusColor }}-subtle fs-6">
                    {{ $statusLabel }}
                </span>
            </div>
            <p class="text-muted mb-0">
                <span class="badge bg-light text-secondary border me-1">{{ $recipe->recipe_number }}</span>
                Creada por {{ $recipe->createdBy?->name ?? '—' }}
                · {{ $recipe->created_at->format('d/m/Y') }}
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('recipes.edit', $recipe) }}" class="btn btn-outline-primary">
                <i class="bi bi-pencil me-1"></i> Editar
            </a>
            @if($recipe->status !== 'activa')
            <form action="{{ route('recipes.destroy', $recipe) }}" method="POST" class="d-inline">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-outline-danger"
                        onclick="return confirm('¿Eliminar esta receta?')">
                    <i class="bi bi-trash me-1"></i> Eliminar
                </button>
            </form>
            @endif
            <a href="{{ route('recipes.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Volver
            </a>
        </div>
    </div>

    {{-- ── KPI Cards ── --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                         style="width:48px;height:48px;flex-shrink:0">
                        <i class="bi bi-box-seam text-primary fs-5"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Producto final</div>
                        <div class="fw-bold">{{ $recipe->product?->name ?? '—' }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center"
                         style="width:48px;height:48px;flex-shrink:0">
                        <i class="bi bi-stack text-info fs-5"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Cantidad que produce</div>
                        <div class="fw-bold">{{ number_format($recipe->quantity_produced, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center"
                         style="width:48px;height:48px;flex-shrink:0">
                        <i class="bi bi-layers text-warning fs-5"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Ingredientes</div>
                        <div class="fw-bold">{{ $recipe->items->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center"
                         style="width:48px;height:48px;flex-shrink:0">
                        <i class="bi bi-currency-dollar text-success fs-5"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Costo estimado total</div>
                        <div class="fw-bold">${{ number_format($totalCost, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Tabla de ingredientes ── --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="fw-bold text-primary mb-0">
                <i class="bi bi-layers me-1"></i> Ingredientes (materias primas)
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Materia prima</th>
                            <th class="text-end">Cantidad</th>
                            <th class="text-end">Costo unitario</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recipe->items as $i => $item)
                        <tr>
                            <td class="text-muted small">{{ $i + 1 }}</td>
                            <td class="fw-medium">{{ $item->product?->name ?? '—' }}</td>
                            <td class="text-end">{{ number_format($item->quantity, 2) }}</td>
                            <td class="text-end">${{ number_format($item->unit_cost, 2) }}</td>
                            <td class="text-end fw-semibold">${{ number_format($item->total_cost, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-1 opacity-25"></i>
                                Sin ingredientes registrados.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($recipe->items->count())
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="4" class="text-end fw-bold">Costo total estimado</td>
                            <td class="text-end fw-bold text-success">${{ number_format($totalCost, 2) }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- ── Descripción ── --}}
    @if($recipe->description)
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h6 class="fw-bold text-primary mb-0">
                <i class="bi bi-chat-left-text me-1"></i> Notas / Descripción
            </h6>
        </div>
        <div class="card-body">
            <p class="mb-0 text-muted">{{ $recipe->description }}</p>
        </div>
    </div>
    @endif

</div>
@endsection
