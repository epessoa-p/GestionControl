@extends('layouts.app')
@section('title', 'Solicitudes de Compra')
@section('page')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><i class="bi bi-file-earmark-plus text-primary me-2"></i>Solicitudes de Compra</h1>
            <p class="text-muted mb-0">Gestión de solicitudes internas de compra</p>
        </div>
        <a href="{{ route('purchases.requests.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Nueva solicitud
        </a>
    </div>

    @if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if(session('error'))<div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    <div class="d-flex gap-2 flex-wrap mb-3">
        @foreach(array_merge(['todos' => 'Todos'], \App\Models\PurchaseRequest::STATUS_LABELS) as $val => $label)
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
                        <th>Solicitud</th>
                        <th>Solicitante</th>
                        <th>Departamento</th>
                        <th>Prioridad</th>
                        <th>Fecha esperada</th>
                        <th>Ítems</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                    <tr>
                        <td><a href="{{ route('purchases.requests.show', $req) }}" class="fw-semibold text-decoration-none">{{ $req->request_number }}</a></td>
                        <td>{{ $req->requestedBy?->name ?? '—' }}</td>
                        <td>{{ $req->department ?: '—' }}</td>
                        <td><span class="badge bg-{{ \App\Models\PurchaseRequest::PRIORITY_COLORS[$req->priority] }}-subtle text-{{ \App\Models\PurchaseRequest::PRIORITY_COLORS[$req->priority] }}">{{ \App\Models\PurchaseRequest::PRIORITY_LABELS[$req->priority] }}</span></td>
                        <td>{{ $req->expected_date?->format('d/m/Y') ?? '—' }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $req->items_count ?? '—' }}</span></td>
                        <td><span class="badge bg-{{ \App\Models\PurchaseRequest::STATUS_COLORS[$req->status] }}-subtle text-{{ \App\Models\PurchaseRequest::STATUS_COLORS[$req->status] }}">{{ \App\Models\PurchaseRequest::STATUS_LABELS[$req->status] }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('purchases.requests.show', $req) }}" class="btn btn-sm btn-outline-secondary me-1"><i class="bi bi-eye"></i></a>
                            @if(in_array($req->status, ['borrador','rechazada','cancelada']))
                            <form action="{{ route('purchases.requests.destroy', $req) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar esta solicitud?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-5 text-muted">
                        <i class="bi bi-file-earmark-plus display-4 d-block mb-2 opacity-25"></i>
                        No hay solicitudes registradas.
                        <a href="{{ route('purchases.requests.create') }}" class="d-block mt-2">Crear la primera</a>
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($requests->hasPages())<div class="card-footer bg-white">{{ $requests->links() }}</div>@endif
    </div>
</div>
@endsection
