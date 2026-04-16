@extends('layouts.app')
@section('title', 'Cajas')
@section('page')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h1 class="mb-1"><i class="bi bi-cash-stack text-primary me-2"></i>Cajas</h1><p class="text-muted mb-0">Gestión de cajas registradoras</p></div>
        <a href="{{ route('cash-registers.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Nueva caja</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nombre</th>
                            <th>Codigo</th>
                            <th>Sucursal</th>
                            <th>Estado</th>
                            <th>Sesion activa</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cashRegisters as $cr)
                            <tr>
                                <td><a href="{{ route('cash-registers.show', $cr) }}" class="text-decoration-none fw-semibold">{{ $cr->name }}</a></td>
                                <td>{{ $cr->code ?: '-' }}</td>
                                <td>{{ $cr->branch?->name ?? '-' }}</td>
                                <td>
                                    @if($cr->active)
                                        <span class="badge bg-success">Activa</span>
                                    @else
                                        <span class="badge bg-secondary">Inactiva</span>
                                    @endif
                                </td>
                                <td>
                                    @php $activeSession = $cr->activeSession(); @endphp
                                    @if($activeSession)
                                        <a href="{{ route('cash-sessions.show', $activeSession) }}" class="badge bg-info text-decoration-none">Abierta</a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if(!$cr->activeSession())
                                        <a href="{{ route('cash-registers.open-session-form', $cr) }}" class="btn btn-sm btn-outline-success"><i class="bi bi-unlock"></i> Abrir</a>
                                    @endif
                                    <a href="{{ route('cash-registers.edit', $cr) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                    <form action="{{ route('cash-registers.destroy', $cr) }}" method="POST" class="d-inline">@csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar caja?')"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-1 d-block mb-2"></i>No hay cajas registradas</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="mt-4 d-flex justify-content-center">{{ $cashRegisters->links() }}</div>
</div>
@endsection
