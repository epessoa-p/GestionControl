@extends('layouts.app')
@section('title', $pettyCash->name)
@section('page')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><i class="bi bi-wallet2 text-primary me-2"></i>{{ $pettyCash->name }}</h1>
            <p class="text-muted mb-0">Detalle y movimientos de caja chica</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('petty-cash.edit', $pettyCash) }}" class="btn btn-primary"><i class="bi bi-pencil me-1"></i> Editar</a>
            <a href="{{ route('petty-cash.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
        </div>
    </div>

    {{-- Balance + KPI cards --}}
    @php
        $totalExpenses = $movements->where('type', 'expense')->sum('amount');
        $totalReplenishments = $movements->where('type', 'replenishment')->sum('amount');
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm {{ $pettyCash->current_balance <= 0 ? 'bg-danger' : 'bg-success' }} bg-opacity-10">
                <div class="card-body text-center py-3">
                    <small class="text-muted d-block">Saldo actual</small>
                    <h3 class="fw-bold mb-0 {{ $pettyCash->current_balance <= 0 ? 'text-danger' : 'text-success' }}">${{ number_format($pettyCash->current_balance, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary text-white" style="width:48px;height:48px;flex-shrink:0;"><i class="bi bi-cash fs-5"></i></div>
                    <div>
                        <small class="text-muted d-block">Monto inicial</small>
                        <strong>${{ number_format($pettyCash->initial_amount, 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-danger text-white" style="width:48px;height:48px;flex-shrink:0;"><i class="bi bi-arrow-up-right fs-5"></i></div>
                    <div>
                        <small class="text-muted d-block">Total gastos</small>
                        <strong class="text-danger">${{ number_format($totalExpenses, 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-success text-white" style="width:48px;height:48px;flex-shrink:0;"><i class="bi bi-arrow-down-left fs-5"></i></div>
                    <div>
                        <small class="text-muted d-block">Total reposiciones</small>
                        <strong class="text-success">${{ number_format($totalReplenishments, 2) }}</strong>
                    </div>
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
                        <li class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted"><i class="bi bi-building me-1 small"></i> Sucursal</span><span>{{ $pettyCash->branch?->name ?? '—' }}</span></li>
                        <li class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted"><i class="bi bi-circle-fill me-1 small"></i> Estado</span><span class="badge bg-{{ $pettyCash->active ? 'success' : 'secondary' }}">{{ $pettyCash->active ? 'Activa' : 'Inactiva' }}</span></li>
                        <li class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted"><i class="bi bi-person me-1 small"></i> Creado por</span><span>{{ $pettyCash->createdBy?->name }}</span></li>
                        <li class="d-flex justify-content-between py-2"><span class="text-muted"><i class="bi bi-calendar me-1 small"></i> Creado</span><span>{{ $pettyCash->created_at?->format('d/m/Y') }}</span></li>
                    </ul>
                </div>
            </div>

            @if($pettyCash->active)
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 px-4">
                        <h5 class="fw-bold mb-0"><i class="bi bi-plus-circle me-2 text-success"></i>Nuevo movimiento</h5>
                    </div>
                    <div class="card-body px-4 pt-3">
                        <form action="{{ route('petty-cash.add-movement', $pettyCash) }}" method="POST" class="row g-2">
                            @csrf
                            <div class="col-12">
                                <label class="form-label small text-muted mb-1">Tipo</label>
                                <select name="type" class="form-select form-select-sm @error('type') is-invalid @enderror" required>
                                    <option value="expense">Gasto</option>
                                    <option value="replenishment">Reposición</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small text-muted mb-1">Concepto</label>
                                <input type="text" name="concept" class="form-control form-control-sm @error('concept') is-invalid @enderror" placeholder="Descripción del movimiento" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small text-muted mb-1">Monto</label>
                                <input type="number" step="0.01" min="0.01" name="amount" class="form-control form-control-sm @error('amount') is-invalid @enderror" placeholder="0.00" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small text-muted mb-1">Fecha</label>
                                <input type="date" name="movement_date" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small text-muted mb-1">No. recibo</label>
                                <input type="text" name="receipt_number" class="form-control form-control-sm" placeholder="Opcional">
                            </div>
                            <div class="col-12 mt-2">
                                <button class="btn btn-sm btn-dark w-100" type="submit"><i class="bi bi-plus-lg me-1"></i> Registrar</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>

        {{-- Movements table --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold mb-0"><i class="bi bi-arrow-left-right me-2 text-primary"></i>Movimientos</h5>
                </div>
                <div class="card-body p-0 pt-3">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Fecha</th>
                                    <th>Tipo</th>
                                    <th>Concepto</th>
                                    <th>Recibo</th>
                                    <th class="text-end">Monto</th>
                                    <th class="pe-4">Registrado por</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($movements as $m)
                                    <tr>
                                        <td class="ps-4">{{ $m->movement_date?->format('d/m/Y') }}</td>
                                        <td>
                                            @if($m->type === 'expense')
                                                <span class="badge bg-danger"><i class="bi bi-arrow-up-right me-1"></i>Gasto</span>
                                            @else
                                                <span class="badge bg-success"><i class="bi bi-arrow-down-left me-1"></i>Reposición</span>
                                            @endif
                                        </td>
                                        <td>{{ $m->concept }}</td>
                                        <td><small class="text-muted">{{ $m->receipt_number ?: '—' }}</small></td>
                                        <td class="text-end fw-semibold {{ $m->type === 'expense' ? 'text-danger' : 'text-success' }}">
                                            {{ $m->type === 'expense' ? '-' : '+' }}${{ number_format($m->amount, 2) }}
                                        </td>
                                        <td class="pe-4">{{ $m->createdBy?->name }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-1 d-block mb-2"></i>No hay movimientos registrados</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="mt-3 d-flex justify-content-center">{{ $movements->links() }}</div>
        </div>
    </div>
</div>
@endsection
