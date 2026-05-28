@extends('layouts.app')

@section('page')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">
                <i class="bi bi-calendar2-week text-primary me-2"></i>
                {{ $period->name }}
                <span class="badge bg-{{ \App\Models\OverheadPeriod::STATUS_COLORS[$period->status] }} ms-2 fs-6">
                    {{ \App\Models\OverheadPeriod::STATUS_LABELS[$period->status] }}
                </span>
            </h1>
            <p class="text-muted mb-0">
                {{ $period->period_start->format('d/m/Y') }} — {{ $period->period_end->format('d/m/Y') }}
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            @if($period->status === 'abierto')
                <form action="{{ route('overhead-periods.auto-depreciation', $period) }}" method="POST">
                    @csrf
                    <button class="btn btn-outline-info" type="submit">
                        <i class="bi bi-magic me-1"></i> Auto-cargar depreciaciones
                    </button>
                </form>
                <form action="{{ route('overhead-periods.close', $period) }}" method="POST"
                      onsubmit="return confirm('¿Cerrar el período? El total quedará congelado y no se podrá editar.')">
                    @csrf
                    <button class="btn btn-outline-warning" type="submit">
                        <i class="bi bi-lock me-1"></i> Cerrar período
                    </button>
                </form>
                <a href="{{ route('overhead-periods.edit', $period) }}" class="btn btn-outline-primary">
                    <i class="bi bi-pencil me-1"></i> Editar
                </a>
            @endif
            <a href="{{ route('overhead-periods.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Volver
            </a>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-primary bg-opacity-10 text-primary fs-4"><i class="bi bi-cash-stack"></i></div>
                    <div>
                        <div class="text-muted small">Total gastos</div>
                        <div class="fs-5 fw-bold">${{ number_format($period->total_amount, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-success bg-opacity-10 text-success fs-4"><i class="bi bi-check2-circle"></i></div>
                    <div>
                        <div class="text-muted small">Asignado a producciones</div>
                        <div class="fs-5 fw-bold">${{ number_format($period->allocatedAmount(), 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-warning bg-opacity-10 text-warning fs-4"><i class="bi bi-hourglass-split"></i></div>
                    <div>
                        <div class="text-muted small">Pendiente de asignar</div>
                        <div class="fs-5 fw-bold">${{ number_format($period->pendingAmount(), 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-info bg-opacity-10 text-info fs-4"><i class="bi bi-gear-wide-connected"></i></div>
                    <div>
                        <div class="text-muted small">Producciones del período</div>
                        <div class="fs-5 fw-bold">{{ $productionsCount }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Ítems de gasto por categoría --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-list-ul me-1"></i> Ítems de gasto</h6>

                    @if($period->items->count())
                        @foreach(\App\Models\OverheadItem::CATEGORIES as $cat)
                            @if(isset($itemsByCategory[$cat]) && $itemsByCategory[$cat]->count())
                                <div class="mb-3">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="badge bg-{{ \App\Models\OverheadItem::CATEGORY_COLORS[$cat] }}">
                                            {{ \App\Models\OverheadItem::CATEGORY_LABELS[$cat] }}
                                        </span>
                                        <span class="text-muted small">
                                            ${{ number_format($itemsByCategory[$cat]->sum('amount'), 2) }}
                                        </span>
                                    </div>
                                    <table class="table table-sm mb-0">
                                        <tbody>
                                            @foreach($itemsByCategory[$cat] as $item)
                                            <tr>
                                                <td>
                                                    {{ $item->concept }}
                                                    @if($item->machinery)
                                                        <span class="text-muted small ms-1">({{ $item->machinery->name }})</span>
                                                    @endif
                                                </td>
                                                <td class="text-end fw-semibold">${{ number_format($item->amount, 2) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        @endforeach
                        <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-2">
                            <strong>Total</strong>
                            <strong class="text-primary">${{ number_format($period->total_amount, 2) }}</strong>
                        </div>
                    @else
                        <div class="text-center py-3 text-muted small">
                            <i class="bi bi-inbox me-1"></i> Sin ítems registrados.
                            @if($period->status === 'abierto')
                                <a href="{{ route('overhead-periods.edit', $period) }}">Agregar ítems</a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Asignaciones a producciones --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-gear-wide-connected me-1"></i> Asignaciones realizadas</h6>
                    @if($period->allocations->count())
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Producción</th>
                                        <th>Método</th>
                                        <th class="text-end">Monto</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($period->allocations as $alloc)
                                    <tr>
                                        <td>
                                            @if($alloc->production)
                                                <a href="{{ route('productions.show', $alloc->production) }}" class="text-decoration-none small">
                                                    {{ $alloc->production->batch_number }}
                                                </a>
                                            @else
                                                <span class="text-muted small">—</span>
                                            @endif
                                        </td>
                                        <td class="small text-muted">{{ $alloc->method }}</td>
                                        <td class="text-end text-success fw-semibold">${{ number_format($alloc->amount, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="2"><strong>Total asignado</strong></td>
                                        <td class="text-end"><strong class="text-success">${{ number_format($period->allocatedAmount(), 2) }}</strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-3 text-muted small">
                            <i class="bi bi-inbox me-1"></i> Sin asignaciones aún.
                            <div class="mt-1">Crea una producción para asignar overhead desde este período.</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
