@extends('layouts.app')
@section('title', $client->display_name)
@section('page')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="client-show-avatar client-show-avatar--{{ $client->type }}">
                <i class="bi {{ \App\Models\Client::TYPE_ICONS[$client->type] ?? 'bi-person' }}"></i>
            </div>
            <div>
                <h1 class="mb-0">{{ $client->display_name }}</h1>
                <div class="d-flex align-items-center gap-2 mt-1">
                    <span class="text-muted small">{{ $client->client_number }}</span>
                    <span class="badge bg-light border text-muted">{{ \App\Models\Client::TYPE_LABELS[$client->type] }}</span>
                    <span class="badge bg-{{ \App\Models\Client::STATUS_COLORS[$client->status] ?? 'secondary' }}">
                        {{ \App\Models\Client::STATUS_LABELS[$client->status] ?? $client->status }}
                    </span>
                </div>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('crm.clients.edit', $client) }}" class="btn btn-outline-secondary">
                <i class="bi bi-pencil me-1"></i> Editar
            </a>
            <form action="{{ route('crm.clients.destroy', $client) }}" method="POST" class="d-inline">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger"
                        onclick="return confirm('¿Eliminar cliente {{ addslashes($client->display_name) }}?')">
                    <i class="bi bi-trash me-1"></i> Eliminar
                </button>
            </form>
            <a href="{{ route('crm.clients.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Volver
            </a>
        </div>
    </div>

    {{-- KPI cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary" style="width:48px;height:48px;flex-shrink:0;">
                        <i class="bi bi-envelope fs-5"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Correo</small>
                        <strong class="small">{{ $client->email ?: '—' }}</strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success" style="width:48px;height:48px;flex-shrink:0;">
                        <i class="bi bi-telephone fs-5"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Teléfono</small>
                        <strong>{{ $client->phone ?: ($client->mobile ?: '—') }}</strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-info bg-opacity-10 text-info" style="width:48px;height:48px;flex-shrink:0;">
                        <i class="bi bi-geo-alt fs-5"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Ciudad</small>
                        <strong>{{ $client->city ?: '—' }}</strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-warning bg-opacity-10 text-warning" style="width:48px;height:48px;flex-shrink:0;">
                        <i class="bi bi-person-check fs-5"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Asesor</small>
                        <strong>{{ $client->assignedTo?->name ?: 'Sin asignar' }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">

        {{-- Datos del cliente --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold mb-0"><i class="bi bi-person-vcard me-2 text-primary"></i>Información general</h5>
                </div>
                <div class="card-body px-4 pt-3">
                    <ul class="list-unstyled mb-0">
                        @if($client->commercial_name && $client->type === 'empresa')
                            <li class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted small">Razón social</span>
                                <span class="fw-semibold">{{ $client->commercial_name }}</span>
                            </li>
                        @endif
                        @if($client->document_number)
                            <li class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted small">{{ \App\Models\Client::DOCUMENT_LABELS[$client->document_type] ?? 'Documento' }}</span>
                                <span>{{ $client->document_number }}</span>
                            </li>
                        @endif
                        @if($client->mobile)
                            <li class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted small"><i class="bi bi-phone me-1"></i>Móvil</span>
                                <span>{{ $client->mobile }}</span>
                            </li>
                        @endif
                        @if($client->address)
                            <li class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted small"><i class="bi bi-map-pin me-1"></i>Dirección</span>
                                <span class="text-end" style="max-width:60%">{{ $client->address }}</span>
                            </li>
                        @endif
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted small">País / Ciudad</span>
                            <span>{{ implode(', ', array_filter([$client->city, $client->country])) ?: '—' }}</span>
                        </li>
                        @if($client->source)
                            <li class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted small"><i class="bi bi-share me-1"></i>Origen</span>
                                <span>{{ \App\Models\Client::SOURCE_LABELS[$client->source] }}</span>
                            </li>
                        @endif
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted small"><i class="bi bi-calendar me-1"></i>Registrado</span>
                            <span>{{ $client->created_at?->format('d/m/Y') }} por {{ $client->createdBy?->name }}</span>
                        </li>
                        @if($client->notes)
                            <li class="py-2">
                                <span class="text-muted small d-block mb-1"><i class="bi bi-sticky me-1"></i>Notas</span>
                                <p class="mb-0 small">{{ $client->notes }}</p>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        {{-- Contactos adicionales --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-people me-2 text-primary"></i>Personas de contacto
                        <span class="badge bg-primary bg-opacity-10 text-primary ms-1">{{ $client->contacts->count() }}</span>
                    </h5>
                    <a href="{{ route('crm.clients.edit', $client) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-pencil"></i>
                    </a>
                </div>
                <div class="card-body p-0 pt-3">
                    @forelse($client->contacts as $contact)
                        <div class="d-flex align-items-start gap-3 px-4 py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div class="rounded-circle bg-secondary bg-opacity-10 text-secondary d-flex align-items-center justify-content-center fw-bold"
                                 style="width:38px;height:38px;flex-shrink:0;font-size:.85rem;">
                                {{ strtoupper(substr($contact->name, 0, 1)) }}
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold">
                                    {{ $contact->name }}
                                    @if($contact->is_primary)
                                        <span class="badge bg-success bg-opacity-10 text-success ms-1 small">Principal</span>
                                    @endif
                                </div>
                                @if($contact->position)<small class="text-muted d-block">{{ $contact->position }}</small>@endif
                                @if($contact->email)<small class="d-block"><i class="bi bi-envelope text-muted me-1"></i>{{ $contact->email }}</small>@endif
                                @if($contact->phone)<small class="d-block"><i class="bi bi-telephone text-muted me-1"></i>{{ $contact->phone }}</small>@endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted px-4">
                            <i class="bi bi-person-x fs-1 d-block mb-2 opacity-25"></i>
                            <p class="mb-0 small">Sin personas de contacto adicionales.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .client-show-avatar {
        width: 58px; height: 58px; border-radius: 14px;
        display: grid; place-items: center; font-size: 1.6rem; flex-shrink: 0;
    }
    .client-show-avatar--persona_natural { background: #e8f4fd; color: #1a73e8; }
    .client-show-avatar--empresa          { background: #fef3e2; color: #e67e22; }
</style>
@endpush
@endsection
