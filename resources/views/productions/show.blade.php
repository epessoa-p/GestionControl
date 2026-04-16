@extends('layouts.app')
@section('title', $production->batch_number)
@section('page')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><i class="bi bi-gear-wide-connected text-secondary me-2"></i>{{ $production->batch_number }}</h1>
            <p class="text-muted mb-0">Orden de producción — {{ $production->production_date?->format('d/m/Y') }}</p>
        </div>
        <div class="d-flex gap-2">
            @if($production->status !== 'completed' && $production->status !== 'cancelled')
                <form action="{{ route('productions.update-status', $production) }}" method="POST" class="d-inline">
                    @csrf
                    @if($production->status === 'planned')
                        <input type="hidden" name="status" value="in_progress">
                        <button class="btn btn-info text-white" onclick="return confirm('¿Iniciar producción?')"><i class="bi bi-play-fill me-1"></i> Iniciar</button>
                    @elseif($production->status === 'in_progress')
                        <input type="hidden" name="status" value="completed">
                        <button class="btn btn-success" onclick="return confirm('¿Completar producción? Se consumirán materias primas y se agregará producto al inventario.')"><i class="bi bi-check-lg me-1"></i> Completar</button>
                    @endif
                </form>
                <form action="{{ route('productions.update-status', $production) }}" method="POST" class="d-inline">
                    @csrf
                    <input type="hidden" name="status" value="cancelled">
                    <button class="btn btn-outline-danger" onclick="return confirm('¿Cancelar producción?')"><i class="bi bi-x-lg"></i></button>
                </form>
            @endif
            <a href="{{ route('productions.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
        </div>
    </div>

    {{-- Status stepper --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-4">
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
                        <div class="text-center px-3">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center {{ $isActive || $isPast ? 'bg-'.$color.' text-white' : 'bg-light text-muted border' }}" style="width:44px;height:44px;">
                                @if($isPast)<i class="bi bi-check-lg"></i>@elseif($isCancelled && $key === 'cancelled')<i class="bi bi-x-lg"></i>@elseif($key === 'planned')<i class="bi bi-clipboard"></i>@elseif($key === 'in_progress')<i class="bi bi-gear"></i>@elseif($key === 'completed')<i class="bi bi-trophy"></i>@else<i class="bi bi-x"></i>@endif
                            </div>
                            <div class="mt-2 small {{ $isActive ? 'fw-bold text-'.$color : 'text-muted' }}">{{ $label }}</div>
                        </div>
                        @if(!$loop->last && !($isCancelled && $key === 'in_progress'))
                            <div class="flex-grow-1 d-flex align-items-center" style="max-width:100px;"><hr class="w-100 {{ $isPast ? 'border-success' : 'border-secondary' }}" style="border-width:2px;opacity:.4;"></div>
                        @endif
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    {{-- KPI cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary text-white" style="width:48px;height:48px;flex-shrink:0;"><i class="bi bi-box-seam fs-5"></i></div>
                    <div>
                        <small class="text-muted d-block">Producto</small>
                        <strong>{{ $production->product?->name }}</strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-info text-white" style="width:48px;height:48px;flex-shrink:0;"><i class="bi bi-stack fs-5"></i></div>
                    <div>
                        <small class="text-muted d-block">Cantidad</small>
                        <strong>{{ number_format($production->quantity_produced, 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-warning text-white" style="width:48px;height:48px;flex-shrink:0;"><i class="bi bi-currency-dollar fs-5"></i></div>
                    <div>
                        <small class="text-muted d-block">Costo total</small>
                        <strong>${{ number_format($production->total_cost, 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-secondary text-white" style="width:48px;height:48px;flex-shrink:0;"><i class="bi bi-person me-0 fs-5"></i></div>
                    <div>
                        <small class="text-muted d-block">Creado por</small>
                        <strong>{{ $production->createdBy?->name }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($production->notes)
        <div class="alert alert-light border-0 shadow-sm mb-4">
            <i class="bi bi-chat-text me-2 text-primary"></i><strong>Notas:</strong> {{ $production->notes }}
        </div>
    @endif

    <div class="row g-4">
        {{-- Materials --}}
        <div class="col-lg-{{ $production->costs->count() ? '6' : '12' }}">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold mb-0"><i class="bi bi-box2 me-2 text-primary"></i>Materias primas <span class="badge bg-primary bg-opacity-10 text-primary ms-1">{{ $production->materials->count() }}</span></h5>
                </div>
                <div class="card-body p-0 pt-3">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr><th class="ps-4">Material</th><th class="text-end">Cantidad</th><th class="text-end">Costo unit.</th><th class="text-end pe-4">Total</th></tr>
                            </thead>
                            <tbody>
                                @forelse($production->materials as $m)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-semibold">{{ $m->product?->name }}</div>
                                            <small class="text-muted">{{ $m->product?->sku }}</small>
                                        </td>
                                        <td class="text-end">{{ number_format($m->quantity_used, 2) }}</td>
                                        <td class="text-end">${{ number_format($m->unit_cost, 2) }}</td>
                                        <td class="text-end pe-4 fw-semibold">${{ number_format($m->total_cost, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-4">Sin materias primas.</td></tr>
                                @endforelse
                            </tbody>
                            @if($production->materials->count())
                                <tfoot><tr class="table-light"><th class="ps-4" colspan="3">Total materiales</th><th class="text-end pe-4">${{ number_format($production->materials->sum('total_cost'), 2) }}</th></tr></tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Additional costs --}}
        @if($production->costs->count())
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 px-4">
                        <h5 class="fw-bold mb-0"><i class="bi bi-receipt me-2 text-warning"></i>Costos adicionales <span class="badge bg-warning bg-opacity-10 text-warning ms-1">{{ $production->costs->count() }}</span></h5>
                    </div>
                    <div class="card-body p-0 pt-3">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr><th class="ps-4">Concepto</th><th>Tipo</th><th class="text-end pe-4">Monto</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($production->costs as $c)
                                        <tr>
                                            <td class="ps-4">{{ $c->concept }}</td>
                                            <td><span class="badge bg-{{ $c->type === 'direct' ? 'primary' : 'info' }} bg-opacity-10 text-{{ $c->type === 'direct' ? 'primary' : 'info' }}">{{ $c->type === 'direct' ? 'Directo' : 'Indirecto' }}</span></td>
                                            <td class="text-end pe-4 fw-semibold">${{ number_format($c->amount, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot><tr class="table-light"><th class="ps-4" colspan="2">Total costos</th><th class="text-end pe-4">${{ number_format($production->costs->sum('amount'), 2) }}</th></tr></tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
