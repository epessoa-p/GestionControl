@extends('layouts.app')

@section('page')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><i class="bi bi-calendar2-week text-primary me-2"></i> Gastos de Período</h1>
            <p class="text-muted mb-0">Registra y distribuye los costos indirectos de producción por período.</p>
        </div>
        <a href="{{ route('overhead-periods.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Nuevo período
        </a>
    </div>

    {{-- Tab-cards de estado --}}
    <div class="d-flex gap-3 mb-4 flex-wrap">
        <a href="{{ route('overhead-periods.index', ['status' => 'abierto']) }}"
           class="tab-card {{ $activeStatus === 'abierto' ? 'tab-active-success' : '' }}">
            <div class="tab-card-icon text-success"><i class="bi bi-unlock"></i></div>
            <div class="tab-card-count">{{ $counts['abierto'] }}</div>
            <div class="tab-card-label">Abiertos</div>
        </a>
        <a href="{{ route('overhead-periods.index', ['status' => 'cerrado']) }}"
           class="tab-card {{ $activeStatus === 'cerrado' ? 'tab-active-secondary' : '' }}">
            <div class="tab-card-icon text-secondary"><i class="bi bi-lock"></i></div>
            <div class="tab-card-count">{{ $counts['cerrado'] }}</div>
            <div class="tab-card-label">Cerrados</div>
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if($periods->count())
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Período</th>
                            <th class="text-center">Rango de fechas</th>
                            <th class="text-end">Total gastos</th>
                            <th class="text-end">Asignado</th>
                            <th class="text-end">Pendiente</th>
                            <th class="text-center">Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($periods as $period)
                        <tr>
                            <td>
                                <a href="{{ route('overhead-periods.show', $period) }}" class="fw-semibold text-decoration-none">
                                    {{ $period->name }}
                                </a>
                                <div class="text-muted small">{{ $period->items->count() ?? 0 }} ítems</div>
                            </td>
                            <td class="text-center small text-muted">
                                {{ $period->period_start->format('d/m/Y') }} — {{ $period->period_end->format('d/m/Y') }}
                            </td>
                            <td class="text-end fw-semibold">${{ number_format($period->total_amount, 2) }}</td>
                            <td class="text-end text-success">${{ number_format($period->allocatedAmount(), 2) }}</td>
                            <td class="text-end text-warning fw-semibold">${{ number_format($period->pendingAmount(), 2) }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ \App\Models\OverheadPeriod::STATUS_COLORS[$period->status] }}-subtle text-{{ \App\Models\OverheadPeriod::STATUS_COLORS[$period->status] }}">
                                    {{ \App\Models\OverheadPeriod::STATUS_LABELS[$period->status] }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('overhead-periods.show', $period) }}" class="btn btn-sm btn-outline-secondary" title="Ver"><i class="bi bi-eye"></i></a>
                                @if($period->status === 'abierto')
                                    <a href="{{ route('overhead-periods.edit', $period) }}" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a>
                                    <form action="{{ route('overhead-periods.destroy', $period) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('¿Eliminar este período? Esta acción no se puede deshacer.')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="bi bi-trash"></i></button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-3 py-2">{{ $periods->links() }}</div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-calendar2-x display-4 text-muted opacity-50"></i>
                <p class="mt-3 text-muted">No hay períodos registrados aún.</p>
                <a href="{{ route('overhead-periods.create') }}" class="btn btn-primary mt-1">
                    <i class="bi bi-plus-lg me-1"></i> Crear primer período
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
