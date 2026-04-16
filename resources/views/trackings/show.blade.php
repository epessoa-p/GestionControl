@extends('layouts.app')
@section('title', $tracking->title)
@section('page')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><i class="bi bi-journal-text text-primary me-2"></i>{{ $tracking->title }}</h1>
            <p class="text-muted mb-0">Seguimiento registrado el {{ $tracking->created_at->format('d/m/Y') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('trackings.edit', $tracking) }}" class="btn btn-primary"><i class="bi bi-pencil me-1"></i> Editar</a>
            <form action="{{ route('trackings.destroy', $tracking) }}" method="POST" class="d-inline">@csrf @method('DELETE')
                <button class="btn btn-outline-danger" onclick="return confirm('¿Eliminar seguimiento?')"><i class="bi bi-trash"></i></button>
            </form>
            <a href="{{ route('trackings.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
        </div>
    </div>

    {{-- Status badges row --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;background:var(--bs-primary);color:#fff;flex-shrink:0;"><i class="bi bi-tag fs-5"></i></div>
                    <div>
                        <small class="text-muted d-block">Tipo</small>
                        <span class="badge bg-secondary">{{ \App\Models\Tracking::TYPE_LABELS[$tracking->type] ?? $tracking->type }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;background:var(--bs-{{ \App\Models\Tracking::STATUS_COLORS[$tracking->status] ?? 'secondary' }});color:#fff;flex-shrink:0;"><i class="bi bi-circle-fill fs-5"></i></div>
                    <div>
                        <small class="text-muted d-block">Estado</small>
                        <span class="badge bg-{{ \App\Models\Tracking::STATUS_COLORS[$tracking->status] ?? 'secondary' }}">{{ \App\Models\Tracking::STATUS_LABELS[$tracking->status] ?? $tracking->status }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;background:var(--bs-{{ \App\Models\Tracking::PRIORITY_COLORS[$tracking->priority] ?? 'secondary' }});color:#fff;flex-shrink:0;"><i class="bi bi-flag fs-5"></i></div>
                    <div>
                        <small class="text-muted d-block">Prioridad</small>
                        <span class="badge bg-{{ \App\Models\Tracking::PRIORITY_COLORS[$tracking->priority] ?? 'secondary' }}">{{ \App\Models\Tracking::PRIORITY_LABELS[$tracking->priority] ?? $tracking->priority }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;background:var(--bs-info);color:#fff;flex-shrink:0;"><i class="bi bi-calendar-event fs-5"></i></div>
                    <div>
                        <small class="text-muted d-block">Vencimiento</small>
                        <strong>{{ $tracking->due_date?->format('d/m/Y') ?? 'Sin fecha' }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Main content --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold mb-0"><i class="bi bi-text-paragraph me-2 text-primary"></i>Descripción</h5>
                </div>
                <div class="card-body px-4 pt-3">
                    @if($tracking->description)
                        <div class="bg-light rounded-3 p-3">{!! nl2br(e($tracking->description)) !!}</div>
                    @else
                        <p class="text-muted fst-italic mb-0">Sin descripción.</p>
                    @endif
                </div>
            </div>
        </div>
        {{-- Sidebar --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold mb-0"><i class="bi bi-info-circle me-2 text-primary"></i>Información</h5>
                </div>
                <div class="card-body px-4 pt-3">
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted">Asignado a</span><strong>{{ $tracking->assignedTo?->name ?? 'Sin asignar' }}</strong></li>
                        <li class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted">Empresa</span><span>{{ $tracking->company?->name }}</span></li>
                        <li class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted">Creado por</span><span>{{ $tracking->createdBy?->name }}</span></li>
                        <li class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted">Creado</span><span>{{ $tracking->created_at->format('d/m/Y H:i') }}</span></li>
                        <li class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted">Actualizado</span><span>{{ $tracking->updated_at->format('d/m/Y H:i') }}</span></li>
                        <li class="d-flex justify-content-between py-2"><span class="text-muted">Completado</span><strong>{{ $tracking->completed_at?->format('d/m/Y H:i') ?? '—' }}</strong></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
