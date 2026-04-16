@extends('layouts.app')
@section('title', $departure->departure_number)
@section('page')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><i class="bi bi-box-arrow-up text-danger me-2"></i>{{ $departure->departure_number }}</h1>
            <p class="text-muted mb-0">Salida de mercancía — {{ $departure->departure_date?->format('d/m/Y') }}</p>
        </div>
        <div class="d-flex gap-2">
            @if($departure->status === 'draft')
                <form action="{{ route('departures.confirm', $departure) }}" method="POST">@csrf
                    <button class="btn btn-success" onclick="return confirm('¿Confirmar salida y descontar inventario?')"><i class="bi bi-check-lg me-1"></i> Confirmar</button>
                </form>
            @elseif($departure->status === 'confirmed')
                <form action="{{ route('departures.cancel', $departure) }}" method="POST">@csrf
                    <button class="btn btn-warning" onclick="return confirm('¿Anular salida? Se revertirá el inventario.')"><i class="bi bi-x-lg me-1"></i> Anular</button>
                </form>
            @endif
            <a href="{{ route('departures.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
        </div>
    </div>

    {{-- Status stepper --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-4">
            <div class="d-flex justify-content-center">
                @php
                    $steps = ['draft' => 'Borrador', 'confirmed' => 'Confirmada', 'cancelled' => 'Anulada'];
                    $currentIdx = $departure->status === 'cancelled' ? 2 : ($departure->status === 'confirmed' ? 1 : 0);
                    $isCancelled = $departure->status === 'cancelled';
                @endphp
                @foreach($steps as $key => $label)
                    @php
                        $idx = $loop->index;
                        $isActive = $key === $departure->status;
                        $isPast = !$isCancelled && $idx < $currentIdx;
                        $color = $isCancelled && $key === 'cancelled' ? 'danger' : ($isActive ? 'primary' : ($isPast ? 'success' : 'secondary'));
                    @endphp
                    <div class="text-center px-4">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center {{ $isActive || $isPast ? 'bg-'.$color.' text-white' : 'bg-light text-muted border' }}" style="width:44px;height:44px;">
                            @if($isPast)<i class="bi bi-check-lg"></i>@elseif($isCancelled && $key === 'cancelled')<i class="bi bi-x-lg"></i>@else<span class="fw-bold">{{ $idx + 1 }}</span>@endif
                        </div>
                        <div class="mt-2 small {{ $isActive ? 'fw-bold text-'.$color : 'text-muted' }}">{{ $label }}</div>
                    </div>
                    @if(!$loop->last)
                        <div class="flex-grow-1 d-flex align-items-center" style="max-width:120px;"><hr class="w-100 {{ $isPast ? 'border-success' : 'border-secondary' }}" style="border-width:2px;opacity:.4;"></div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Info sidebar --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold mb-0"><i class="bi bi-info-circle me-2 text-primary"></i>Información</h5>
                </div>
                <div class="card-body px-4 pt-3">
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted"><i class="bi bi-circle-fill me-1 small"></i> Estado</span>
                            <span class="badge bg-{{ \App\Models\Departure::STATUS_COLORS[$departure->status] ?? 'secondary' }}">{{ \App\Models\Departure::STATUS_LABELS[$departure->status] ?? $departure->status }}</span>
                        </li>
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted"><i class="bi bi-calendar me-1 small"></i> Fecha</span>
                            <span>{{ $departure->departure_date?->format('d/m/Y') }}</span>
                        </li>
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted"><i class="bi bi-building me-1 small"></i> Almacén</span>
                            <span>{{ $departure->warehouse?->name }}</span>
                        </li>
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted"><i class="bi bi-signpost me-1 small"></i> Motivo</span>
                            <span class="badge bg-secondary">{{ \App\Models\Departure::REASON_LABELS[$departure->reason] ?? $departure->reason }}</span>
                        </li>
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted"><i class="bi bi-person me-1 small"></i> Creado por</span>
                            <span>{{ $departure->createdBy?->name }}</span>
                        </li>
                        <li class="d-flex justify-content-between py-2">
                            <span class="text-muted"><i class="bi bi-chat-text me-1 small"></i> Notas</span>
                            <span class="text-end" style="max-width:60%;">{{ $departure->notes ?: '—' }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Total card --}}
            <div class="card border-0 shadow-sm mt-3 bg-danger bg-opacity-10">
                <div class="card-body text-center py-4">
                    <small class="text-muted d-block mb-1">Total de la salida</small>
                    <h2 class="fw-bold text-danger mb-0">${{ number_format($departure->total, 2) }}</h2>
                    <small class="text-muted">{{ $departure->details->count() }} producto(s)</small>
                </div>
            </div>
        </div>

        {{-- Products table --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold mb-0"><i class="bi bi-box-seam me-2 text-primary"></i>Productos</h5>
                </div>
                <div class="card-body p-0 pt-3">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Producto</th>
                                    <th class="text-end">Cantidad</th>
                                    <th class="text-end">Costo unit.</th>
                                    <th class="text-end pe-4">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($departure->details as $d)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-semibold">{{ $d->product?->name }}</div>
                                            <small class="text-muted">{{ $d->product?->sku }}</small>
                                        </td>
                                        <td class="text-end">{{ number_format($d->quantity, 2) }}</td>
                                        <td class="text-end">${{ number_format($d->unit_cost, 2) }}</td>
                                        <td class="text-end pe-4 fw-semibold">${{ number_format($d->total, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <th class="ps-4" colspan="3">Total</th>
                                    <th class="text-end pe-4 fs-5 text-danger">${{ number_format($departure->total, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
