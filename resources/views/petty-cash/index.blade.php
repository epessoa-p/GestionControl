@extends('layouts.app')
@section('title', 'Caja Chica')
@section('page')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h1 class="mb-1"><i class="bi bi-wallet2 text-primary me-2"></i>Caja Chica</h1><p class="text-muted mb-0">Gestión de fondos de caja chica</p></div>
        <a href="{{ route('petty-cash.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Nueva caja chica</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nombre</th>
                            <th>Sucursal</th>
                            <th class="text-end">Monto inicial</th>
                            <th class="text-end">Saldo actual</th>
                            <th>Estado</th>
                            <th>Creado por</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pettyCashes as $pc)
                            <tr>
                                <td><a href="{{ route('petty-cash.show', $pc) }}" class="text-decoration-none fw-semibold">{{ $pc->name }}</a></td>
                                <td>{{ $pc->branch?->name ?? '-' }}</td>
                                <td class="text-end">${{ number_format($pc->initial_amount, 2) }}</td>
                                <td class="text-end fw-semibold {{ $pc->current_balance <= 0 ? 'text-danger' : '' }}">${{ number_format($pc->current_balance, 2) }}</td>
                                <td>{!! $pc->active ? '<span class="badge bg-success">Activa</span>' : '<span class="badge bg-secondary">Inactiva</span>' !!}</td>
                                <td>{{ $pc->createdBy?->name }}</td>
                                <td class="text-end">
                                    <form action="{{ route('petty-cash.destroy', $pc) }}" method="POST" class="d-inline">@csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar caja chica?')"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-1 d-block mb-2"></i>No hay cajas chicas registradas</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="mt-4 d-flex justify-content-center">{{ $pettyCashes->links() }}</div>
</div>
@endsection
