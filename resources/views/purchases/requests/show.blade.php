@extends('layouts.app')
@section('title', 'Solicitud ' . $purchaseRequest->request_number)
@section('page')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h1 class="mb-0"><i class="bi bi-file-earmark-plus text-primary me-2"></i>{{ $purchaseRequest->request_number }}</h1>
                <span class="badge bg-{{ \App\Models\PurchaseRequest::STATUS_COLORS[$purchaseRequest->status] }}-subtle text-{{ \App\Models\PurchaseRequest::STATUS_COLORS[$purchaseRequest->status] }} fs-6">
                    {{ \App\Models\PurchaseRequest::STATUS_LABELS[$purchaseRequest->status] }}
                </span>
                <span class="badge bg-{{ \App\Models\PurchaseRequest::PRIORITY_COLORS[$purchaseRequest->priority] }}-subtle text-{{ \App\Models\PurchaseRequest::PRIORITY_COLORS[$purchaseRequest->priority] }}">
                    <i class="bi bi-flag-fill me-1"></i>{{ \App\Models\PurchaseRequest::PRIORITY_LABELS[$purchaseRequest->priority] }}
                </span>
            </div>
            <p class="text-muted mb-0">
                Solicitado por <strong>{{ $purchaseRequest->requestedBy?->name }}</strong>
                {{ $purchaseRequest->department ? '· ' . $purchaseRequest->department : '' }}
                · {{ $purchaseRequest->created_at->format('d/m/Y H:i') }}
            </p>
        </div>
        <a href="{{ route('purchases.requests.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Volver</a>
    </div>

    @if(session('success'))<div class="alert alert-success alert-dismissible fade show mb-3"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if(session('error'))<div class="alert alert-danger alert-dismissible fade show mb-3"><i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    <div class="row g-4">
        <div class="col-lg-8">
            {{-- Ítems --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-2 px-4">
                    <h6 class="fw-bold mb-0"><i class="bi bi-list-ul me-1 text-primary"></i> Productos solicitados</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Producto</th>
                                <th class="text-end">Cantidad</th>
                                <th class="text-end">Costo est.</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($purchaseRequest->items as $item)
                            <tr>
                                <td>{{ $item->product?->name ?? '—' }}</td>
                                <td class="text-end">{{ number_format($item->quantity, 2) }}</td>
                                <td class="text-end">${{ number_format($item->estimated_unit_cost, 2) }}</td>
                                <td class="text-end fw-semibold">${{ number_format($item->line_total, 2) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">Sin ítems</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="3" class="text-end fw-bold">Total estimado:</td>
                                <td class="text-end fw-bold text-primary">${{ number_format($purchaseRequest->estimated_total, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Cotizaciones vinculadas --}}
            @if($purchaseRequest->quotations->count())
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-2 px-4 d-flex justify-content-between">
                    <h6 class="fw-bold mb-0"><i class="bi bi-file-earmark-text me-1 text-primary"></i> Cotizaciones derivadas</h6>
                    <a href="{{ route('purchases.quotations.create', ['request_id' => $purchaseRequest->id]) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-plus-lg me-1"></i> Nueva cotización
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light"><tr><th>Cotización</th><th>Proveedor</th><th class="text-end">Total</th><th>Estado</th></tr></thead>
                        <tbody>
                            @foreach($purchaseRequest->quotations as $quot)
                            <tr>
                                <td><a href="{{ route('purchases.quotations.show', $quot) }}">{{ $quot->quotation_number }}</a></td>
                                <td>{{ $quot->supplier?->display_name ?? '—' }}</td>
                                <td class="text-end">${{ number_format($quot->total, 2) }}</td>
                                <td><span class="badge bg-{{ \App\Models\PurchaseQuotation::STATUS_COLORS[$quot->status] }}-subtle text-{{ \App\Models\PurchaseQuotation::STATUS_COLORS[$quot->status] }}">{{ \App\Models\PurchaseQuotation::STATUS_LABELS[$quot->status] }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            {{-- Info --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    @if($purchaseRequest->expected_date)
                    <div class="mb-3">
                        <small class="text-muted d-block">Fecha esperada</small>
                        <strong>{{ $purchaseRequest->expected_date->format('d/m/Y') }}</strong>
                    </div>
                    @endif
                    @if($purchaseRequest->notes)
                    <div>
                        <small class="text-muted d-block">Notas</small>
                        <p class="mb-0">{{ $purchaseRequest->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Transiciones de estado --}}
            @php
                $transitions = [
                    'borrador'   => ['pendiente' => ['Enviar para aprobación', 'primary']],
                    'pendiente'  => ['aprobada' => ['Aprobar', 'success'], 'rechazada' => ['Rechazar', 'danger']],
                    'aprobada'   => ['cancelada' => ['Cancelar', 'warning']],
                    'rechazada'  => ['borrador' => ['Volver a borrador', 'secondary']],
                    'en_proceso' => ['completada' => ['Marcar completada', 'success'], 'cancelada' => ['Cancelar', 'warning']],
                ];
                $availableTransitions = $transitions[$purchaseRequest->status] ?? [];
            @endphp
            @if($availableTransitions)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-2 px-4">
                    <h6 class="fw-bold mb-0 small"><i class="bi bi-arrow-right-circle me-1 text-primary"></i> Cambiar estado</h6>
                </div>
                <div class="card-body p-3 d-flex flex-column gap-2">
                    @foreach($availableTransitions as $newStatus => [$label, $color])
                    <form action="{{ route('purchases.requests.update-status', $purchaseRequest) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="{{ $newStatus }}">
                        <button type="submit" class="btn btn-{{ $color }} btn-sm w-100">{{ $label }}</button>
                    </form>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Acción cotizar --}}
            @if(in_array($purchaseRequest->status, ['aprobada','en_proceso']))
            <a href="{{ route('purchases.quotations.create', ['request_id' => $purchaseRequest->id]) }}"
               class="btn btn-outline-primary w-100 mb-2">
                <i class="bi bi-file-earmark-text me-1"></i> Crear cotización
            </a>
            @endif

            {{-- Eliminar --}}
            @if(in_array($purchaseRequest->status, ['borrador','rechazada','cancelada']))
            <form action="{{ route('purchases.requests.destroy', $purchaseRequest) }}" method="POST"
                  onsubmit="return confirm('¿Eliminar esta solicitud?')">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger w-100"><i class="bi bi-trash me-1"></i> Eliminar</button>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection
