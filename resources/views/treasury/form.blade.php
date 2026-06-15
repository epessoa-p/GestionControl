<form action="{{ $action }}" method="POST">
    @csrf
    @if($method === 'PUT') @method('PUT') @endif

    <div class="row g-4">
        {{-- Datos principales --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-2 px-4">
                    <h6 class="fw-bold mb-0">Información de la cuenta</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold small">Nombre de la cuenta <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $account?->name) }}" placeholder="Ej: Banco Ganadero, Caja Principal" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Tipo de cuenta <span class="text-danger">*</span></label>
                            <select name="type" id="accountType" class="form-select @error('type') is-invalid @enderror" required>
                                @foreach(\App\Models\TreasuryAccount::TYPE_LABELS as $val => $label)
                                    <option value="{{ $val }}" {{ old('type', $account?->type ?? 'efectivo') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Campos banco (visibles solo cuando tipo = banco) --}}
                        <div class="col-md-6" id="bankFields" style="{{ old('type', $account?->type ?? 'efectivo') === 'banco' ? '' : 'display:none' }}">
                            <label class="form-label fw-semibold small">Nombre del banco</label>
                            <input type="text" name="bank_name" class="form-control form-control-sm"
                                   value="{{ old('bank_name', $account?->bank_name) }}" placeholder="Ej: Banco del Pacífico">
                        </div>
                        <div class="col-md-6" id="accountNumberField" style="{{ old('type', $account?->type ?? 'efectivo') === 'banco' ? '' : 'display:none' }}">
                            <label class="form-label fw-semibold small">Número de cuenta</label>
                            <input type="text" name="account_number" class="form-control form-control-sm"
                                   value="{{ old('account_number', $account?->account_number) }}" placeholder="Ej: 2200123456">
                        </div>

                        {{-- Saldo inicial (solo en creación) --}}
                        @if(!$account)
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Saldo inicial</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">$</span>
                                <input type="number" name="initial_balance" class="form-control @error('initial_balance') is-invalid @enderror"
                                       value="{{ old('initial_balance', '0.00') }}" min="0" step="0.01">
                                @error('initial_balance')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-text text-muted">Solo se establece al crear la cuenta.</div>
                        </div>
                        @endif

                        <div class="col-md-{{ $account ? '6' : '6' }}">
                            <label class="form-label fw-semibold small">Color</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="color" name="color" id="colorPicker"
                                       class="form-control form-control-color form-control-sm"
                                       value="{{ old('color', $account?->color ?? '#3b82f6') }}"
                                       title="Color de la cuenta">
                                <span class="text-muted small">Color identificador</span>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small">Notas</label>
                            <textarea name="notes" class="form-control form-control-sm" rows="3"
                                      placeholder="Información adicional sobre la cuenta...">{{ old('notes', $account?->notes) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Estado + Guardar --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-bottom py-2 px-4">
                    <h6 class="fw-bold mb-0 small">Estado</h6>
                </div>
                <div class="card-body p-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="active" id="activeToggle" value="1"
                               {{ old('active', $account?->active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="activeToggle">Cuenta activa</label>
                    </div>
                    <div class="text-muted small mt-1">Las cuentas inactivas no aparecen en el balance total.</div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <button type="submit" class="btn btn-primary w-100 mb-2">
                        <i class="bi bi-check-lg me-1"></i> {{ $account ? 'Guardar cambios' : 'Crear cuenta' }}
                    </button>
                    <a href="{{ $account ? route('treasury.show', $account) : route('treasury.index') }}" class="btn btn-outline-secondary w-100">
                        Cancelar
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
document.getElementById('accountType').addEventListener('change', function () {
    const isBanco = this.value === 'banco';
    document.getElementById('bankFields').style.display        = isBanco ? '' : 'none';
    document.getElementById('accountNumberField').style.display = isBanco ? '' : 'none';

    // Auto-set color based on type
    const colors = { banco: '#3b82f6', efectivo: '#16a34a', otro: '#6b7280' };
    document.getElementById('colorPicker').value = colors[this.value] || '#3b82f6';
});
</script>
@endpush
