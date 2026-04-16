@extends('layouts.app')
@section('title', 'Salidas')
@section('page')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h1 class="mb-1"><i class="bi bi-box-arrow-up text-primary me-2"></i>Salidas</h1><p class="text-muted mb-0">Salidas de mercancía de almacenes</p></div>
        <a href="{{ route('departures.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Nueva salida</a>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-auto">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Estado</option>
                        @foreach(\App\Models\Departure::STATUS_LABELS as $val => $label)
                            <option value="{{ $val }}" {{ ($filters['status'] ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <select name="reason" class="form-select form-select-sm">
                        <option value="">Motivo</option>
                        @foreach(\App\Models\Departure::REASON_LABELS as $val => $label)
                            <option value="{{ $val }}" {{ ($filters['reason'] ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
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
                <div class="col-auto"><input type="date" name="from" class="form-control form-control-sm" value="{{ $filters['from'] ?? '' }}"></div>
                <div class="col-auto"><input type="date" name="to" class="form-control form-control-sm" value="{{ $filters['to'] ?? '' }}"></div>
                <div class="col-auto"><button class="btn btn-sm btn-dark" type="submit"><i class="bi bi-search"></i></button></div>
                <div class="col-auto"><a href="{{ route('departures.index') }}" class="btn btn-sm btn-outline-secondary">Limpiar</a></div>
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
                            <th>Motivo</th>
                            <th class="text-end">Total</th>
                            <th>Estado</th>
                            <th>Creado por</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($departures as $dep)
                            <tr>
                                <td><a href="{{ route('departures.show', $dep) }}" class="text-decoration-none fw-semibold">{{ $dep->departure_number }}</a></td>
                                <td>{{ $dep->departure_date?->format('d/m/Y') }}</td>
                                <td>{{ $dep->warehouse?->name }}</td>
                                <td><span class="badge bg-secondary">{{ \App\Models\Departure::REASON_LABELS[$dep->reason] ?? $dep->reason }}</span></td>
                                <td class="text-end">${{ number_format($dep->total, 2) }}</td>
                                <td><span class="badge bg-{{ \App\Models\Departure::STATUS_COLORS[$dep->status] ?? 'secondary' }}">{{ \App\Models\Departure::STATUS_LABELS[$dep->status] ?? $dep->status }}</span></td>
                                <td>{{ $dep->createdBy?->name }}</td>
                                <td class="text-end">
                                    @if($dep->status === 'draft')
                                        <form action="{{ route('departures.confirm', $dep) }}" method="POST" class="d-inline">@csrf
                                            <button class="btn btn-sm btn-outline-success" onclick="return confirm('¿Confirmar salida?')"><i class="bi bi-check-lg"></i></button>
                                        </form>
                                        <form action="{{ route('departures.destroy', $dep) }}" method="POST" class="d-inline">@csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar?')"><i class="bi bi-trash"></i></button>
                                        </form>
                                    @elseif($dep->status === 'confirmed')
                                        <form action="{{ route('departures.cancel', $dep) }}" method="POST" class="d-inline">@csrf
                                            <button class="btn btn-sm btn-outline-warning" onclick="return confirm('¿Anular salida?')"><i class="bi bi-x-lg"></i></button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center py-5 text-muted">No hay salidas registradas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="mt-4 d-flex justify-content-center">{{ $departures->links() }}</div>
</div>
@endsection
