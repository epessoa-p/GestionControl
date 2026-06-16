@extends('layouts.app')
@section('title', 'Movimientos')
@section('page')
<div class="container-fluid">

    {{-- Header + KPIs históricos --}}
    <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-3">
        <div>
            <h1 class="mb-0"><i class="bi bi-arrow-left-right text-primary me-2"></i>Movimientos</h1>
            <p class="text-muted mb-0 small">Resumen financiero por sucursal y período</p>
        </div>
        {{-- KPIs históricos (sin filtro de fecha) --}}
        <div class="d-flex align-items-stretch gap-0 shadow-sm rounded-3 overflow-hidden border">
            <div class="px-3 py-2 d-flex align-items-center gap-2 bg-white border-end">
                <i class="bi bi-clock-history text-muted"></i>
                <span class="text-muted small fw-semibold">HISTÓRICO</span>
            </div>
            <div class="px-4 py-2 text-center bg-white border-end" style="min-width:120px">
                <div class="text-muted" style="font-size:.65rem;letter-spacing:.05em;font-weight:600">BALANCE</div>
                <div class="fw-bold {{ $historicBalance >= 0 ? '' : 'text-danger' }}">
                    Bs. {{ number_format($historicBalance, 2) }}
                </div>
            </div>
            <div class="px-4 py-2 text-center bg-white border-end" style="min-width:120px">
                <div class="text-muted" style="font-size:.65rem;letter-spacing:.05em;font-weight:600">INGRESOS</div>
                <div class="fw-bold text-success">Bs. {{ number_format($historicIngresos, 2) }}</div>
            </div>
            <div class="px-4 py-2 text-center bg-white" style="min-width:120px">
                <div class="text-muted" style="font-size:.65rem;letter-spacing:.05em;font-weight:600">EGRESOS</div>
                <div class="fw-bold text-danger">Bs. {{ number_format($historicEgresos, 2) }}</div>
            </div>
        </div>
    </div>

    {{-- Branch chips (caja por sucursal) --}}
    <div class="d-flex gap-2 flex-wrap mb-2 align-items-center">
        <span class="text-muted small fw-semibold me-1"><i class="bi bi-shop me-1"></i>Sucursal:</span>
        <a href="{{ request()->fullUrlWithQuery(['branch_id' => null, 'treasury_account_id' => null, 'page' => null]) }}"
           class="btn btn-sm {{ !$branchId && !$treasuryAccountId ? 'btn-dark' : 'btn-outline-secondary' }} rounded-pill">
            <i class="bi bi-grid me-1"></i>Todas
        </a>
        @foreach($branches as $branch)
        <a href="{{ request()->fullUrlWithQuery(['branch_id' => $branch->id, 'treasury_account_id' => null, 'page' => null]) }}"
           class="btn btn-sm {{ $branchId == $branch->id ? 'btn-dark' : 'btn-outline-secondary' }} rounded-pill">
            <span class="me-1" style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#16a34a"></span>
            {{ $branch->name }}
        </a>
        @endforeach
    </div>

    {{-- Treasury account chips (tesorería por cuenta) --}}
    @if($treasuryAccounts->isNotEmpty())
    <div class="d-flex gap-2 flex-wrap mb-2 align-items-center">
        <span class="text-muted small fw-semibold me-1"><i class="bi bi-bank me-1"></i>Tesorería:</span>
        @foreach($treasuryAccounts as $acc)
        <a href="{{ request()->fullUrlWithQuery(['treasury_account_id' => $acc->id, 'branch_id' => null, 'page' => null]) }}"
           class="btn btn-sm {{ $treasuryAccountId == $acc->id ? 'btn-info text-white' : 'btn-outline-info' }} rounded-pill">
            {{ $acc->name }}
        </a>
        @endforeach
    </div>
    @endif

    {{-- Period filter --}}
    <form method="GET" class="mb-3">
        <input type="hidden" name="branch_id" value="{{ $branchId }}">
        <input type="hidden" name="treasury_account_id" value="{{ $treasuryAccountId }}">
        <div class="d-flex gap-2 flex-wrap align-items-center">
            @foreach(['dia' => 'Día', 'semana' => 'Semana', 'mes' => 'Mes', 'todo' => 'Todo', 'rango' => 'Rango'] as $key => $label)
            <a href="{{ request()->fullUrlWithQuery(['period' => $key, 'page' => null]) }}"
               class="btn btn-sm {{ $period === $key ? 'btn-dark' : 'btn-outline-secondary' }}">{{ $label }}</a>
            @endforeach

            @if($period === 'rango')
            <input type="hidden" name="period" value="rango">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}" style="width:140px">
            <span class="text-muted small">al</span>
            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}" style="width:140px">
            <button type="submit" class="btn btn-sm btn-primary">Filtrar</button>
            @else
            <input type="hidden" name="date_from" value="{{ $dateFrom }}">
            <input type="hidden" name="date_to" value="{{ $dateTo }}">
            @endif
        </div>
    </form>

    {{-- Tab principal --}}
    <ul class="nav nav-tabs mb-0" id="mainTabs">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabTransacciones">
                <i class="bi bi-arrow-left-right me-1"></i>Transacciones
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabCierres">
                <i class="bi bi-lock me-1"></i>Cierres de caja
                <span class="badge bg-secondary ms-1">{{ $cashSessions->total() }}</span>
            </button>
        </li>
    </ul>

    <div class="tab-content">

        {{-- ── Tab: Transacciones ──────────────────────────────────── --}}
        <div class="tab-pane fade show active" id="tabTransacciones">
            <div class="card border-0 shadow-sm rounded-top-0">

                {{-- 3 KPI cards del período --}}
                <div class="card-body border-bottom pb-3">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center justify-content-between p-3 rounded-3 border">
                                <div>
                                    <div class="text-muted small">Balance neto del período</div>
                                    <div class="fs-4 fw-bold {{ $balance >= 0 ? '' : 'text-danger' }}">
                                        Bs. {{ number_format($balance, 2) }}
                                    </div>
                                </div>
                                <div class="rounded-2 bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center" style="width:44px;height:44px">
                                    <i class="bi bi-graph-up-arrow text-secondary fs-5"></i>
                                </div>
                                <span class="badge bg-secondary position-absolute" style="top:8px;right:8px;font-size:.6rem">Balance</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center justify-content-between p-3 rounded-3 border border-success border-opacity-25">
                                <div>
                                    <div class="text-muted small">Ingresos totales del período</div>
                                    <div class="fs-4 fw-bold text-success">Bs. {{ number_format($totalIngresos, 2) }}</div>
                                </div>
                                <div class="rounded-2 bg-success bg-opacity-10 d-flex align-items-center justify-content-center" style="width:44px;height:44px">
                                    <i class="bi bi-arrow-down-circle text-success fs-5"></i>
                                </div>
                                <span class="badge bg-success position-absolute" style="top:8px;right:8px;font-size:.6rem">Ingresos</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center justify-content-between p-3 rounded-3 border border-danger border-opacity-25">
                                <div>
                                    <div class="text-muted small">Egresos totales del período</div>
                                    <div class="fs-4 fw-bold text-danger">Bs. {{ number_format($totalEgresos, 2) }}</div>
                                </div>
                                <div class="rounded-2 bg-danger bg-opacity-10 d-flex align-items-center justify-content-center" style="width:44px;height:44px">
                                    <i class="bi bi-arrow-up-circle text-danger fs-5"></i>
                                </div>
                                <span class="badge bg-danger position-absolute" style="top:8px;right:8px;font-size:.6rem">Egresos</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sub-tabs: Ingresos / Egresos / Por cobrar / Por pagar --}}
                @php
                    $subTabs = [
                        'ingresos'   => ['label'=>'Ingresos',   'count'=>$ingresosCount,  'hex'=>'#16a34a'],
                        'egresos'    => ['label'=>'Egresos',    'count'=>$egresosCount,   'hex'=>'#dc2626'],
                        'por_cobrar' => ['label'=>'Por cobrar', 'count'=>$porCobrarCount, 'hex'=>'#d97706'],
                        'por_pagar'  => ['label'=>'Por pagar',  'count'=>$porPagarCount,  'hex'=>'#2563eb'],
                    ];
                @endphp
                <div class="border-bottom px-4 py-2" style="background:#f4f5f7;">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        @foreach($subTabs as $key => $info)
                        @php $isActive = $tab === $key; @endphp
                        <a href="{{ request()->fullUrlWithQuery(['tab' => $key, 'page' => null]) }}"
                           class="btn btn-sm rounded-pill d-inline-flex align-items-center gap-2 fw-semibold border {{ $isActive ? 'text-white shadow-sm' : 'bg-white' }}"
                           style="{{ $isActive
                               ? 'background-color:'.$info['hex'].';border-color:'.$info['hex'].';'
                               : 'color:'.$info['hex'].';border-color:'.$info['hex'].'66;' }}">
                            {{ $info['label'] }}
                            <span class="badge rounded-pill"
                                  style="{{ $isActive
                                      ? 'background:#fff;color:'.$info['hex'].';'
                                      : 'background:'.$info['hex'].';color:#fff;' }}">{{ $info['count'] }}</span>
                        </a>
                        @endforeach
                    </div>
                </div>

                {{-- Lista de movimientos --}}
                @if(in_array($tab, ['por_cobrar', 'por_pagar']))
                <div class="card-body text-center py-5 text-muted">
                    <i class="bi bi-{{ $tab === 'por_cobrar' ? 'cash' : 'credit-card-2-front' }} fs-1 d-block mb-3 opacity-25"></i>
                    @if($tab === 'por_cobrar')
                        <p class="mb-2">Hay <strong>{{ $porCobrarCount }}</strong> cuota(s) pendiente(s) de cobro.</p>
                        <a href="{{ route('sales.index') }}" class="btn btn-sm btn-outline-primary">Ver ventas a crédito →</a>
                    @else
                        <p class="mb-2">Hay <strong>{{ $porPagarCount }}</strong> cuenta(s) por pagar pendiente(s).</p>
                        <a href="{{ route('purchases.payables.index') }}" class="btn btn-sm btn-outline-primary">Ver cuentas por pagar →</a>
                    @endif
                </div>
                @else
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:36px"></th>
                                <th>Concepto</th>
                                <th class="text-end">Valor</th>
                                <th>Medio de pago</th>
                                <th>Origen</th>
                                <th>Fecha y hora</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($movements as $mov)
                            @php
                                $isIncome = $mov->type === 'income';
                                $catLabel = $mov->origin_type === 'tesoreria'
                                    ? (\App\Models\TreasuryMovement::CATEGORIES[$mov->category]['label'] ?? $mov->category)
                                    : (\App\Models\CashMovement::CATEGORIES[$mov->category]['label'] ?? $mov->category);
                                $pmLabel  = $mov->payment_method
                                    ? (\App\Models\CashMovement::PAYMENT_LABELS[$mov->payment_method] ?? $mov->payment_method)
                                    : '—';
                            @endphp
                            <tr>
                                <td>
                                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                                         style="width:30px;height:30px;background:{{ $isIncome ? '#dcfce7' : '#fee2e2' }};">
                                        <i class="bi bi-arrow-{{ $isIncome ? 'down' : 'up' }}-circle"
                                           style="color:{{ $isIncome ? '#16a34a' : '#dc2626' }};font-size:.85rem;"></i>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold small">{{ $mov->concept ?? $catLabel }}</div>
                                    <div class="text-muted" style="font-size:.72rem">{{ $catLabel }}</div>
                                </td>
                                <td class="text-end fw-semibold {{ $isIncome ? 'text-success' : 'text-danger' }}">
                                    {{ $isIncome ? '+' : '-' }}Bs. {{ number_format($mov->amount, 2) }}
                                </td>
                                <td class="text-muted small">{{ $pmLabel }}</td>
                                <td class="text-muted small">
                                    @if($mov->origin_type === 'tesoreria')
                                        <span class="badge bg-info-subtle text-info border border-info-subtle"><i class="bi bi-bank me-1"></i>Tesorería</span>
                                        <div style="font-size:.7rem">{{ $mov->origin_name }}</div>
                                    @else
                                        {{ $mov->origin_name ?? '—' }}
                                        @if($mov->origin_branch)
                                        <div style="font-size:.7rem">{{ $mov->origin_branch }}</div>
                                        @endif
                                    @endif
                                </td>
                                <td class="text-muted small">{{ \Carbon\Carbon::parse($mov->movement_date)->format('d/m/Y H:i') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                                    Sin movimientos en el período seleccionado.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($movements->hasPages())
                <div class="card-footer bg-white border-top px-4 py-2">
                    {{ $movements->links() }}
                </div>
                @endif
                @endif

            </div>
        </div>

        {{-- ── Tab: Cierres de caja ────────────────────────────────── --}}
        <div class="tab-pane fade" id="tabCierres">
            <div class="card border-0 shadow-sm rounded-top-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Estado</th>
                                <th>Caja</th>
                                <th>Sucursal</th>
                                <th>Cajero</th>
                                <th>Apertura</th>
                                <th>Cierre</th>
                                <th class="text-end">Saldo inicial</th>
                                <th class="text-end">Esperado</th>
                                <th class="text-end">Contado / Actual</th>
                                <th class="text-end">Diferencia</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cashSessions as $session)
                            @php
                                $isOpen = $session->status === 'open';
                                $diff   = $isOpen ? null : (float)($session->difference ?? 0);
                            @endphp
                            <tr class="{{ $isOpen ? 'table-warning' : '' }}">
                                <td>
                                    @if($isOpen)
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-unlock-fill me-1"></i>Abierta
                                    </span>
                                    <div class="text-warning-emphasis fw-semibold" style="font-size:.66rem">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i>Cierre pendiente
                                    </div>
                                    @else
                                    <span class="badge bg-secondary-subtle text-secondary border">
                                        <i class="bi bi-lock-fill me-1"></i>Cerrada
                                    </span>
                                    @endif
                                </td>
                                <td class="fw-semibold small">{{ $session->cashRegister?->name ?? '—' }}</td>
                                <td class="text-muted small">{{ $session->cashRegister?->branch?->name ?? '—' }}</td>
                                <td class="text-muted small">{{ $session->openedBy?->name ?? '—' }}</td>
                                <td class="text-muted small">{{ $session->opened_at?->format('d/m/Y H:i') }}</td>
                                <td class="text-muted small">
                                    @if($isOpen)
                                        <span class="text-muted fst-italic">En curso...</span>
                                    @else
                                        {{ $session->closed_at?->format('d/m/Y H:i') }}
                                    @endif
                                </td>
                                <td class="text-end small">Bs. {{ number_format($session->opening_amount, 2) }}</td>
                                <td class="text-end small">Bs. {{ number_format($session->expected_amount, 2) }}</td>
                                <td class="text-end small">
                                    @if($isOpen)
                                        <span class="text-success fw-semibold">Bs. {{ number_format($session->expected_amount, 2) }}</span>
                                        <div style="font-size:.68rem" class="text-muted">calculado</div>
                                    @else
                                        Bs. {{ number_format($session->closing_amount, 2) }}
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($isOpen)
                                        <span class="text-muted fst-italic small">—</span>
                                    @else
                                        <span class="badge {{ $diff == 0 ? 'bg-success-subtle text-success' : ($diff < 0 ? 'bg-danger-subtle text-danger' : 'bg-warning-subtle text-warning') }}">
                                            {{ $diff >= 0 ? '+' : '' }}Bs. {{ number_format($diff, 2) }}
                                        </span>
                                    @endif
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
                                <td colspan="11" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                                    No hay sesiones de caja en el período seleccionado.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($cashSessions->hasPages())
                <div class="card-footer bg-white border-top px-4 py-2">
                    {{ $cashSessions->links() }}
                </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
