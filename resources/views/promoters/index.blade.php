@extends('layouts.app')
@section('title', 'Promotores')
@section('page')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h1 class="mb-1"><i class="bi bi-megaphone text-primary me-2"></i>Promotores</h1><p class="text-muted mb-0">Gestión de promotores y vendedores</p></div>
        <a href="{{ route('promoters.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Nuevo promotor</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nombre</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th>Personal</th>
                            <th class="text-end">Comisión %</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($promoters as $prom)
                            <tr>
                                <td><a href="{{ route('promoters.show', $prom) }}" class="text-decoration-none fw-semibold">{{ $prom->name }}</a></td>
                                <td>{{ $prom->phone ?: '-' }}</td>
                                <td>{{ $prom->email ?: '-' }}</td>
                                <td>{{ $prom->personal?->full_name ?? '-' }}</td>
                                <td class="text-end">{{ number_format($prom->commission_rate, 2) }}%</td>
                                <td>{!! $prom->active ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-secondary">Inactivo</span>' !!}</td>
                                <td class="text-end">
                                    <a href="{{ route('promoters.edit', $prom) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                    <form action="{{ route('promoters.destroy', $prom) }}" method="POST" class="d-inline">@csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar promotor?')"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-1 d-block mb-2"></i>No hay promotores registrados</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="mt-4 d-flex justify-content-center">{{ $promoters->links() }}</div>
</div>
@endsection
