@extends('layouts.app')
@section('title', $promoter->name)
@section('page')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><i class="bi bi-megaphone text-primary me-2"></i>{{ $promoter->name }}</h1>
            <p class="text-muted mb-0">Detalle del promotor</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('promoters.edit', $promoter) }}" class="btn btn-primary"><i class="bi bi-pencil me-1"></i> Editar</a>
            <a href="{{ route('promoters.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
        </div>
    </div>

    {{-- KPI cards --}}
    @php
        $totalComm = $promoter->commissions->sum('amount');
        $pendingComm = $promoter->commissions->where('status', 'pending')->sum('amount');
        $paidComm = $promoter->commissions->where('status', 'paid')->sum('amount');
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center {{ $promoter->active ? 'bg-success' : 'bg-secondary' }} text-white" style="width:48px;height:48px;flex-shrink:0;"><i class="bi bi-{{ $promoter->active ? 'check-circle' : 'dash-circle' }} fs-5"></i></div>
                    <div>
                        <small class="text-muted d-block">Estado</small>
                        <span class="badge bg-{{ $promoter->active ? 'success' : 'secondary' }}">{{ $promoter->active ? 'Activo' : 'Inactivo' }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary bg-opacity-10">
                <div class="card-body text-center py-3">
                    <small class="text-muted d-block">Total comisiones</small>
                    <h4 class="fw-bold text-primary mb-0">${{ number_format($totalComm, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning bg-opacity-10">
                <div class="card-body text-center py-3">
                    <small class="text-muted d-block">Pendientes</small>
                    <h4 class="fw-bold text-warning mb-0">${{ number_format($pendingComm, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success bg-opacity-10">
                <div class="card-body text-center py-3">
                    <small class="text-muted d-block">Pagadas</small>
                    <h4 class="fw-bold text-success mb-0">${{ number_format($paidComm, 2) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Info sidebar --}}
        <div class="col-lg-4">
            {{-- Profile card --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body text-center py-4">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10 mx-auto mb-3" style="width:72px;height:72px;">
                        <i class="bi bi-person-fill text-primary" style="font-size:2rem;"></i>
                    </div>
                    <h5 class="fw-bold mb-1">{{ $promoter->name }}</h5>
                    <p class="text-muted mb-2">{{ $promoter->email ?: 'Sin email' }}</p>
                    <span class="badge bg-info bg-opacity-75 fs-6">{{ number_format($promoter->commission_rate, 2) }}% comisión</span>
                </div>
            </div>

            {{-- Contact info --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold mb-0"><i class="bi bi-info-circle me-2 text-primary"></i>Información</h5>
                </div>
                <div class="card-body px-4 pt-3">
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted"><i class="bi bi-telephone me-1 small"></i> Teléfono</span><span>{{ $promoter->phone ?: '—' }}</span></li>
                        <li class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted"><i class="bi bi-envelope me-1 small"></i> Email</span><span>{{ $promoter->email ?: '—' }}</span></li>
                        <li class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted"><i class="bi bi-person-badge me-1 small"></i> Personal</span><span>{{ $promoter->personal?->full_name ?? 'Sin vincular' }}</span></li>
                        <li class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted"><i class="bi bi-person me-1 small"></i> Creado por</span><span>{{ $promoter->createdBy?->name }}</span></li>
                        <li class="d-flex justify-content-between py-2"><span class="text-muted"><i class="bi bi-calendar me-1 small"></i> Creado</span><span>{{ $promoter->created_at?->format('d/m/Y') }}</span></li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Sales table --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold mb-0"><i class="bi bi-cart3 me-2 text-primary"></i>Últimas ventas</h5>
                </div>
                <div class="card-body p-0 pt-3">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Número</th>
                                    <th>Fecha</th>
                                    <th>Cliente</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-center pe-4">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($promoter->sales as $s)
                                    <tr>
                                        <td class="ps-4"><a href="{{ route('sales.show', $s) }}" class="text-decoration-none fw-semibold">{{ $s->sale_number }}</a></td>
                                        <td>{{ $s->sale_date?->format('d/m/Y') }}</td>
                                        <td>{{ $s->client_name ?: '—' }}</td>
                                        <td class="text-end fw-semibold">${{ number_format($s->total, 2) }}</td>
                                        <td class="text-center pe-4"><span class="badge bg-{{ \App\Models\Sale::STATUS_COLORS[$s->status] ?? 'secondary' }}">{{ \App\Models\Sale::STATUS_LABELS[$s->status] ?? $s->status }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-1 d-block mb-2"></i>Sin ventas registradas</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
