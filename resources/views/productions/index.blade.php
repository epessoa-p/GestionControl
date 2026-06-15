@extends('layouts.app')
@section('title', 'Produccion')
@section('page')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h1 class="mb-1"><i class="bi bi-gear-wide-connected text-primary me-2"></i>Producción</h1><p class="text-muted mb-0">Registro y control de órdenes de producción</p></div>
        <a href="{{ route('productions.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Nueva producción</a>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-auto">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Estado</option>
                        @foreach(\App\Models\Production::STATUS_LABELS as $val => $label)
                            <option value="{{ $val }}" {{ ($filters['status'] ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto"><input type="date" name="from" class="form-control form-control-sm" value="{{ $filters['from'] ?? '' }}"></div>
                <div class="col-auto"><input type="date" name="to" class="form-control form-control-sm" value="{{ $filters['to'] ?? '' }}"></div>
                <div class="col-auto"><button class="btn btn-sm btn-dark" type="submit"><i class="bi bi-search"></i></button></div>
                <div class="col-auto"><a href="{{ route('productions.index') }}" class="btn btn-sm btn-outline-secondary">Limpiar</a></div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Lote</th>
                            <th>Producto</th>
                            <th>Almacén</th>
                            <th>Fecha</th>
                            <th class="text-end">Cantidad</th>
                            <th class="text-end">Costo total</th>
                            <th>Estado</th>
                            <th>Creado por</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($productions as $prod)
                            <tr>
                                <td><a href="{{ route('productions.show', $prod) }}" class="text-decoration-none fw-semibold">{{ $prod->batch_number }}</a></td>
                                <td>{{ $prod->product?->name }}</td>
                                <td>
                                    @if($prod->warehouse)
                                        <span class="badge bg-light text-secondary border"><i class="bi bi-building me-1"></i>{{ $prod->warehouse->name }}</span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td>{{ $prod->production_date?->format('d/m/Y') }}</td>
                                <td class="text-end">{{ number_format($prod->quantity_produced, 2) }}</td>
                                <td class="text-end">${{ number_format($prod->total_cost, 2) }}</td>
                                <td><span class="badge bg-{{ \App\Models\Production::STATUS_COLORS[$prod->status] ?? 'secondary' }}">{{ \App\Models\Production::STATUS_LABELS[$prod->status] ?? $prod->status }}</span></td>
                                <td>{{ $prod->createdBy?->name }}</td>
                                <td class="text-end">
                                    <a href="{{ route('productions.show', $prod) }}" class="btn btn-sm btn-outline-primary me-1" title="Ver detalle"><i class="bi bi-eye"></i></a>
                                    @if($prod->status === 'planned')
                                        <form action="{{ route('productions.update-status', $prod) }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="status" value="in_progress">
                                            <button class="btn btn-sm btn-outline-info me-1" title="Iniciar" onclick="return confirm('¿Iniciar producción?')"><i class="bi bi-play-fill"></i></button>
                                        </form>
                                        <form action="{{ route('productions.update-status', $prod) }}" method="POST" class="d-inline me-1">
                                            @csrf
                                            <input type="hidden" name="status" value="cancelled">
                                            <button class="btn btn-sm btn-outline-warning" title="Cancelar" onclick="return confirm('¿Cancelar producción?')"><i class="bi bi-slash-circle"></i></button>
                                        </form>
                                    @elseif($prod->status === 'in_progress')
                                        <form action="{{ route('productions.update-status', $prod) }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="status" value="completed">
                                            <button class="btn btn-sm btn-outline-success me-1" title="Completar" onclick="return confirm('¿Completar producción? Se consumirán materias primas y se agregará al inventario.')"><i class="bi bi-check-lg"></i></button>
                                        </form>
                                        <form action="{{ route('productions.update-status', $prod) }}" method="POST" class="d-inline me-1">
                                            @csrf
                                            <input type="hidden" name="status" value="cancelled">
                                            <button class="btn btn-sm btn-outline-warning" title="Cancelar" onclick="return confirm('¿Cancelar producción?')"><i class="bi bi-slash-circle"></i></button>
                                        </form>
                                    @endif
                                    @if($prod->status !== 'completed' && $prod->status !== 'cancelled')
                                        <form action="{{ route('productions.destroy', $prod) }}" method="POST" class="d-inline">@csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="return confirm('¿Eliminar producción?')"><i class="bi bi-trash"></i></button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-1 d-block mb-2"></i>No hay producciones registradas</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="mt-4 d-flex justify-content-center">{{ $productions->links() }}</div>
</div>
@endsection
