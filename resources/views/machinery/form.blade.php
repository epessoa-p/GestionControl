<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">
                <i class="bi bi-tools text-primary me-2"></i>
                {{ $machine ? 'Editar maquinaria' : 'Nueva maquinaria' }}
            </h1>
            <p class="text-muted mb-0">Registra un activo productivo para calcular su depreciación automáticamente.</p>
        </div>
        <a href="{{ route('machinery.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="{{ $action }}" method="POST">
                @csrf
                @if($method !== 'POST') @method($method) @endif

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $machine?->name) }}"
                               placeholder="Ej: Mezcladora industrial modelo X" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Costo de adquisición <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" min="0" name="cost"
                                   class="form-control @error('cost') is-invalid @enderror"
                                   value="{{ old('cost', $machine?->cost ?? '') }}"
                                   placeholder="0.00" required>
                        </div>
                        @error('cost')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Vida útil (meses) <span class="text-danger">*</span></label>
                        <input type="number" min="1" name="useful_life_months"
                               class="form-control @error('useful_life_months') is-invalid @enderror"
                               value="{{ old('useful_life_months', $machine?->useful_life_months ?? 12) }}"
                               placeholder="12" required>
                        @error('useful_life_months')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Fecha de compra <span class="text-danger">*</span></label>
                        <input type="date" name="purchase_date"
                               class="form-control @error('purchase_date') is-invalid @enderror"
                               value="{{ old('purchase_date', $machine?->purchase_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                               required>
                        @error('purchase_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        {{-- Preview de depreciación mensual --}}
                        <div class="w-100 px-3 py-2 rounded-3 border bg-light text-center">
                            <div class="text-muted small">Depr. mensual estimada</div>
                            <div class="fs-5 fw-bold text-success" id="deprPreview">$0.00</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Estado</label>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="active" value="1" id="activeSwitch"
                                   {{ old('active', $machine?->active ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="activeSwitch">Activa</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Descripción</label>
                        <textarea name="description" class="form-control" rows="2"
                                  placeholder="Detalles técnicos, ubicación, etc.">{{ old('description', $machine?->description) }}</textarea>
                    </div>
                </div>

                <div class="d-flex gap-2 pt-2 border-top">
                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-check-lg me-1"></i> Guardar
                    </button>
                    <a href="{{ route('machinery.index') }}" class="btn btn-light border">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const costInput = document.querySelector('input[name="cost"]');
    const lifeInput = document.querySelector('input[name="useful_life_months"]');
    const preview   = document.getElementById('deprPreview');

    function updatePreview() {
        const cost = parseFloat(costInput.value) || 0;
        const life = parseInt(lifeInput.value)   || 1;
        preview.textContent = '$' + (cost / life).toFixed(2);
    }

    costInput?.addEventListener('input', updatePreview);
    lifeInput?.addEventListener('input', updatePreview);
    updatePreview();
})();
</script>
@endpush
