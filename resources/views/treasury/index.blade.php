@extends('layouts.app')
@section('title', 'Tesorería')
@section('page')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-0"><i class="bi bi-bank text-primary me-2"></i>Tesorería</h1>
            <p class="text-muted mb-0 small">Cuentas bancarias y de efectivo de la empresa</p>
        </div>
        <a href="{{ route('treasury.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Nueva cuenta
        </a>
    </div>

    @if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if(session('error'))<div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    {{-- Balance total banner --}}
    <div class="rounded-3 text-white p-4 mb-4" style="background:linear-gradient(135deg,#1e293b 0%,#334155 100%);">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-white bg-opacity-10 d-flex align-items-center justify-content-center" style="width:56px;height:56px;">
                <i class="bi bi-bank fs-4 text-white"></i>
            </div>
            <div>
                <div class="text-white-50 small">Balance total en tesorería</div>
                <div class="display-6 fw-bold">${{ number_format($totalBalance, 2) }}</div>
            </div>
        </div>
    </div>

    @if($accounts->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-bank fs-1 d-block mb-3 opacity-25"></i>
            <p class="mb-3">No hay cuentas de tesorería registradas.</p>
            <a href="{{ route('treasury.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Crear primera cuenta
            </a>
        </div>
    @else
        <div class="row g-3">
            @foreach($accounts as $account)
            @php $typeColor = \App\Models\TreasuryAccount::TYPE_COLORS[$account->type] ?? '#6b7280'; @endphp
            <div class="col-md-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100" style="border-top: 4px solid {{ $typeColor }} !important;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle d-flex align-items-center justify-content-center"
                                     style="width:40px;height:40px;background:{{ $typeColor }}20;">
                                    <i class="bi bi-{{ \App\Models\TreasuryAccount::TYPE_ICONS[$account->type] }}"
                                       style="color:{{ $typeColor }};font-size:1.1rem;"></i>
                                </div>
                                <div>
                                    <div class="fw-bold">{{ $account->name }}</div>
                                    <div class="text-muted small">{{ \App\Models\TreasuryAccount::TYPE_LABELS[$account->type] }}</div>
                                </div>
                            </div>
                            <span class="badge {{ $account->active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                {{ $account->active ? 'Activa' : 'Inactiva' }}
                            </span>
                        </div>

                        @if($account->type === 'banco' && ($account->bank_name || $account->account_number))
                        <div class="mb-3 p-2 rounded bg-light">
                            @if($account->bank_name)
                            <div class="text-muted small"><i class="bi bi-building me-1"></i>{{ $account->bank_name }}</div>
                            @endif
                            @if($account->account_number)
                            <div class="text-muted small"><i class="bi bi-credit-card me-1"></i>••••{{ substr($account->account_number, -4) }}</div>
                            @endif
                        </div>
                        @endif

                        <div class="mb-4">
                            <div class="text-muted small">Saldo disponible</div>
                            <div class="fs-4 fw-bold" style="color:{{ $typeColor }}">
                                ${{ number_format($account->current_balance, 2) }}
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="{{ route('treasury.show', $account) }}" class="btn btn-sm btn-outline-secondary flex-fill">
                                <i class="bi bi-clock-history me-1"></i> Ver movimientos
                            </a>
                            <a href="{{ route('treasury.edit', $account) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
