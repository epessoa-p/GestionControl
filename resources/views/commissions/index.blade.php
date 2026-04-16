@extends('layouts.app')
@section('title', 'Comisiones')
@section('page')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><i class="bi bi-percent text-primary me-2"></i>Comisiones</h1>
            <p class="text-muted mb-0">Gestión de comisiones a promotores</p>
        </div>
    </div>

    {{-- Summary cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning bg-opacity-10">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Pendientes</h6>
                    <h3 class="fw-bold text-warning mb-0">${{ number_format($totalPending, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success bg-opacity-10">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Pagadas</h6>
                    <h3 class="fw-bold text-success mb-0">${{ number_format($totalPaid, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label">Estado</label>
                    <select name="status" class="form-select">
                        <option value="">Todos</option>
                        <option value="pending" @selected(($filters['status'] ?? '') === 'pending')>Pendiente</option>
                        <option value="paid" @selected(($filters['status'] ?? '') === 'paid')>Pagada</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Promotor</label>
                    <select name="promoter_id" class="form-select">
                        <option value="">Todos</option>
                        @foreach($promoters as $p)
                            <option value="{{ $p->id }}" @selected(($filters['promoter_id'] ?? '') == $p->id)>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Desde</label>
                    <input type="date" name="from" class="form-control" value="{{ $filters['from'] ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Hasta</label>
                    <input type="date" name="to" class="form-control" value="{{ $filters['to'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary"><i class="bi bi-search"></i> Filtrar</button>
                    <a href="{{ route('commissions.index') }}" class="btn btn-outline-secondary">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <form id="bulkForm" action="{{ route('commissions.mark-paid-bulk') }}" method="POST">
        @csrf
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <strong>Listado</strong>
                <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('¿Marcar seleccionadas como pagadas?')">
                    <i class="bi bi-check-all"></i> Pagar seleccionadas
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th><input type="checkbox" id="checkAll"></th>
                                <th>Venta</th>
                                <th>Promotor</th>
                                <th class="text-end">Monto</th>
                                <th class="text-end">Tasa</th>
                                <th>Estado</th>
                                <th>Fecha pago</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($commissions as $c)
                                <tr>
                                    <td>
                                        @if($c->status === 'pending')
                                            <input type="checkbox" name="commission_ids[]" value="{{ $c->id }}" class="chk">
                                        @endif
                                    </td>
                                    <td>{{ $c->sale?->sale_number ?? '-' }}</td>
                                    <td>{{ $c->promoter?->name ?? '-' }}</td>
                                    <td class="text-end">${{ number_format($c->amount, 2) }}</td>
                                    <td class="text-end">{{ number_format($c->rate, 2) }}%</td>
                                    <td><span class="badge bg-{{ $c->status === 'paid' ? 'success' : 'warning' }}">{{ $c->status === 'paid' ? 'Pagada' : 'Pendiente' }}</span></td>
                                    <td>{{ $c->paid_at ? \Carbon\Carbon::parse($c->paid_at)->format('d/m/Y') : '-' }}</td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            @if($c->status === 'pending')
                                                <form action="{{ route('commissions.mark-paid', $c) }}" method="POST">@csrf
                                                    <button class="btn btn-sm btn-outline-success" title="Pagar" onclick="return confirm('¿Marcar como pagada?')"><i class="bi bi-check-lg"></i></button>
                                                </form>
                                            @endif
                                            <form action="{{ route('commissions.destroy', $c) }}" method="POST">@csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="return confirm('¿Eliminar comisión?')"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted py-5"><i class="bi bi-inbox fs-1 d-block mb-2"></i>No hay comisiones registradas</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </form>

    <div class="mt-3">{{ $commissions->links() }}</div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('checkAll').addEventListener('change', function() {
    document.querySelectorAll('.chk').forEach(c => c.checked = this.checked);
});
</script>
@endpush
