@extends('layouts.app')
@section('title', $transfer->transfer_number)
@section('page')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><i class="bi bi-arrow-left-right text-primary me-2"></i>{{ $transfer->transfer_number }}</h1>
            <p class="text-muted mb-0">Traspaso — {{ $transfer->transfer_date?->format('d/m/Y') }}</p>
        </div>
        <div class="d-flex gap-2">
            @if($transfer->status === 'draft')
                <form action="{{ route('transfers.dispatch', $transfer) }}" method="POST">@csrf
                    <button class="btn btn-info text-white" onclick="return confirm('¿Enviar traspaso? Se descontará stock del origen.')"><i class="bi bi-truck me-1"></i> Enviar</button>
                </form>
            @endif
            @if($transfer->status === 'in_transit')
                <form action="{{ route('transfers.complete', $transfer) }}" method="POST">@csrf
                    <button class="btn btn-success" onclick="return confirm('¿Recibir traspaso? Se agregará stock al destino.')"><i class="bi bi-check-lg me-1"></i> Recibir</button>
                </form>
            @endif
            @if($transfer->status !== 'completed' && $transfer->status !== 'cancelled')
                <form action="{{ route('transfers.cancel', $transfer) }}" method="POST">@csrf
                    <button class="btn btn-warning" onclick="return confirm('¿Cancelar traspaso?')"><i class="bi bi-x-lg me-1"></i> Cancelar</button>
                </form>
            @endif
            <a href="{{ route('transfers.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;flex-shrink:0;background:var(--bs-{{ \App\Models\WarehouseTransfer::STATUS_COLORS[$transfer->status] ?? 'secondary' }});color:#fff;">
                        <i class="bi bi-circle-fill fs-5"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Estado</small>
                        <span class="badge bg-{{ \App\Models\WarehouseTransfer::STATUS_COLORS[$transfer->status] ?? 'secondary' }}">
                            {{ \App\Models\WarehouseTransfer::STATUS_LABELS[$transfer->status] ?? $transfer->status }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-danger text-white" style="width:48px;height:48px;flex-shrink:0;"><i class="bi bi-building fs-5"></i></div>
                    <div>
                        <small class="text-muted d-block">Almacén Origen</small>
                        <strong>{{ $transfer->fromWarehouse?->name }}</strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-success text-white" style="width:48px;height:48px;flex-shrink:0;"><i class="bi bi-building fs-5"></i></div>
                    <div>
                        <small class="text-muted d-block">Almacén Destino</small>
                        <strong>{{ $transfer->toWarehouse?->name }}</strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary bg-opacity-10">
                <div class="card-body text-center py-3">
                    <small class="text-muted d-block">Total Productos</small>
                    <h3 class="fw-bold text-primary mb-0">{{ $transfer->details->count() }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Info sidebar --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold mb-0"><i class="bi bi-info-circle me-2 text-primary"></i>Información</h5>
                </div>
                <div class="card-body px-4 pt-3">
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Fecha</span>
                            <span>{{ $transfer->transfer_date?->format('d/m/Y') }}</span>
                        </li>
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Creado por</span>
                            <span>{{ $transfer->createdBy?->name }}</span>
                        </li>
                        @if($transfer->confirmedBy)
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Recibido por</span>
                            <span>{{ $transfer->confirmedBy?->name }}</span>
                        </li>
                        @endif
                        @if($transfer->confirmed_at)
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Fecha recepción</span>
                            <span>{{ $transfer->confirmed_at?->format('d/m/Y H:i') }}</span>
                        </li>
                        @endif
                        <li class="d-flex justify-content-between py-2">
                            <span class="text-muted">Notas</span>
                            <span class="text-end" style="max-width:60%;">{{ $transfer->notes ?: '—' }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Flow indicator --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 text-center">
                    <h6 class="fw-bold text-muted mb-3">Flujo del traspaso</h6>
                    <div class="d-flex align-items-center justify-content-center gap-2">
                        <div class="p-2 rounded bg-danger bg-opacity-10 text-danger">
                            <i class="bi bi-building fs-4 d-block"></i>
                            <small class="fw-semibold">{{ $transfer->fromWarehouse?->name }}</small>
                        </div>
                        <i class="bi bi-arrow-right fs-3 text-muted"></i>
                        <div class="p-2 rounded bg-success bg-opacity-10 text-success">
                            <i class="bi bi-building fs-4 d-block"></i>
                            <small class="fw-semibold">{{ $transfer->toWarehouse?->name }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Products --}}
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
                                    <th class="ps-4">#</th>
                                    <th>Producto</th>
                                    <th class="text-end">Cantidad</th>
                                    <th>Notas</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transfer->details as $idx => $d)
                                    <tr>
                                        <td class="ps-4 text-muted">{{ $idx + 1 }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $d->product?->name }}</div>
                                            <small class="text-muted">{{ $d->product?->sku }}</small>
                                        </td>
                                        <td class="text-end fw-semibold">{{ number_format($d->quantity, 2) }}</td>
                                        <td><small class="text-muted">{{ $d->notes ?: '—' }}</small></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
