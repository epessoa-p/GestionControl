@extends('layouts.app')
@section('title', 'Clientes')
@section('page')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><i class="bi bi-people text-primary me-2"></i>Clientes</h1>
            <p class="text-muted mb-0">Directorio CRM de clientes y prospectos</p>
        </div>
        <a href="{{ route('crm.clients.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Nuevo cliente
        </a>
    </div>

    {{-- Tab-cards de estado --}}
    <div class="row g-2 mb-4">
        @php
            $tabs = [
                'todos'     => ['label' => 'Todos',      'color' => 'dark',      'icon' => 'bi-grid'],
                'activo'    => ['label' => 'Activos',    'color' => 'success',   'icon' => 'bi-check-circle'],
                'prospecto' => ['label' => 'Prospectos', 'color' => 'info',      'icon' => 'bi-stars'],
                'inactivo'  => ['label' => 'Inactivos',  'color' => 'secondary', 'icon' => 'bi-pause-circle'],
                'bloqueado' => ['label' => 'Bloqueados', 'color' => 'danger',    'icon' => 'bi-slash-circle'],
            ];
        @endphp
        @foreach($tabs as $key => $tab)
            <div class="col-6 col-md">
                <a href="{{ route('crm.clients.index', array_merge(request()->except(['status','page']), ['status' => $key])) }}"
                   class="card border-2 text-decoration-none h-100 {{ $activeStatus === $key ? 'border-'.$tab['color'].' shadow-sm' : 'border-transparent' }}">
                    <div class="card-body py-3 px-3">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi {{ $tab['icon'] }} text-{{ $tab['color'] }}"></i>
                            <span class="small fw-semibold text-dark">{{ $tab['label'] }}</span>
                        </div>
                        <div class="fw-bold fs-5 mt-1 text-{{ $tab['color'] }}">{{ number_format($counts[$key]) }}</div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    {{-- Filtros --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <input type="hidden" name="status" value="{{ $activeStatus }}">
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" class="form-control" placeholder="Nombre, documento, email, teléfono..." value="{{ $search }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="assigned_to" class="form-select form-select-sm">
                        <option value="">Todos los asesores</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ (string)$assignedTo === (string)$u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button class="btn btn-sm btn-dark" type="submit"><i class="bi bi-funnel"></i> Filtrar</button>
                </div>
                <div class="col-auto">
                    <a href="{{ route('crm.clients.index') }}" class="btn btn-sm btn-outline-secondary">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Nº</th>
                            <th>Cliente</th>
                            <th>Documento</th>
                            <th>Contacto</th>
                            <th>Origen</th>
                            <th>Asesor</th>
                            <th>Estado</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clients as $client)
                            <tr>
                                <td class="ps-4">
                                    <span class="text-muted small">{{ $client->client_number }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="client-avatar client-avatar--{{ $client->type }}">
                                            <i class="bi {{ \App\Models\Client::TYPE_ICONS[$client->type] ?? 'bi-person' }}"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $client->display_name }}</div>
                                            @if($client->type === 'empresa' && $client->name !== $client->commercial_name)
                                                <small class="text-muted">{{ $client->name }}</small>
                                            @endif
                                            <small class="d-block text-muted">{{ \App\Models\Client::TYPE_LABELS[$client->type] }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($client->document_number)
                                        <small class="text-muted">{{ \App\Models\Client::DOCUMENT_LABELS[$client->document_type] ?? '' }}</small>
                                        <div class="small">{{ $client->document_number }}</div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($client->email)
                                        <div class="small"><i class="bi bi-envelope text-muted me-1"></i>{{ $client->email }}</div>
                                    @endif
                                    @if($client->phone || $client->mobile)
                                        <div class="small"><i class="bi bi-telephone text-muted me-1"></i>{{ $client->phone ?: $client->mobile }}</div>
                                    @endif
                                    @if(!$client->email && !$client->phone && !$client->mobile)
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($client->source)
                                        <span class="badge bg-light text-muted border">{{ \App\Models\Client::SOURCE_LABELS[$client->source] ?? $client->source }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $client->assignedTo?->name ?? '—' }}
                                </td>
                                <td>
                                    <span class="badge bg-{{ \App\Models\Client::STATUS_COLORS[$client->status] ?? 'secondary' }}">
                                        {{ \App\Models\Client::STATUS_LABELS[$client->status] ?? $client->status }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('crm.clients.show', $client) }}" class="btn btn-sm btn-outline-primary me-1" title="Ver detalle">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('crm.clients.edit', $client) }}" class="btn btn-sm btn-outline-secondary me-1" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('crm.clients.destroy', $client) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="Eliminar"
                                                onclick="return confirm('¿Eliminar cliente {{ addslashes($client->display_name) }}?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-people fs-1 d-block mb-2 opacity-25"></i>
                                    No se encontraron clientes
                                    @if($search)
                                        para "<strong>{{ $search }}</strong>"
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4 d-flex justify-content-center">{{ $clients->links() }}</div>
</div>

@push('styles')
<style>
    .client-avatar {
        width: 36px; height: 36px; border-radius: 9px;
        display: grid; place-items: center; flex-shrink: 0; font-size: 1rem;
    }
    .client-avatar--persona_natural { background: #e8f4fd; color: #1a73e8; }
    .client-avatar--empresa          { background: #fef3e2; color: #e67e22; }
    .border-transparent { border-color: transparent !important; }
</style>
@endpush
@endsection
