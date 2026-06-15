@extends('layouts.app')
@section('title', 'Cotizaciones')
@section('page')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><i class="bi bi-file-earmark-text text-primary me-2"></i>Cotizaciones</h1>
            <p class="text-muted mb-0">Cotizaciones de proveedores</p>
        </div>
        <a href="{{ route('purchases.quotations.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Nueva cotización
        </a>
    </div>

    @if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if(session('error'))<div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    <div class="d-flex gap-2 flex-wrap mb-3">
        @foreach(array_merge(['todos' => 'Todos'], \App\Models\PurchaseQuotation::STATUS_LABELS) as $val => $label)
            <a href="{{ request()->fullUrlWithQuery(['status' => $val]) }}"
               class="btn btn-sm {{ $status === $val ? 'btn-primary' : 'btn-outline-secondary' }}">
                {{ $label }}
                <span class="badge {{ $status === $val ? 'bg-white text-primary' : 'bg-secondary' }} ms-1">{{ $counts[$val] ?? 0 }}</span>
            </a>
        @endforeach
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Cotización</th>
                        <th>Proveedor</th>
                        <th>Solicitud</th>
                        <th>Fecha</th>
                        <th>Válida hasta</th>
                        <th class="text-end">Total</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($quotations as $quot)
                    <tr>
                        <td><a href="{{ route('purchases.quotations.show', $quot) }}" class="fw-semibold text-decoration-none">{{ $quot->quotation_number }}</a></td>
                        <td>{{ $quot->supplier?->display_name ?? '—' }}</td>
                        <td>
                            @if($quot->purchaseRequest)
                            <a href="{{ route('purchases.requests.show', $quot->purchaseRequest) }}" class="small">{{ $quot->purchaseRequest->request_number }}</a>
                            @else —
                            @endif
                        </td>
                        <td>{{ $quot->quotation_date?->format('d/m/Y') }}</td>
                        <td>
                            @if($quot->valid_until)
                                <span class="{{ $quot->valid_until < now() ? 'text-danger' : '' }}">{{ $quot->valid_until->format('d/m/Y') }}</span>
                            @else —
                            @endif
                        </td>
                        <td class="text-end fw-semibold">${{ number_format($quot->total, 2) }}</td>
                        <td><span class="badge bg-{{ \App\Models\PurchaseQuotation::STATUS_COLORS[$quot->status] }}-subtle text-{{ \App\Models\PurchaseQuotation::STATUS_COLORS[$quot->status] }}">{{ \App\Models\PurchaseQuotation::STATUS_LABELS[$quot->status] }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('purchases.quotations.show', $quot) }}" class="btn btn-sm btn-outline-secondary me-1"><i class="bi bi-eye"></i></a>
                            @if(in_array($quot->status, ['borrador','rechazada','cancelada']))
                            <form action="{{ route('purchases.quotations.destroy', $quot) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar esta cotización?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-5 text-muted">
                        <i class="bi bi-file-earmark-text display-4 d-block mb-2 opacity-25"></i>
                        No hay cotizaciones registradas.
                        <a href="{{ route('purchases.quotations.create') }}" class="d-block mt-2">Crear la primera</a>
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($quotations->hasPages())<div class="card-footer bg-white">{{ $quotations->links() }}</div>@endif
    </div>
</div>
@endsection
