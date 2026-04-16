@extends('layouts.app')

@section('title', 'Seguimientos')

@section('page')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h1 class="mb-1"><i class="bi bi-clipboard-check text-primary me-2"></i>Seguimientos</h1><p class="text-muted mb-0">Control y seguimiento de actividades</p></div>
        <a href="{{ route('trackings.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Nuevo</a>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-auto">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Estado</option>
                        @foreach(\App\Models\Tracking::STATUS_LABELS as $val => $label)
                            <option value="{{ $val }}" {{ ($filters['status'] ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <select name="priority" class="form-select form-select-sm">
                        <option value="">Prioridad</option>
                        @foreach(\App\Models\Tracking::PRIORITY_LABELS as $val => $label)
                            <option value="{{ $val }}" {{ ($filters['priority'] ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <select name="type" class="form-select form-select-sm">
                        <option value="">Tipo</option>
                        @foreach(\App\Models\Tracking::TYPE_LABELS as $val => $label)
                            <option value="{{ $val }}" {{ ($filters['type'] ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto"><button class="btn btn-sm btn-dark" type="submit"><i class="bi bi-search"></i></button></div>
                <div class="col-auto"><a href="{{ route('trackings.index') }}" class="btn btn-sm btn-outline-secondary">Limpiar</a></div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Título</th>
                            <th>Tipo</th>
                            <th>Prioridad</th>
                            <th>Estado</th>
                            <th>Asignado a</th>
                            <th>Vencimiento</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($trackings as $tracking)
                            <tr>
                                <td><a href="{{ route('trackings.show', $tracking) }}" class="text-decoration-none fw-semibold">{{ $tracking->title }}</a></td>
                                <td><span class="badge bg-secondary">{{ \App\Models\Tracking::TYPE_LABELS[$tracking->type] ?? $tracking->type }}</span></td>
                                <td><span class="badge bg-{{ \App\Models\Tracking::PRIORITY_COLORS[$tracking->priority] ?? 'secondary' }}">{{ \App\Models\Tracking::PRIORITY_LABELS[$tracking->priority] ?? $tracking->priority }}</span></td>
                                <td><span class="badge bg-{{ \App\Models\Tracking::STATUS_COLORS[$tracking->status] ?? 'secondary' }}">{{ \App\Models\Tracking::STATUS_LABELS[$tracking->status] ?? $tracking->status }}</span></td>
                                <td>{{ $tracking->assignedTo?->name ?? '-' }}</td>
                                <td>{{ $tracking->due_date?->format('d/m/Y') ?? '-' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('trackings.edit', $tracking) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                    <form action="{{ route('trackings.destroy', $tracking) }}" method="POST" class="d-inline">@csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar seguimiento?')"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-1 d-block mb-2"></i>No hay seguimientos registrados</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="mt-4 d-flex justify-content-center">{{ $trackings->links() }}</div>
</div>
@endsection
