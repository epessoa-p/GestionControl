@extends('layouts.app')
@section('title', $cashRegister->name)
@section('page')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><i class="bi bi-cash-stack text-primary me-2"></i>{{ $cashRegister->name }}</h1>
            <p class="text-muted mb-0">Detalle e historial de la caja</p>
        </div>
        <div class="d-flex gap-2">
            @if(!$cashRegister->activeSession())
                <a href="{{ route('cash-registers.open-session-form', $cashRegister) }}" class="btn btn-success"><i class="bi bi-unlock me-1"></i> Abrir sesión</a>
            @endif
            <a href="{{ route('cash-registers.edit', $cashRegister) }}" class="btn btn-primary"><i class="bi bi-pencil me-1"></i> Editar</a>
            <a href="{{ route('cash-registers.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
        </div>
    </div>

    {{-- KPI cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center {{ $cashRegister->active ? 'bg-success' : 'bg-secondary' }} text-white" style="width:48px;height:48px;flex-shrink:0;"><i class="bi bi-{{ $cashRegister->active ? 'check-circle' : 'dash-circle' }} fs-5"></i></div>
                    <div>
                        <small class="text-muted d-block">Estado</small>
                        <span class="badge bg-{{ $cashRegister->active ? 'success' : 'secondary' }}">{{ $cashRegister->active ? 'Activa' : 'Inactiva' }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary text-white" style="width:48px;height:48px;flex-shrink:0;"><i class="bi bi-upc-scan fs-5"></i></div>
                    <div>
                        <small class="text-muted d-block">Código</small>
                        <strong>{{ $cashRegister->code ?: '—' }}</strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-info text-white" style="width:48px;height:48px;flex-shrink:0;"><i class="bi bi-building fs-5"></i></div>
                    <div>
                        <small class="text-muted d-block">Sucursal</small>
                        <strong>{{ $cashRegister->branch?->name ?? '—' }}</strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-warning text-white" style="width:48px;height:48px;flex-shrink:0;"><i class="bi bi-clock-history fs-5"></i></div>
                    <div>
                        <small class="text-muted d-block">Sesiones</small>
                        <strong>{{ $sessions->total() }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Info sidebar --}}
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold mb-0"><i class="bi bi-info-circle me-2 text-primary"></i>Información</h5>
                </div>
                <div class="card-body px-4 pt-3">
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted"><i class="bi bi-person me-1 small"></i> Creado por</span><span>{{ $cashRegister->createdBy?->name }}</span></li>
                        <li class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted"><i class="bi bi-calendar me-1 small"></i> Creado</span><span>{{ $cashRegister->created_at?->format('d/m/Y') }}</span></li>
                        <li class="d-flex justify-content-between py-2"><span class="text-muted"><i class="bi bi-arrow-repeat me-1 small"></i> Actualizado</span><span>{{ $cashRegister->updated_at?->diffForHumans() }}</span></li>
                    </ul>
                </div>
            </div>

            @if($cashRegister->activeSession())
                <div class="card border-0 shadow-sm mt-3 border-start border-success border-3">
                    <div class="card-body text-center py-3">
                        <small class="text-muted d-block mb-1">Sesión activa</small>
                        <i class="bi bi-circle-fill text-success small"></i>
                        <span class="fw-semibold ms-1">{{ $cashRegister->activeSession()->personal?->full_name }}</span>
                        <div class="text-muted small mt-1">{{ $cashRegister->activeSession()->opened_at?->diffForHumans() }}</div>
                        <a href="{{ route('cash-sessions.show', $cashRegister->activeSession()) }}" class="btn btn-sm btn-outline-success mt-2"><i class="bi bi-eye me-1"></i> Ver sesión</a>
                    </div>
                </div>
            @endif
        </div>

        {{-- Sessions table --}}
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>Historial de sesiones</h5>
                </div>
                <div class="card-body p-0 pt-3">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Apertura</th>
                                    <th>Personal</th>
                                    <th class="text-end">Monto apertura</th>
                                    <th class="text-end">Monto cierre</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-end pe-4">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sessions as $s)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-semibold">{{ $s->opened_at?->format('d/m/Y') }}</div>
                                            <small class="text-muted">{{ $s->opened_at?->format('H:i') }}</small>
                                        </td>
                                        <td>{{ $s->personal?->full_name }}</td>
                                        <td class="text-end">${{ number_format($s->opening_amount, 2) }}</td>
                                        <td class="text-end">{{ $s->closing_amount !== null ? '$' . number_format($s->closing_amount, 2) : '—' }}</td>
                                        <td class="text-center">
                                            @if($s->isOpen())
                                                <span class="badge bg-success"><i class="bi bi-unlock me-1"></i>Abierta</span>
                                            @else
                                                <span class="badge bg-secondary"><i class="bi bi-lock me-1"></i>Cerrada</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="{{ route('cash-sessions.show', $s) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-1 d-block mb-2"></i>No hay sesiones registradas</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="mt-3 d-flex justify-content-center">{{ $sessions->links() }}</div>
        </div>
    </div>
</div>
@endsection
