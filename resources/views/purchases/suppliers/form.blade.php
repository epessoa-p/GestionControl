<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><i class="bi bi-truck text-primary me-2"></i>{{ $supplier ? 'Editar proveedor' : 'Nuevo proveedor' }}</h1>
            <p class="text-muted mb-0">{{ $supplier ? $supplier->supplier_number : 'Registra un nuevo proveedor' }}</p>
        </div>
        <a href="{{ route('purchases.suppliers.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>

    <form action="{{ $action }}" method="POST" class="row g-4" id="supplierForm">
        @csrf
        @if($method !== 'POST') @method($method) @endif

        <div class="col-lg-8">
            {{-- Identificación --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-2 px-4">
                    <h6 class="fw-bold mb-0"><i class="bi bi-person-vcard me-1 text-primary"></i> Identificación</h6>
                </div>
                <div class="card-body p-4 pt-3 row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Tipo <span class="text-danger">*</span></label>
                        <div class="d-flex gap-3">
                            @foreach(\App\Models\Supplier::TYPE_LABELS as $val => $label)
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="type" id="type_{{ $val }}" value="{{ $val }}"
                                           {{ old('type', $supplier?->type ?? 'empresa') === $val ? 'checked' : '' }}>
                                    <label class="form-check-label" for="type_{{ $val }}">
                                        <i class="bi {{ \App\Models\Supplier::TYPE_ICONS[$val] }} me-1"></i>{{ $label }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Nombre / Razón social <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-sm @error('name') is-invalid @enderror"
                               value="{{ old('name', $supplier?->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Nombre comercial</label>
                        <input type="text" name="commercial_name" class="form-control form-control-sm"
                               value="{{ old('commercial_name', $supplier?->commercial_name) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Tipo de documento</label>
                        <select name="document_type" class="form-select form-select-sm">
                            <option value="">Sin especificar</option>
                            @foreach(\App\Models\Supplier::DOCUMENT_LABELS as $val => $label)
                                <option value="{{ $val }}" {{ old('document_type', $supplier?->document_type) === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold small">Número de documento</label>
                        <input type="text" name="document_number" class="form-control form-control-sm @error('document_number') is-invalid @enderror"
                               value="{{ old('document_number', $supplier?->document_number) }}">
                        @error('document_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            {{-- Contacto --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-2 px-4">
                    <h6 class="fw-bold mb-0"><i class="bi bi-telephone me-1 text-primary"></i> Contacto</h6>
                </div>
                <div class="card-body p-4 pt-3 row g-3">
                    <div class="col-md-12">
                        <label class="form-label fw-semibold small">Email</label>
                        <input type="email" name="email" class="form-control form-control-sm" value="{{ old('email', $supplier?->email) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Teléfono</label>
                        <input type="text" name="phone" class="form-control form-control-sm" value="{{ old('phone', $supplier?->phone) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Móvil</label>
                        <input type="text" name="mobile" class="form-control form-control-sm" value="{{ old('mobile', $supplier?->mobile) }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Dirección</label>
                        <textarea name="address" class="form-control form-control-sm" rows="2">{{ old('address', $supplier?->address) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Ciudad</label>
                        <input type="text" name="city" class="form-control form-control-sm" value="{{ old('city', $supplier?->city) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">País</label>
                        <input type="text" name="country" class="form-control form-control-sm" value="{{ old('country', $supplier?->country ?? 'Ecuador') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Persona de contacto</label>
                        <input type="text" name="contact_name" class="form-control form-control-sm" value="{{ old('contact_name', $supplier?->contact_name) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Email de contacto</label>
                        <input type="email" name="contact_email" class="form-control form-control-sm" value="{{ old('contact_email', $supplier?->contact_email) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Teléfono de contacto</label>
                        <input type="text" name="contact_phone" class="form-control form-control-sm" value="{{ old('contact_phone', $supplier?->contact_phone) }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- Condiciones comerciales --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-2 px-3">
                    <h6 class="fw-bold mb-0 small"><i class="bi bi-cash me-1 text-primary"></i> Condiciones comerciales</h6>
                </div>
                <div class="card-body p-3 row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Plazo de pago (días)</label>
                        <input type="number" name="payment_terms" class="form-control form-control-sm"
                               value="{{ old('payment_terms', $supplier?->payment_terms ?? 0) }}" min="0" max="365">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Límite de crédito ($)</label>
                        <input type="number" name="credit_limit" class="form-control form-control-sm"
                               value="{{ old('credit_limit', $supplier?->credit_limit ?? 0) }}" min="0" step="0.01">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Estado <span class="text-danger">*</span></label>
                        <select name="status" class="form-select form-select-sm" required>
                            @foreach(\App\Models\Supplier::STATUS_LABELS as $val => $label)
                                <option value="{{ $val }}" {{ old('status', $supplier?->status ?? 'activo') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Notas --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-2 px-3">
                    <h6 class="fw-bold mb-0 small"><i class="bi bi-sticky me-1 text-primary"></i> Notas</h6>
                </div>
                <div class="card-body p-3">
                    <textarea name="notes" class="form-control form-control-sm border-0 bg-light" rows="4"
                              placeholder="Observaciones...">{{ old('notes', $supplier?->notes) }}</textarea>
                </div>
            </div>

            <div class="d-flex flex-column gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> {{ $supplier ? 'Guardar cambios' : 'Registrar proveedor' }}
                </button>
                <a href="{{ route('purchases.suppliers.index') }}" class="btn btn-light border text-center">Cancelar</a>
            </div>
        </div>
    </form>
</div>
