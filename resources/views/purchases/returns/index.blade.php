@extends('layouts.app')
@section('title', 'Devoluciones')
@section('page')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><i class="bi bi-arrow-return-left text-primary me-2"></i>Devoluciones</h1>
            <p class="text-muted mb-0">Devoluciones de mercancía a proveedores</p>
        </div>
        <a href="{{ route('purchases.returns.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Nueva devolución
        </a>
    </div>

    @if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if(session('error'))<div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    <div class="d-flex gap-2 flex-wrap mb-3">
        @foreach(array_merge(['todos' => 'Todos'], \App\Models\PurchaseReturn::STATUS_LABELS) as $val => $label)
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
                        <th>Devolución</th>
                        <th>Proveedor</th>
                        <th>Recepción orig.</th>
                        <th>Motivo</th>
                        <th>Fecha</th>
                        <th class="text-end">Total</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returns as $ret)
                    <tr>
                        <td><a href="{{ route('purchases.returns.show', $ret) }}" class="fw-semibold text-decoration-none">{{ $ret->return_number }}</a></td>
                        <td>{{ $ret->supplier?->display_name ?? '—' }}</td>
                        <td>
                            @if($ret->reception)
                            <a href="{{ route('purchases.receptions.show', $ret->reception) }}" class="small">{{ $ret->reception->reception_number }}</a>
                            @else —
                            @endif
                        </td>
                        <td><span class="badge bg-secondary-subtle text-secondary">{{ \App\Models\PurchaseReturn::REASON_LABELS[$ret->reason] ?? $ret->reason }}</span></td>
                        <td>{{ $ret->return_date?->format('d/m/Y') }}</td>
                        <td class="text-end fw-semibold">${{ number_format($ret->total, 2) }}</td>
                        <td><span class="badge bg-{{ \App\Models\PurchaseReturn::STATUS_COLORS[$ret->status] }}-subtle text-{{ \App\Models\PurchaseReturn::STATUS_COLORS[$ret->status] }}">{{ \App\Models\PurchaseReturn::STATUS_LABELS[$ret->status] }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('purchases.returns.show', $ret) }}" class="btn btn-sm btn-outline-secondary me-1"><i class="bi bi-eye"></i></a>
                            @if($ret->status === 'borrador')
                            <form action="{{ route('purchases.returns.destroy', $ret) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar esta devolución?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-5 text-muted">
                        <i class="bi bi-arrow-return-left display-4 d-block mb-2 opacity-25"></i>
                        No hay devoluciones registradas.
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($returns->hasPages())<div class="card-footer bg-white">{{ $returns->links() }}</div>@endif
    </div>
</div>
@endsection
