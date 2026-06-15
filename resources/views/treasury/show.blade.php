@extends('layouts.app')
@section('title', $account->name . ' — Tesorería')
@section('page')
@php
    $typeColor = \App\Models\TreasuryAccount::TYPE_COLORS[$account->type] ?? '#6b7280';
    $typeIcon  = \App\Models\TreasuryAccount::TYPE_ICONS[$account->type] ?? 'wallet';
    $typeLabel = \App\Models\TreasuryAccount::TYPE_LABELS[$account->type] ?? $account->type;
    $cats      = \App\Models\TreasuryMovement::CATEGORIES;
@endphp
<div class="container-fluid">

    {{-- Header --}}
    <div class="card border-0 shadow-sm mb-4" style="border-top: 5px solid {{ $typeColor }} !important;">
        <div class="card-body p-4">
            <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:56px;height:56px;background:{{ $typeColor }}20;">
                        <i class="bi bi-{{ $typeIcon }}" style="color:{{ $typeColor }};font-size:1.5rem;"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h1 class="mb-0 fs-3">{{ $account->name }}</h1>
                            <span class="badge {{ $account->active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                {{ $account->active ? 'Activa' : 'Inactiva' }}
                            </span>
                        </div>
                        <div class="text-muted small">{{ $typeLabel }}
                            @if($account->bank_name) · {{ $account->bank_name }} @endif
                            @if($account->account_number) · ••••{{ substr($account->account_number, -4) }} @endif
                        </div>
                    </div>
                </div>
                <div class="text-end">
                    <div class="text-muted small">Saldo actual</div>
                    <div class="display-6 fw-bold" style="color:{{ $typeColor }}">
                        ${{ number_format($account->current_balance, 2) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if(session('error'))<div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    {{-- Action buttons --}}
    <div class="d-flex gap-2 mb-4 flex-wrap">
        <button class="btn btn-primary" id="toggleMovForm">
            <i class="bi bi-plus-lg me-1"></i> Registrar movimiento
        </button>
        <a href="{{ route('treasury.edit', $account) }}" class="btn btn-outline-secondary">
            <i class="bi bi-pencil me-1"></i> Editar
        </a>
        <a href="{{ route('treasury.index') }}" class="btn btn-outline-secondary ms-auto">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>

    {{-- Inline form: Registrar movimiento --}}
    <div id="movFormCard" class="card border-0 shadow-sm mb-4" style="display:none">
        <div class="card-header bg-white border-bottom py-2 px-4">
            <h6 class="fw-bold mb-0 small"><i class="bi bi-plus-circle me-1 text-primary"></i> Nuevo movimiento</h6>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('treasury.movements.store', $account) }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Tipo <span class="text-danger">*</span></label>
                        <select name="type" id="movType" class="form-select form-select-sm" required>
                            <option value="entrada" {{ old('type') === 'entrada' ? 'selected' : '' }}>Entrada</option>
                            <option value="salida"  {{ old('type') === 'salida'  ? 'selected' : '' }}>Salida</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Categoría <span class="text-danger">*</span></label>
                        <select name="category" id="movCategory" class="form-select form-select-sm" required>
                            @foreach($cats as $key => $cat)
                                <option value="{{ $key }}" data-mov-type="{{ $cat['type'] }}"
                                    {{ old('category') === $key ? 'selected' : '' }}>
                                    {{ $cat['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small">Monto <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">$</span>
                            <input type="number" name="amount" class="form-control" min="0.01" step="0.01"
                                   value="{{ old('amount') }}" required>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small">Fecha <span class="text-danger">*</span></label>
                        <input type="date" name="movement_date" class="form-control form-control-sm"
                               value="{{ old('movement_date', now()->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small">Referencia</label>
                        <input type="text" name="reference" class="form-control form-control-sm"
                               value="{{ old('reference') }}" placeholder="Nº transf., factura...">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Descripción</label>
                        <input type="text" name="description" class="form-control form-control-sm"
                               value="{{ old('description') }}" placeholder="Detalle del movimiento...">
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Registrar
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="cancelMovForm">Cancelar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Historial de movimientos --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-2 px-4 d-flex align-items-center justify-content-between">
            <h6 class="fw-bold mb-0">
                <i class="bi bi-clock-history me-1 text-primary"></i> Historial de movimientos
                <span class="badge bg-secondary ms-1">{{ $movements->total() }}</span>
            </h6>
        </div>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Categoría</th>
                        <th>Tipo</th>
                        <th class="text-end">Monto</th>
                        <th>Descripción</th>
                        <th>Usuario</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $mov)
                    <tr>
                        <td class="text-muted small">{{ $mov->movement_date?->format('d/m/Y') }}</td>
                        <td>{{ $mov->category_label }}</td>
                        <td>
                            <span class="badge {{ $mov->type === 'entrada' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                <i class="bi bi-arrow-{{ $mov->type === 'entrada' ? 'down' : 'up' }}-circle me-1"></i>
                                {{ $mov->type === 'entrada' ? 'Entrada' : 'Salida' }}
                            </span>
                        </td>
                        <td class="text-end fw-semibold {{ $mov->type === 'entrada' ? 'text-success' : 'text-danger' }}">
                            {{ $mov->type === 'entrada' ? '+' : '-' }}${{ number_format($mov->amount, 2) }}
                        </td>
                        <td class="text-muted small">{{ $mov->description ?? ($mov->reference ? '# '.$mov->reference : '—') }}</td>
                        <td class="text-muted small">{{ $mov->createdBy?->name ?? '—' }}</td>
                        <td>
                            <form action="{{ route('treasury.movements.destroy', [$account, $mov]) }}" method="POST"
                                  onsubmit="return confirm('¿Eliminar este movimiento? El saldo se recalculará.')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-link text-danger p-0"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Sin movimientos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($movements->hasPages())
        <div class="card-footer bg-white border-top px-4 py-2">
            {{ $movements->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
const toggleBtn   = document.getElementById('toggleMovForm');
const cancelBtn   = document.getElementById('cancelMovForm');
const formCard    = document.getElementById('movFormCard');
const movType     = document.getElementById('movType');
const movCategory = document.getElementById('movCategory');

toggleBtn.addEventListener('click', () => {
    formCard.style.display = formCard.style.display === 'none' ? '' : 'none';
    if (formCard.style.display !== 'none') formCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
});
cancelBtn.addEventListener('click', () => { formCard.style.display = 'none'; });

function filterCategories() {
    const type = movType.value;
    Array.from(movCategory.options).forEach(opt => {
        opt.hidden = opt.dataset.movType && opt.dataset.movType !== type;
    });
    // Select first visible
    const first = Array.from(movCategory.options).find(o => !o.hidden);
    if (first) movCategory.value = first.value;
}
movType.addEventListener('change', filterCategories);
filterCategories();
</script>
@endpush
@endsection
