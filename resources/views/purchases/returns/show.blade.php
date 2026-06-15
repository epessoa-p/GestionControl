@extends('layouts.app')
@section('title', 'Devolución ' . $return->return_number)
@section('page')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h1 class="mb-0"><i class="bi bi-arrow-return-left text-primary me-2"></i>{{ $return->return_number }}</h1>
                <span class="badge bg-{{ \App\Models\PurchaseReturn::STATUS_COLORS[$return->status] }}-subtle text-{{ \App\Models\PurchaseReturn::STATUS_COLORS[$return->status] }} fs-6">
                    {{ \App\Models\PurchaseReturn::STATUS_LABELS[$return->status] }}
                </span>
                <span class="badge bg-secondary-subtle text-secondary">{{ \App\Models\PurchaseReturn::REASON_LABELS[$return->reason] ?? $return->reason }}</span>
            </div>
            <p class="text-muted mb-0">
                {{ $return->supplier?->display_name }}
                @if($return->reception)
                · Recepción: <a href="{{ route('purchases.receptions.show', $return->reception) }}">{{ $return->reception->reception_number }}</a>
                @endif
                · {{ $return->return_date?->format('d/m/Y') }}
            </p>
        </div>
        <a href="{{ route('purchases.returns.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Volver</a>
    </div>

    @if(session('success'))<div class="alert alert-success alert-dismissible fade show mb-3"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if(session('error'))<div class="alert alert-danger alert-dismissible fade show mb-3"><i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-2 px-4">
                    <h6 class="fw-bold mb-0"><i class="bi bi-list-ul me-1 text-primary"></i> Ítems devueltos</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Producto</th>
                                <th class="text-end">Cantidad</th>
                                <th class="text-end">P. Unit.</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($return->items as $item)
                            <tr>
                                <td>{{ $item->product?->name ?? '—' }}</td>
                                <td class="text-end">{{ number_format($item->quantity, 2) }}</td>
                                <td class="text-end">${{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-end fw-semibold">${{ number_format($item->total, 2) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">Sin ítems</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="3" class="text-end fw-bold fs-6">Total:</td>
                                <td class="text-end fw-bold fs-6 text-primary">${{ number_format($return->total, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            @if($return->notes)
            <div class="card border-0 shadow-sm">
                <div class="card-body"><small class="text-muted d-block mb-1">Notas</small>{{ $return->notes }}</div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            @if($return->status === 'borrador')
            <div class="d-flex flex-column gap-2">
                <form action="{{ route('purchases.returns.confirm', $return) }}" method="POST" onsubmit="return confirm('¿Confirmar devolución? Se decrementará el stock de los productos.')">
                    @csrf
                    <button class="btn btn-warning w-100"><i class="bi bi-check-circle me-1"></i> Confirmar devolución</button>
                </form>
                <form action="{{ route('purchases.returns.cancel', $return) }}" method="POST" onsubmit="return confirm('¿Cancelar esta devolución?')">
                    @csrf
                    <button class="btn btn-outline-secondary w-100">Cancelar</button>
                </form>
                <form action="{{ route('purchases.returns.destroy', $return) }}" method="POST" onsubmit="return confirm('¿Eliminar esta devolución?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger w-100"><i class="bi bi-trash me-1"></i> Eliminar</button>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
