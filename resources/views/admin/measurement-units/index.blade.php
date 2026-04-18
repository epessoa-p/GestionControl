@extends('layouts.app')

@section('title', 'Unidades de Medida')

@section('page')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="mb-1"><i class="bi bi-rulers"></i> Unidades de Medida</h1>
            <p class="text-muted mb-0">Administra las unidades que se usan en el catálogo de productos.</p>
        </div>
        <a href="{{ route('measurement-units.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nueva unidad
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Nombre</th>
                            <th>Símbolo</th>
                            <th>Descripción</th>
                            <th class="text-center">Estado</th>
                            <th class="text-end pe-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($measurementUnits as $measurementUnit)
                            <tr>
                                <td class="ps-3 fw-semibold">{{ $measurementUnit->name }}</td>
                                <td><span class="badge bg-info-subtle text-info border border-info-subtle">{{ $measurementUnit->symbol }}</span></td>
                                <td>{{ $measurementUnit->description ?: '-' }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $measurementUnit->active ? 'bg-success' : 'bg-secondary' }}">{{ $measurementUnit->active ? 'Activa' : 'Inactiva' }}</span>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('measurement-units.edit', $measurementUnit) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                    <form action="{{ route('measurement-units.destroy', $measurementUnit) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar unidad {{ addslashes($measurementUnit->name) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">No hay unidades registradas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">{{ $measurementUnits->links('pagination::bootstrap-5') }}</div>
    </div>
</div>
@endsection
