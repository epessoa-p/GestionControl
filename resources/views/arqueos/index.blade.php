@extends('layouts.app')
@section('title', 'Arqueos')
@section('page')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-0"><i class="bi bi-clipboard-check text-primary me-2"></i>Arqueos</h1>
            <p class="text-muted mb-0 small">Cierres de caja registrados</p>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label form-label-sm fw-semibold">Sucursal</label>
                    <select name="branch_id" class="form-select form-select-sm">
                        <option value="">Todas las sucursales</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label form-label-sm fw-semibold">Mes</label>
                    <input type="month" name="month" class="form-control form-control-sm" value="{{ $month }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label form-label-sm fw-semibold">Buscar caja</label>
                    <input type="text" name="search" class="form-control form-control-sm"
                           value="{{ $search }}" placeholder="Nombre de la caja...">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-sm btn-primary w-100">
                        <i class="bi bi-funnel me-1"></i> Filtrar
                    </button>
                </div>
                @if($branchId || $month || $search)
                <div class="col-md-1">
                    <a href="{{ route('arqueos.index') }}" class="btn btn-sm btn-outline-secondary w-100">Limpiar</a>
                </div>
                @endif
            </div>
        </div>
    </form>

    {{-- Table --}}
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Caja</th>
                        <th>Sucursal</th>
                        <th>Cajero</th>
                        <th>Apertura</th>
                        <th>Cierre</th>
                        <th class="text-end">Saldo inicial</th>
                        <th class="text-end">Esperado</th>
                        <th class="text-end">Contado</th>
                        <th class="text-end">Diferencia</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($arqueos as $session)
                    @php
                        $diff = (float)($session->difference ?? 0);
                        $diffClass = $diff == 0 ? 'text-success' : ($diff < 0 ? 'text-danger' : 'text-warning');
                        $diffIcon  = $diff == 0 ? 'check-circle' : ($diff < 0 ? 'exclamation-triangle' : 'info-circle');
                    @endphp
                    <tr>
                        <td>
                            <div class="fw-semibold small">{{ $session->cashRegister?->name ?? '—' }}</div>
                            @if($session->cashRegister?->code)
                            <div class="text-muted" style="font-size:.7rem">{{ $session->cashRegister->code }}</div>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $session->cashRegister?->branch?->name ?? '—' }}</td>
                        <td class="text-muted small">{{ $session->openedBy?->name ?? '—' }}</td>
                        <td class="text-muted small">{{ $session->opened_at?->format('d/m/Y H:i') }}</td>
                        <td class="text-muted small">{{ $session->closed_at?->format('d/m/Y H:i') }}</td>
                        <td class="text-end small">${{ number_format($session->opening_amount, 2) }}</td>
                        <td class="text-end small">${{ number_format($session->expected_amount, 2) }}</td>
                        <td class="text-end small">${{ number_format($session->closing_amount, 2) }}</td>
                        <td class="text-end">
                            <span class="badge {{ $diff == 0 ? 'bg-success-subtle text-success' : ($diff < 0 ? 'bg-danger-subtle text-danger' : 'bg-warning-subtle text-warning') }}">
                                <i class="bi bi-{{ $diffIcon }} me-1"></i>
                                {{ $diff >= 0 ? '+' : '' }}${{ number_format($diff, 2) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('cash-sessions.show', $session) }}"
                               class="btn btn-sm btn-outline-secondary" title="Ver detalle">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-5">
                            <i class="bi bi-clipboard-check fs-1 d-block mb-2 opacity-25"></i>
                            No hay arqueos en el período seleccionado.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($arqueos->hasPages())
        <div class="card-footer bg-white border-top px-4 py-2">
            {{ $arqueos->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
