@extends('layouts.app')
@section('title', $production->batch_number)
@section('page')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 fw-bold"><i class="bi bi-gear-wide-connected text-secondary me-2"></i>{{ $production->batch_number }}</h4>
            <p class="text-muted mb-0 small">Orden de producción — {{ $production->production_date?->format('d/m/Y') }}</p>
        </div>
        <div class="d-flex gap-2">
            @if($production->status === 'planned')
                <form action="{{ route('productions.update-status', $production) }}" method="POST" class="d-inline">
                    @csrf
                    <input type="hidden" name="status" value="in_progress">
                    <button class="btn btn-info text-white" onclick="return confirm('¿Iniciar producción?')"><i class="bi bi-play-fill me-1"></i> Iniciar</button>
                </form>
                <form action="{{ route('productions.update-status', $production) }}" method="POST" class="d-inline">
                    @csrf
                    <input type="hidden" name="status" value="cancelled">
                    <button class="btn btn-outline-warning" onclick="return confirm('¿Cancelar producción?')"><i class="bi bi-slash-circle me-1"></i> Cancelar</button>
                </form>
                <form action="{{ route('productions.destroy', $production) }}" method="POST" class="d-inline">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger" onclick="return confirm('¿Eliminar producción?')"><i class="bi bi-trash me-1"></i> Eliminar</button>
                </form>
            @elseif($production->status === 'in_progress')
                <form action="{{ route('productions.update-status', $production) }}" method="POST" class="d-inline">
                    @csrf
                    <input type="hidden" name="status" value="completed">
                    <button class="btn btn-success" onclick="return confirm('¿Completar producción? Se consumirán materias primas y se agregará producto al inventario.')"><i class="bi bi-check-lg me-1"></i> Completar</button>
                </form>
                <form action="{{ route('productions.update-status', $production) }}" method="POST" class="d-inline">
                    @csrf
                    <input type="hidden" name="status" value="cancelled">
                    <button class="btn btn-outline-warning" onclick="return confirm('¿Cancelar producción?')"><i class="bi bi-slash-circle me-1"></i> Cancelar</button>
                </form>
                <form action="{{ route('productions.destroy', $production) }}" method="POST" class="d-inline">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger" onclick="return confirm('¿Eliminar producción?')"><i class="bi bi-trash me-1"></i> Eliminar</button>
                </form>
            @endif
            <a href="{{ route('productions.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
        </div>
    </div>

    {{-- Status stepper --}}
    <div class="card border-0 shadow-sm mb-3 bg-white">
        <div class="card-body py-3">
            <div class="d-flex justify-content-center">
                @php
                    $steps = ['planned' => 'Planificada', 'in_progress' => 'En proceso', 'completed' => 'Completada', 'cancelled' => 'Cancelada'];
                    $statusOrder = ['planned' => 0, 'in_progress' => 1, 'completed' => 2, 'cancelled' => 3];
                    $currentIdx = $statusOrder[$production->status] ?? 0;
                    $isCancelled = $production->status === 'cancelled';
                @endphp
                @foreach($steps as $key => $label)
                    @php
                        $idx = $loop->index;
                        $isActive = $key === $production->status;
                        $isPast = !$isCancelled && $idx < $currentIdx;
                        if ($isCancelled && $key === 'cancelled') $color = 'danger';
                        elseif ($isActive) $color = \App\Models\Production::STATUS_COLORS[$key] ?? 'primary';
                        elseif ($isPast) $color = 'success';
                        else $color = 'secondary';
                    @endphp
                    @if(!($isCancelled && $key === 'completed'))
                        <div class="text-center px-2">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center {{ $isActive || $isPast ? 'bg-'.$color.' text-white' : 'bg-light text-muted border' }}" style="width:34px;height:34px;font-size:.8rem;">
                                @if($isPast)<i class="bi bi-check-lg"></i>@elseif($isCancelled && $key === 'cancelled')<i class="bi bi-x-lg"></i>@elseif($key === 'planned')<i class="bi bi-clipboard"></i>@elseif($key === 'in_progress')<i class="bi bi-gear"></i>@elseif($key === 'completed')<i class="bi bi-trophy"></i>@else<i class="bi bi-x"></i>@endif
                            </div>
                            <div class="mt-1 small {{ $isActive ? 'fw-semibold text-'.$color : 'text-muted' }}" style="font-size:.75rem;">{{ $label }}</div>
                        </div>
                        @if(!$loop->last && !($isCancelled && $key === 'in_progress'))
                            <div class="flex-grow-1 d-flex align-items-center" style="max-width:80px;"><hr class="w-100 {{ $isPast ? 'border-success' : 'border-secondary' }}" style="border-width:2px;opacity:.3;"></div>
                        @endif
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    {{-- KPI cards --}}
    <div class="row g-2 mb-3">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary text-white flex-shrink-0" style="width:36px;height:36px;"><i class="bi bi-box-seam"></i></div>
                    <div>
                        <div class="text-muted" style="font-size:.72rem;">Producto</div>
                        <div class="fw-semibold small lh-sm">{{ $production->product?->name }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-info text-white flex-shrink-0" style="width:36px;height:36px;"><i class="bi bi-stack"></i></div>
                    <div>
                        <div class="text-muted" style="font-size:.72rem;">Cantidad</div>
                        <div class="fw-semibold small">{{ number_format($production->quantity_produced, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-warning text-white flex-shrink-0" style="width:36px;height:36px;"><i class="bi bi-currency-dollar"></i></div>
                    <div>
                        <div class="text-muted" style="font-size:.72rem;">Costo total</div>
                        <div class="fw-semibold small">${{ number_format($production->total_cost, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-2 py-2 px-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-secondary text-white flex-shrink-0" style="width:36px;height:36px;"><i class="bi bi-person"></i></div>
                    <div>
                        <div class="text-muted" style="font-size:.72rem;">Creado por</div>
                        <div class="fw-semibold small">{{ $production->createdBy?->name }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($production->notes)
        <div class="alert alert-light border shadow-sm mb-3 py-2 px-3 small">
            <i class="bi bi-chat-text me-1 text-primary"></i><strong>Notas:</strong> {{ $production->notes }}
        </div>
    @endif

    {{-- Overhead Allocations --}}
    @if($production->overheadAllocations->count() || isset($openPeriod))
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white border-bottom py-2 px-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-semibold mb-0 small text-dark">
                <i class="bi bi-calculator me-1 text-success"></i>Gastos indirectos
                <span class="badge bg-success text-white ms-1">{{ $production->overheadAllocations->count() }}</span>
            </h6>
            @if(isset($openPeriod) && $openPeriod)
                <button type="button" class="btn btn-sm btn-success"
                        data-bs-toggle="modal" data-bs-target="#addOverheadModal">
                    <i class="bi bi-plus-lg me-1"></i> Aplicar overhead
                </button>
            @endif
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 small">Período</th>
                            <th class="small">Método</th>
                            <th class="small">Notas</th>
                            <th class="text-end small">Monto</th>
                            <th style="width:36px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $methodLabels = [
                                'por_unidades' => 'Por unidades',
                                'por_orden'    => 'Por orden',
                                'tasa_fija'    => 'Tasa fija',
                                'manual'       => 'Manual',
                            ];
                        @endphp
                        @forelse($production->overheadAllocations as $oa)
                            <tr>
                                <td class="ps-3">
                                    @if($oa->period)
                                        <span class="small fw-semibold">{{ $oa->period->name }}</span>
                                        <div class="text-muted" style="font-size:.72rem;">{{ $oa->period->period_start?->format('d/m/Y') }} – {{ $oa->period->period_end?->format('d/m/Y') }}</div>
                                    @else
                                        <span class="text-muted small">Sin período</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">{{ $methodLabels[$oa->method] ?? $oa->method }}</span>
                                </td>
                                <td class="text-muted small">{{ $oa->notes ?? '—' }}</td>
                                <td class="text-end fw-semibold small text-success">${{ number_format($oa->amount, 2) }}</td>
                                <td class="pe-2 text-end">
                                    <form action="{{ route('productions.overhead.destroy', [$production, $oa]) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger border-0" title="Eliminar"
                                                onclick="return confirm('¿Eliminar esta asignación de overhead?')">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3 small">
                                    Sin overhead asignado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($production->overheadAllocations->count())
                        <tfoot>
                            <tr class="table-light">
                                <th class="ps-3 small" colspan="3">Total gastos indirectos</th>
                                <th class="text-end small text-success">${{ number_format($production->overheadAllocations->sum('amount'), 2) }}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
    @endif

    <div class="row g-3">
        {{-- Materials --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-2 px-3">
                    <h6 class="fw-semibold mb-0 small text-dark"><i class="bi bi-box2 me-1 text-primary"></i>Materias primas <span class="badge bg-primary text-white ms-1">{{ $production->materials->count() }}</span></h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3 small">Material</th>
                                    <th class="text-end small">Cantidad</th>
                                    <th class="text-end small">Costo unit.</th>
                                    <th class="text-end pe-3 small">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($production->materials as $m)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="small fw-semibold">{{ $m->product?->name }}</div>
                                            <div class="text-muted" style="font-size:.72rem;">{{ $m->product?->sku }}</div>
                                        </td>
                                        <td class="text-end small">{{ number_format($m->quantity_used, 2) }}</td>
                                        <td class="text-end small">${{ number_format($m->unit_cost, 2) }}</td>
                                        <td class="text-end pe-3 small fw-semibold">${{ number_format($m->total_cost, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-3 small">Sin materias primas.</td></tr>
                                @endforelse
                            </tbody>
                            @if($production->materials->count())
                                <tfoot><tr class="table-light"><th class="ps-3 small" colspan="3">Total</th><th class="text-end pe-3 small">${{ number_format($production->materials->sum('total_cost'), 2) }}</th></tr></tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Additional costs --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-2 px-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-semibold mb-0 small text-dark">
                        <i class="bi bi-receipt me-1 text-warning"></i>Costos adicionales
                        <span class="badge bg-warning text-dark ms-1">{{ $production->costs->count() }}</span>
                    </h6>
                    <button type="button" class="btn btn-sm btn-warning text-dark"
                            data-bs-toggle="modal" data-bs-target="#addCostModal">
                        <i class="bi bi-plus-lg me-1"></i> Agregar gasto
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3 small">Concepto</th>
                                    <th class="small">Tipo</th>
                                    <th class="text-end small">Monto</th>
                                    <th style="width:36px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($production->costs as $c)
                                    <tr>
                                        <td class="ps-3 small">{{ $c->concept }}</td>
                                        <td>
                                            <span class="badge bg-{{ $c->type === 'direct' ? 'primary' : 'info' }} bg-opacity-10 text-{{ $c->type === 'direct' ? 'primary' : 'info' }}">
                                                {{ $c->type === 'direct' ? 'Directo' : 'Indirecto' }}
                                            </span>
                                        </td>
                                        <td class="text-end small fw-semibold">${{ number_format($c->amount, 2) }}</td>
                                        <td class="pe-2 text-end">
                                            <form action="{{ route('productions.costs.destroy', [$production, $c]) }}" method="POST" class="d-inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger border-0" title="Eliminar"
                                                        onclick="return confirm('¿Eliminar este gasto?')">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3 small">
                                            Sin gastos adicionales registrados.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if($production->costs->count())
                                <tfoot>
                                    <tr class="table-light">
                                        <th class="ps-3 small" colspan="2">Total</th>
                                        <th class="text-end small">${{ number_format($production->costs->sum('amount'), 2) }}</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Análisis de costo por unidad --}}
    @php
        $qty           = (float) $production->quantity_produced ?: 1;
        $matTotal      = (float) $production->materials->sum('total_cost');
        $costTotal     = (float) $production->costs->sum('amount');
        $overheadTotal = (float) $production->overheadAllocations->sum('amount');
        $grandTotal    = $matTotal + $costTotal + $overheadTotal;
        $unitCost      = $grandTotal > 0 ? $grandTotal / $qty : 0;
    @endphp
    <div class="card border-0 shadow-sm mt-3">
        <div class="card-header bg-white border-bottom py-2 px-3 d-flex align-items-center gap-2">
            <h6 class="fw-semibold mb-0 small text-dark">
                <i class="bi bi-graph-up-arrow me-1 text-primary"></i> Análisis de costo por unidad
            </h6>
            @if($grandTotal <= 0)
                <span class="badge bg-secondary text-white ms-1" style="font-size:.7rem;">Sin datos suficientes</span>
            @endif
        </div>
        <div class="card-body px-3 py-3">
            <div class="row g-3 align-items-center">

                {{-- Desglose en barras --}}
                <div class="col-md-7">
                    @php
                        $components = [
                            ['label' => 'Materias primas',    'total' => $matTotal,      'color' => 'primary',  'icon' => 'bi-box2'],
                            ['label' => 'Costos adicionales', 'total' => $costTotal,     'color' => 'warning',  'icon' => 'bi-receipt'],
                            ['label' => 'Gastos indirectos',  'total' => $overheadTotal, 'color' => 'success',  'icon' => 'bi-calculator'],
                        ];
                    @endphp
                    @foreach($components as $comp)
                    @php
                        $pct     = $grandTotal > 0 ? ($comp['total'] / $grandTotal * 100) : 0;
                        $unitVal = $qty > 0 ? $comp['total'] / $qty : 0;
                    @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small text-muted"><i class="bi {{ $comp['icon'] }} me-1"></i>{{ $comp['label'] }}</span>
                            <div class="d-flex gap-2 align-items-center">
                                <span class="small fw-semibold text-dark">${{ number_format($unitVal, 2) }}<span class="text-muted fw-normal">/u</span></span>
                                <span class="badge rounded-pill" style="font-size:.7rem;background:var(--bs-{{ $comp['color'] }}-bg,#e9ecef);color:var(--bs-{{ $comp['color'] }});">{{ number_format($pct, 1) }}%</span>
                            </div>
                        </div>
                        <div class="progress rounded-pill" style="height:7px;">
                            <div class="progress-bar bg-{{ $comp['color'] }} rounded-pill" style="width:{{ $pct }}%;transition:width .6s ease;"></div>
                        </div>
                    </div>
                    @endforeach
                    <div class="d-flex justify-content-between pt-1 border-top">
                        <span class="small fw-semibold text-muted">Total ({{ number_format($qty, 0) }} u producidas)</span>
                        <span class="small fw-bold text-dark">${{ number_format($grandTotal, 2) }}</span>
                    </div>
                </div>

                {{-- Costo unitario + precio sugerido --}}
                <div class="col-md-5">
                    <div class="rounded-3 border p-3 text-center" style="background:linear-gradient(135deg,#f8f9ff 0%,#f0f4ff 100%);">
                        <div class="text-muted small mb-1">Costo por unidad</div>
                        <div class="fw-bold text-dark mb-0" style="font-size:2rem;" id="unitCostDisplay">
                            ${{ number_format($unitCost, 2) }}
                        </div>
                        <div class="text-muted" style="font-size:.72rem;">incluye todos los componentes</div>

                        <hr class="my-2">

                        <div class="text-muted small mb-2">Precio sugerido con margen:</div>
                        <div class="d-flex justify-content-center gap-1 mb-2 flex-wrap">
                            @foreach([10, 20, 30, 50] as $pct)
                                <button class="btn btn-sm btn-outline-secondary markup-btn"
                                        data-pct="{{ $pct }}"
                                        style="font-size:.75rem;padding:3px 10px;border-radius:20px;">
                                    +{{ $pct }}%
                                </button>
                            @endforeach
                        </div>
                        <div class="fw-bold text-primary" id="suggestedPriceDisplay" style="font-size:1.5rem;min-height:2.2rem;">—</div>
                        <div class="text-muted" id="suggestedMarginNote" style="font-size:.7rem;min-height:1rem;"></div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- Modal: Aplicar overhead de período --}}
@if(isset($openPeriod) && $openPeriod)
@php
    $methodLabelsModal = [
        'por_unidades' => 'Por unidades producidas',
        'por_orden'    => 'Por orden de producción',
        'tasa_fija'    => 'Tasa fija por unidad',
        'manual'       => 'Manual',
    ];
@endphp
<div class="modal fade" id="addOverheadModal" tabindex="-1" aria-labelledby="addOverheadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('productions.overhead.store', $production) }}" method="POST">
                @csrf
                <input type="hidden" name="overhead_period_id" value="{{ $openPeriod->id }}">
                <input type="hidden" name="method" value="{{ $distributionMethod }}">
                <div class="modal-header border-0 pb-0">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-success bg-opacity-15 d-flex align-items-center justify-content-center"
                             style="width:42px;height:42px;flex-shrink:0;">
                            <i class="bi bi-calculator text-success fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0" id="addOverheadModalLabel">Aplicar overhead</h5>
                            <small class="text-muted">{{ $openPeriod->name }}</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    <div class="d-flex flex-wrap gap-3 text-muted small mb-3 p-3 rounded-3 border" style="background:#f8f9fa;">
                        <span>Total período: <strong class="text-dark">${{ number_format($openPeriod->total_amount, 2) }}</strong></span>
                        <span>Asignado: <strong class="text-dark">${{ number_format($openPeriod->allocatedAmount(), 2) }}</strong></span>
                        <span>Pendiente: <strong class="text-warning">${{ number_format($openPeriod->pendingAmount(), 2) }}</strong></span>
                        <span>Método: <strong class="text-dark">{{ $methodLabelsModal[$distributionMethod] ?? $distributionMethod }}</strong></span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Monto a aplicar <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="amount" id="overheadModalAmount" class="form-control" required
                                   step="0.01" min="0.01" placeholder="0.00">
                            <button type="button" class="btn btn-outline-secondary" id="calcOverheadModal" title="Calcular sugerido">
                                <i class="bi bi-magic"></i> Sugerir
                            </button>
                        </div>
                        <div class="form-text">Basado en {{ number_format($production->quantity_produced, 2) }} unidades producidas.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Notas</label>
                        <input type="text" name="notes" class="form-control" placeholder="Ej: Enero 2026, depreciación...">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success fw-semibold">
                        <i class="bi bi-check-lg me-1"></i> Aplicar overhead
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Modal: Agregar gasto --}}
<div class="modal fade" id="addCostModal" tabindex="-1" aria-labelledby="addCostModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('productions.costs.store', $production) }}" method="POST">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-warning bg-opacity-15 d-flex align-items-center justify-content-center"
                             style="width:42px;height:42px;flex-shrink:0;">
                            <i class="bi bi-receipt text-warning fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0" id="addCostModalLabel">Agregar gasto / costo indirecto</h5>
                            <small class="text-muted">Producción {{ $production->batch_number }}</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Concepto <span class="text-danger">*</span></label>
                        <input type="text" name="concept" class="form-control" required
                               placeholder="Ej: Electricidad, Mano de obra, Transporte..."
                               autofocus>
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold">Tipo <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="indirect" selected>Indirecto (overhead)</option>
                                <option value="direct">Directo</option>
                            </select>
                            <div class="form-text">Indirecto = overhead de producción.</div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold">Monto <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="amount" class="form-control" required
                                       step="0.01" min="0.01" placeholder="0.00">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning text-dark fw-semibold">
                        <i class="bi bi-plus-lg me-1"></i> Agregar gasto
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Auto-open cost modal on validation errors
    @if($errors->any())
        document.addEventListener('DOMContentLoaded', () => {
            new bootstrap.Modal(document.getElementById('addCostModal')).show();
        });
    @endif

    // Calculadora de margen — precio sugerido por unidad
    (function () {
        const unitCost = {{ $unitCost }};
        let activeBtn  = null;

        document.querySelectorAll('.markup-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const pct   = parseInt(this.dataset.pct);
                const price = unitCost * (1 + pct / 100);
                const gain  = price - unitCost;

                document.getElementById('suggestedPriceDisplay').textContent =
                    '$' + price.toFixed(2);
                document.getElementById('suggestedMarginNote').textContent =
                    'Ganancia estimada: $' + gain.toFixed(2) + ' / u';

                if (activeBtn) {
                    activeBtn.classList.replace('btn-primary', 'btn-outline-secondary');
                }
                this.classList.replace('btn-outline-secondary', 'btn-primary');
                activeBtn = this;
            });
        });
    })();

    // Botón "Sugerir" en modal de overhead
    document.getElementById('calcOverheadModal')?.addEventListener('click', async function () {
        const periodId = {{ $openPeriod->id ?? 'null' }};
        const qty      = {{ $production->quantity_produced }};
        if (!periodId) return;

        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Calculando...';

        try {
            const res  = await fetch(`/productions/suggest-overhead?period_id=${periodId}&production_qty=${qty}`);
            const data = await res.json();
            const amountInput = document.getElementById('overheadModalAmount');
            if (amountInput) amountInput.value = (data.suggested ?? 0).toFixed(2);
        } catch (e) {
            console.error('Error al calcular overhead sugerido:', e);
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-magic"></i> Sugerir';
        }
    });
</script>
@endpush
@endsection
