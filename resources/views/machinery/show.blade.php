@extends('layouts.app')

@section('page')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><i class="bi bi-tools text-primary me-2"></i> {{ $machine->name }}</h1>
            <p class="text-muted mb-0">Comprada el {{ $machine->purchase_date->format('d/m/Y') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('machinery.edit', $machine) }}" class="btn btn-outline-primary">
                <i class="bi bi-pencil me-1"></i> Editar
            </a>
            <a href="{{ route('machinery.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Volver
            </a>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-primary bg-opacity-10 text-primary fs-4"><i class="bi bi-currency-dollar"></i></div>
                    <div>
                        <div class="text-muted small">Costo de adquisición</div>
                        <div class="fs-5 fw-bold">${{ number_format($machine->cost, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-success bg-opacity-10 text-success fs-4"><i class="bi bi-graph-down-arrow"></i></div>
                    <div>
                        <div class="text-muted small">Depreciación mensual</div>
                        <div class="fs-5 fw-bold">${{ number_format($machine->monthlyDepreciation(), 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-warning bg-opacity-10 text-warning fs-4"><i class="bi bi-hourglass-split"></i></div>
                    <div>
                        <div class="text-muted small">Meses restantes</div>
                        <div class="fs-5 fw-bold">{{ $machine->remainingMonths() }} / {{ $machine->useful_life_months }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-danger bg-opacity-10 text-danger fs-4"><i class="bi bi-bar-chart-steps"></i></div>
                    <div>
                        <div class="text-muted small">Depreciación acumulada</div>
                        <div class="fs-5 fw-bold">${{ number_format($machine->accumulatedDepreciation(), 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Descripción --}}
        @if($machine->description)
        <div class="col-md-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-info-circle me-1"></i> Descripción</h6>
                    <p class="mb-0 text-muted">{{ $machine->description }}</p>
                    <div class="mt-3 small text-muted">
                        Estado: <span class="badge bg-{{ $machine->active ? 'success' : 'secondary' }}">{{ $machine->active ? 'Activa' : 'Inactiva' }}</span>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Proyección anual de depreciación --}}
        <div class="col-md-{{ $machine->description ? '7' : '12' }}">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-table me-1"></i> Proyección de depreciación (próximos 12 meses)</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Mes</th>
                                    <th class="text-end">Depreciación</th>
                                    <th class="text-end">Valor libro</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $monthly  = $machine->monthlyDepreciation();
                                    $elapsed  = (int) $machine->purchase_date->diffInMonths(now());
                                    $bookValue = max(0, (float) $machine->cost - $machine->accumulatedDepreciation());
                                @endphp
                                @for($i = 1; $i <= 12; $i++)
                                    @php
                                        $monthNum  = $elapsed + $i;
                                        $active    = $monthNum <= $machine->useful_life_months;
                                        $depr      = $active ? $monthly : 0;
                                        $bookValue = max(0, $bookValue - $depr);
                                        $date      = now()->addMonths($i)->format('M Y');
                                    @endphp
                                    <tr class="{{ !$active ? 'text-muted' : '' }}">
                                        <td>{{ $date }}</td>
                                        <td class="text-end {{ $active ? 'text-danger' : '' }}">${{ number_format($depr, 2) }}</td>
                                        <td class="text-end">${{ number_format($bookValue, 2) }}</td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
