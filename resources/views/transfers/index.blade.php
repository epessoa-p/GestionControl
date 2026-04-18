@extends('layouts.app')
@section('title', 'Traspasos entre Almacenes')
@section('page')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><i class="bi bi-arrow-left-right text-primary me-2"></i>Traspasos</h1>
            <p class="text-muted mb-0">Gestión de traspasos entre almacenes</p>
        </div>
        <a href="{{ route('transfers.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Nuevo traspaso</a>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-auto">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Estado</option>
                        @foreach(\App\Models\WarehouseTransfer::STATUS_LABELS as $val => $label)
                            <option value="{{ $val }}" {{ ($filters['status'] ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto"><input type="date" name="from" class="form-control form-control-sm" value="{{ $filters['from'] ?? '' }}" placeholder="Desde"></div>
                <div class="col-auto"><input type="date" name="to" class="form-control form-control-sm" value="{{ $filters['to'] ?? '' }}" placeholder="Hasta"></div>
                <div class="col-auto"><button class="btn btn-sm btn-dark" type="submit"><i class="bi bi-search"></i></button></div>
                <div class="col-auto"><a href="{{ route('transfers.index') }}" class="btn btn-sm btn-outline-secondary">Limpiar</a></div>
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
                            <th>Fecha</th>
                            <th>Origen</th>
                            <th>Destino</th>
                            <th class="text-center">Ítems</th>
                            <th>Estado</th>
                            <th>Creado por</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transfers as $t)
                            <tr>
                                <td>
                                    <a href="{{ route('transfers.show', $t) }}" class="text-decoration-none fw-semibold">
                                        {{ $t->transfer_number }}
                                    </a>
                                </td>
                                <td>{{ $t->transfer_date?->format('d/m/Y') }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <i class="bi bi-building-add me-1"></i>{{ $t->fromWarehouse?->name ?? '—' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <i class="bi bi-building-add me-1"></i>{{ $t->toWarehouse?->name ?? '—' }}
                                    </span>
                                </td>
                                <td class="text-center"><span class="badge bg-secondary">{{ $t->total_items }}</span></td>
                                <td>
                                    <span class="badge bg-{{ \App\Models\WarehouseTransfer::STATUS_COLORS[$t->status] ?? 'secondary' }}">
                                        {{ \App\Models\WarehouseTransfer::STATUS_LABELS[$t->status] ?? $t->status }}
                                    </span>
                                </td>
                                <td><small class="text-muted">{{ $t->createdBy?->name }}</small></td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('transfers.show', $t) }}" class="btn btn-outline-primary" title="Ver"><i class="bi bi-eye"></i></a>
                                        @if($t->status === 'draft')
                                            <form action="{{ route('transfers.dispatch', $t) }}" method="POST" class="d-inline">@csrf
                                                <button class="btn btn-outline-info" onclick="return confirm('¿Enviar traspaso? Se descontará stock del almacén origen.')" title="Enviar"><i class="bi bi-truck"></i></button>
                                            </form>
                                        @endif
                                        @if($t->status === 'in_transit')
                                            <form action="{{ route('transfers.complete', $t) }}" method="POST" class="d-inline">@csrf
                                                <button class="btn btn-outline-success" onclick="return confirm('¿Recibir traspaso? Se agregará stock al almacén destino.')" title="Recibir"><i class="bi bi-check-lg"></i></button>
                                            </form>
                                        @endif
                                        @if($t->status !== 'completed' && $t->status !== 'cancelled')
                                            <form action="{{ route('transfers.cancel', $t) }}" method="POST" class="d-inline">@csrf
                                                <button class="btn btn-outline-warning" onclick="return confirm('¿Cancelar traspaso?')" title="Cancelar"><i class="bi bi-x-lg"></i></button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-arrow-left-right fs-1 d-block mb-2"></i>
                                    No hay traspasos registrados
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="mt-4 d-flex justify-content-center">{{ $transfers->links() }}</div>
</div>
@endsection
