@extends('layouts.app')
@section('title', 'Entradas')
@section('page')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h1 class="mb-1"><i class="bi bi-box-arrow-in-down text-primary me-2"></i>Entradas</h1><p class="text-muted mb-0">Entradas de mercancía a almacenes</p></div>
        <a href="{{ route('entries.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Nueva entrada</a>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-auto">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Estado</option>
                        @foreach(\App\Models\Entry::STATUS_LABELS as $val => $label)
                            <option value="{{ $val }}" {{ ($filters['status'] ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <select name="warehouse_id" class="form-select form-select-sm">
                        <option value="">Almacen</option>
                        @foreach($warehouses as $w)
                            <option value="{{ $w->id }}" {{ ($filters['warehouse_id'] ?? '') == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto"><input type="date" name="from" class="form-control form-control-sm" value="{{ $filters['from'] ?? '' }}" placeholder="Desde"></div>
                <div class="col-auto"><input type="date" name="to" class="form-control form-control-sm" value="{{ $filters['to'] ?? '' }}" placeholder="Hasta"></div>
                <div class="col-auto"><button class="btn btn-sm btn-dark" type="submit"><i class="bi bi-search"></i></button></div>
                <div class="col-auto"><a href="{{ route('entries.index') }}" class="btn btn-sm btn-outline-secondary">Limpiar</a></div>
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
                            <th>Almacen</th>
                            <th>Proveedor</th>
                            <th class="text-end">Total</th>
                            <th>Estado</th>
                            <th>Creado por</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($entries as $entry)
                            <tr>
                                <td><a href="{{ route('entries.show', $entry) }}" class="text-decoration-none fw-semibold">{{ $entry->entry_number }}</a></td>
                                <td>{{ $entry->entry_date?->format('d/m/Y') }}</td>
                                <td>{{ $entry->warehouse?->name }}</td>
                                <td>{{ $entry->supplier ?: '-' }}</td>
                                <td class="text-end">${{ number_format($entry->total, 2) }}</td>
                                <td><span class="badge bg-{{ \App\Models\Entry::STATUS_COLORS[$entry->status] ?? 'secondary' }}">{{ \App\Models\Entry::STATUS_LABELS[$entry->status] ?? $entry->status }}</span></td>
                                <td>{{ $entry->createdBy?->name }}</td>
                                <td class="text-end">
                                    @if($entry->status === 'draft')
                                        <form action="{{ route('entries.confirm', $entry) }}" method="POST" class="d-inline">@csrf
                                            <button class="btn btn-sm btn-outline-success" onclick="return confirm('¿Confirmar entrada?')"><i class="bi bi-check-lg"></i></button>
                                        </form>
                                        <form action="{{ route('entries.destroy', $entry) }}" method="POST" class="d-inline">@csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar?')"><i class="bi bi-trash"></i></button>
                                        </form>
                                    @elseif($entry->status === 'confirmed')
                                        <form action="{{ route('entries.cancel', $entry) }}" method="POST" class="d-inline">@csrf
                                            <button class="btn btn-sm btn-outline-warning" onclick="return confirm('¿Anular entrada? Se revertira el inventario.')"><i class="bi bi-x-lg"></i></button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-1 d-block mb-2"></i>No hay entradas registradas</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="mt-4 d-flex justify-content-center">{{ $entries->links() }}</div>
</div>
@endsection
