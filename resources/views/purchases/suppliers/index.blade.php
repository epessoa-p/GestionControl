@extends('layouts.app')
@section('title', 'Proveedores')
@section('page')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><i class="bi bi-truck text-primary me-2"></i>Proveedores</h1>
            <p class="text-muted mb-0">Gestión de proveedores de la empresa</p>
        </div>
        <a href="{{ route('purchases.suppliers.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Nuevo proveedor
        </a>
    </div>

    @if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if(session('error'))<div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    {{-- Filtros de status --}}
    <div class="d-flex gap-2 flex-wrap mb-3">
        @foreach(['todos' => 'Todos', 'activo' => 'Activos', 'inactivo' => 'Inactivos'] as $val => $label)
            <a href="{{ request()->fullUrlWithQuery(['status' => $val]) }}"
               class="btn btn-sm {{ $status === $val ? 'btn-primary' : 'btn-outline-secondary' }}">
                {{ $label }}
                <span class="badge {{ $status === $val ? 'bg-white text-primary' : 'bg-secondary' }} ms-1">{{ $counts[$val] }}</span>
            </a>
        @endforeach
        <form class="ms-auto d-flex gap-2" method="GET">
            <input type="hidden" name="status" value="{{ $status }}">
            <input type="text" name="q" class="form-control form-control-sm" value="{{ $search }}" placeholder="Buscar proveedor...">
            <button class="btn btn-sm btn-outline-primary"><i class="bi bi-search"></i></button>
        </form>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nº</th>
                        <th>Proveedor</th>
                        <th>Documento</th>
                        <th>Contacto</th>
                        <th>Plazo pago</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $supplier)
                        <tr>
                            <td><span class="badge bg-light text-dark border">{{ $supplier->supplier_number }}</span></td>
                            <td>
                                <div class="fw-semibold">{{ $supplier->display_name }}</div>
                                @if($supplier->commercial_name && $supplier->name !== $supplier->display_name)
                                    <small class="text-muted">{{ $supplier->name }}</small>
                                @endif
                            </td>
                            <td>
                                @if($supplier->document_number)
                                    <small class="text-muted">{{ \App\Models\Supplier::DOCUMENT_LABELS[$supplier->document_type] ?? '' }}</small><br>
                                    {{ $supplier->document_number }}
                                @else <span class="text-muted">—</span> @endif
                            </td>
                            <td>
                                @if($supplier->email)<div><i class="bi bi-envelope me-1 text-muted"></i>{{ $supplier->email }}</div>@endif
                                @if($supplier->phone)<div><i class="bi bi-telephone me-1 text-muted"></i>{{ $supplier->phone }}</div>@endif
                            </td>
                            <td>{{ $supplier->payment_terms }} días</td>
                            <td>
                                <span class="badge bg-{{ \App\Models\Supplier::STATUS_COLORS[$supplier->status] }}-subtle text-{{ \App\Models\Supplier::STATUS_COLORS[$supplier->status] }}">
                                    {{ \App\Models\Supplier::STATUS_LABELS[$supplier->status] }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('purchases.suppliers.show', $supplier) }}" class="btn btn-sm btn-outline-secondary me-1" title="Ver"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('purchases.suppliers.edit', $supplier) }}" class="btn btn-sm btn-outline-primary me-1" title="Editar"><i class="bi bi-pencil"></i></a>
                                <form action="{{ route('purchases.suppliers.destroy', $supplier) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('¿Eliminar este proveedor?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-truck display-4 d-block mb-2 opacity-25"></i>
                            No hay proveedores registrados.
                            <a href="{{ route('purchases.suppliers.create') }}" class="d-block mt-2">Crear el primero</a>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($suppliers->hasPages())
            <div class="card-footer bg-white">{{ $suppliers->links() }}</div>
        @endif
    </div>
</div>
@endsection
