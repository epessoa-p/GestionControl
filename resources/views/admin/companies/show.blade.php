@extends('layouts.app')

@section('page')
<h1><i class="bi bi-building"></i> {{ $company->name }}</h1>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Información</h5>
            </div>
            <div class="card-body">
                <p><strong>RUC:</strong> {{ $company->ruc ?? '-' }}</p>
                <p><strong>Email:</strong> {{ $company->email ?? '-' }}</p>
                <p><strong>Teléfono:</strong> {{ $company->phone ?? '-' }}</p>
                <p><strong>Dirección:</strong> {{ $company->address ?? '-' }}</p>
                <p><strong>Estado:</strong> <span class="badge {{ $company->active ? 'bg-success' : 'bg-danger' }}">{{ $company->active ? 'Activo' : 'Inactivo' }}</span></p>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        {{-- Costos indirectos --}}
        @php
            $methodLabels = [
                'manual'       => ['label' => 'Manual',                    'icon' => 'bi-pencil-square',  'color' => 'secondary'],
                'por_unidades' => ['label' => 'Por unidades producidas',   'icon' => 'bi-stack',          'color' => 'primary'],
                'por_orden'    => ['label' => 'Por orden de producción',   'icon' => 'bi-receipt',        'color' => 'info'],
                'tasa_fija'    => ['label' => 'Tasa fija por unidad',      'icon' => 'bi-calculator',     'color' => 'success'],
            ];
            $method = $company->overhead_distribution_method ?? 'manual';
            $meta   = $methodLabels[$method] ?? $methodLabels['manual'];
        @endphp
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-bottom d-flex align-items-center gap-2 py-3">
                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                     style="width:32px;height:32px;flex-shrink:0;">
                    <i class="bi bi-gear-wide text-primary" style="font-size:.85rem;"></i>
                </div>
                <h6 class="fw-bold mb-0">Costos indirectos de producción</h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 p-3 rounded-3 border mb-3"
                     style="background:#f8f9fa;">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-{{ $meta['color'] }} flex-shrink-0"
                         style="width:44px;height:44px;background:var(--bs-{{ $meta['color'] }}-bg,#e9ecef);">
                        <i class="bi {{ $meta['icon'] }} fs-5"></i>
                    </div>
                    <div>
                        <div class="fw-semibold">{{ $meta['label'] }}</div>
                        <small class="text-muted">Método de distribución configurado</small>
                    </div>
                    <span class="ms-auto badge bg-{{ $meta['color'] }} bg-opacity-15 text-{{ $meta['color'] }}">
                        Activo
                    </span>
                </div>

                @if($method === 'tasa_fija')
                    <div class="d-flex align-items-center justify-content-between px-1">
                        <span class="text-muted small"><i class="bi bi-calculator me-1"></i>Tasa fija por unidad</span>
                        <span class="fw-bold text-success fs-5">
                            ${{ number_format($company->overhead_fixed_rate ?? 0, 4) }}
                            <small class="text-muted fw-normal fs-6">/unidad</small>
                        </span>
                    </div>
                @elseif($method === 'manual')
                    <p class="text-muted small mb-0"><i class="bi bi-info-circle me-1"></i>El monto se ingresa manualmente en cada orden de producción.</p>
                @elseif($method === 'por_unidades')
                    <p class="text-muted small mb-0"><i class="bi bi-info-circle me-1"></i>El overhead del período se distribuye proporcional a las unidades producidas por cada orden.</p>
                @elseif($method === 'por_orden')
                    <p class="text-muted small mb-0"><i class="bi bi-info-circle me-1"></i>El overhead del período se divide en partes iguales entre todas las órdenes activas.</p>
                @endif
            </div>
        </div>

        {{-- Acciones --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom py-3">
                <h6 class="fw-bold mb-0">Acciones</h6>
            </div>
            <div class="card-body d-flex flex-column gap-2">
                <a href="{{ route('companies.edit', $company) }}" class="btn btn-warning">
                    <i class="bi bi-pencil me-1"></i> Editar empresa
                </a>
                <form action="{{ route('companies.destroy', $company) }}" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger w-100"
                            onclick="return confirm('¿Eliminar empresa {{ addslashes($company->name) }}?')">
                        <i class="bi bi-trash me-1"></i> Eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0">Usuarios de la Empresa</h5>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->pivot->role_id ? \App\Models\Role::find($user->pivot->role_id)->name : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted">No hay usuarios en esta empresa</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<a href="{{ route('companies.index') }}" class="btn btn-secondary mt-4">
    <i class="bi bi-arrow-left"></i> Volver
</a>
@endsection
