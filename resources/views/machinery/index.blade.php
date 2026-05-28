@extends('layouts.app')

@section('page')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><i class="bi bi-tools text-primary me-2"></i> Maquinaria</h1>
            <p class="text-muted mb-0">Activos productivos con depreciación mensual automática.</p>
        </div>
        <a href="{{ route('machinery.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Nueva máquina
        </a>
    </div>

    {{-- Tab-cards de estado --}}
    <div class="d-flex gap-3 mb-4 flex-wrap">
        <a href="{{ route('machinery.index', ['active' => '1']) }}"
           class="tab-card {{ $activeFilter === '1' ? 'tab-active-success' : '' }}">
            <div class="tab-card-icon text-success"><i class="bi bi-check-circle"></i></div>
            <div class="tab-card-count">{{ $counts['activas'] }}</div>
            <div class="tab-card-label">Activas</div>
        </a>
        <a href="{{ route('machinery.index', ['active' => '0']) }}"
           class="tab-card {{ $activeFilter === '0' ? 'tab-active-secondary' : '' }}">
            <div class="tab-card-icon text-secondary"><i class="bi bi-x-circle"></i></div>
            <div class="tab-card-count">{{ $counts['inactivas'] }}</div>
            <div class="tab-card-label">Inactivas</div>
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if($machines->count())
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nombre</th>
                            <th class="text-end">Costo</th>
                            <th class="text-center">Vida útil</th>
                            <th class="text-end">Depr. mensual</th>
                            <th class="text-center">Meses restantes</th>
                            <th class="text-center">Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($machines as $machine)
                        <tr>
                            <td>
                                <a href="{{ route('machinery.show', $machine) }}" class="fw-semibold text-decoration-none">
                                    {{ $machine->name }}
                                </a>
                                @if($machine->description)
                                    <div class="text-muted small text-truncate" style="max-width:220px">{{ $machine->description }}</div>
                                @endif
                            </td>
                            <td class="text-end">${{ number_format($machine->cost, 2) }}</td>
                            <td class="text-center">{{ $machine->useful_life_months }} meses</td>
                            <td class="text-end text-success fw-semibold">${{ number_format($machine->monthlyDepreciation(), 2) }}</td>
                            <td class="text-center">
                                @php $rem = $machine->remainingMonths(); @endphp
                                <span class="badge bg-{{ $rem > 6 ? 'success' : ($rem > 0 ? 'warning' : 'danger') }}-subtle text-{{ $rem > 6 ? 'success' : ($rem > 0 ? 'warning' : 'danger') }}">
                                    {{ $rem }} meses
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $machine->active ? 'success' : 'secondary' }}-subtle text-{{ $machine->active ? 'success' : 'secondary' }}">
                                    {{ $machine->active ? 'Activa' : 'Inactiva' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('machinery.show', $machine) }}" class="btn btn-sm btn-outline-secondary" title="Ver"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('machinery.edit', $machine) }}" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a>
                                <form action="{{ route('machinery.destroy', $machine) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('¿Eliminar esta maquinaria?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-3 py-2">{{ $machines->links() }}</div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-tools display-4 text-muted opacity-50"></i>
                <p class="mt-3 text-muted">No hay maquinaria registrada aún.</p>
                <a href="{{ route('machinery.create') }}" class="btn btn-primary mt-1">
                    <i class="bi bi-plus-lg me-1"></i> Registrar máquina
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
.tab-card {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    min-width: 130px; padding: 14px 20px; border-radius: 10px;
    border: 2px solid #e5e7eb; background: #fff; text-decoration: none;
    color: inherit; transition: all .18s ease; cursor: pointer;
}
.tab-card:hover { border-color: #c5c5c5; box-shadow: 0 2px 8px rgba(0,0,0,.06); color: inherit; }
.tab-card-icon { font-size: 1.3rem; margin-bottom: 4px; }
.tab-card-count { font-size: 1.5rem; font-weight: 700; line-height: 1.2; color: #1a1a2e; }
.tab-card-label { font-size: .78rem; color: #6c757d; margin-top: 2px; font-weight: 500; }
.tab-active-success { border-color: #198754; box-shadow: 0 2px 10px rgba(25,135,84,.15); }
.tab-active-secondary { border-color: #6c757d; box-shadow: 0 2px 10px rgba(108,117,125,.15); }
</style>
@endpush
@endsection
