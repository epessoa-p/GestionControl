<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">
                <i class="bi bi-person-plus text-primary me-2"></i>
                {{ $client ? 'Editar cliente' : 'Nuevo cliente' }}
            </h1>
            <p class="text-muted mb-0">
                {{ $client ? 'Modifica los datos del cliente ' . $client->client_number : 'Registra un nuevo cliente o prospecto' }}
            </p>
        </div>
        <a href="{{ route('crm.clients.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>

    <form action="{{ $action }}" method="POST" class="row g-4" id="clientForm">
        @csrf
        @if($method !== 'POST') @method($method) @endif

        {{-- ── Columna izquierda ──────────────────────────── --}}
        <div class="col-lg-8">

            {{-- Sección: Identificación --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 px-4">
                    <h6 class="fw-bold text-primary mb-0"><i class="bi bi-person-vcard me-1"></i> Identificación</h6>
                </div>
                <div class="card-body p-4 pt-3 row g-3">

                    {{-- Tipo de cliente --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold">Tipo <span class="text-danger">*</span></label>
                        <div class="d-flex gap-3">
                            @foreach(\App\Models\Client::TYPE_LABELS as $val => $label)
                                <div class="form-check form-check-inline client-type-card {{ old('type', $client?->type ?? 'persona_natural') === $val ? 'selected' : '' }}"
                                     data-type="{{ $val }}">
                                    <input class="form-check-input visually-hidden" type="radio"
                                           name="type" id="type_{{ $val }}" value="{{ $val }}"
                                           {{ old('type', $client?->type ?? 'persona_natural') === $val ? 'checked' : '' }}>
                                    <label class="form-check-label" for="type_{{ $val }}">
                                        <i class="bi {{ \App\Models\Client::TYPE_ICONS[$val] }} me-2"></i>{{ $label }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Nombre --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" id="nameLabel">Nombre completo <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $client?->name) }}" required placeholder="Nombre del cliente">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Nombre comercial (solo empresa) --}}
                    <div class="col-md-6" id="commercialNameWrap">
                        <label class="form-label fw-semibold">Razón social / Nombre comercial</label>
                        <input type="text" name="commercial_name" class="form-control"
                               value="{{ old('commercial_name', $client?->commercial_name) }}"
                               placeholder="Nombre con el que opera">
                    </div>

                    {{-- Documento --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Tipo de documento</label>
                        <select name="document_type" class="form-select">
                            <option value="">Sin especificar</option>
                            @foreach(\App\Models\Client::DOCUMENT_LABELS as $val => $label)
                                <option value="{{ $val }}" {{ old('document_type', $client?->document_type) === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Número de documento</label>
                        <input type="text" name="document_number" class="form-control @error('document_number') is-invalid @enderror"
                               value="{{ old('document_number', $client?->document_number) }}" placeholder="Ej: 1712345678">
                        @error('document_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            {{-- Sección: Contacto --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 px-4">
                    <h6 class="fw-bold text-primary mb-0"><i class="bi bi-telephone me-1"></i> Contacto</h6>
                </div>
                <div class="card-body p-4 pt-3 row g-3">
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Correo electrónico</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $client?->email) }}" placeholder="correo@empresa.com">
                        </div>
                        @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Teléfono fijo</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                            <input type="text" name="phone" class="form-control"
                                   value="{{ old('phone', $client?->phone) }}" placeholder="02-2345678">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Móvil / WhatsApp</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-phone"></i></span>
                            <input type="text" name="mobile" class="form-control"
                                   value="{{ old('mobile', $client?->mobile) }}" placeholder="0991234567">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sección: Ubicación --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 px-4">
                    <h6 class="fw-bold text-primary mb-0"><i class="bi bi-geo-alt me-1"></i> Ubicación</h6>
                </div>
                <div class="card-body p-4 pt-3 row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Dirección</label>
                        <textarea name="address" class="form-control" rows="2"
                                  placeholder="Calle, número, sector...">{{ old('address', $client?->address) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Ciudad</label>
                        <input type="text" name="city" class="form-control"
                               value="{{ old('city', $client?->city) }}" placeholder="Quito">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">País</label>
                        <input type="text" name="country" class="form-control"
                               value="{{ old('country', $client?->country ?? 'Ecuador') }}" placeholder="Ecuador">
                    </div>
                </div>
            </div>

            {{-- Sección: Contactos adicionales (empresa) --}}
            <div class="card border-0 shadow-sm mb-4" id="contactsSection">
                <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 px-4 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-primary mb-0"><i class="bi bi-people me-1"></i> Personas de contacto</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addContactBtn">
                        <i class="bi bi-plus-lg me-1"></i> Agregar
                    </button>
                </div>
                <div class="card-body p-4 pt-3">
                    <div id="contactsContainer">
                        @php $existingContacts = old('contacts', $client?->contacts?->toArray() ?? []); @endphp
                        @if(count($existingContacts) > 0)
                            @foreach($existingContacts as $i => $contact)
                                @include('crm.clients._contact_row', ['i' => $i, 'contact' => $contact])
                            @endforeach
                        @else
                            @include('crm.clients._contact_row', ['i' => 0, 'contact' => []])
                        @endif
                    </div>
                    <p class="text-muted small mt-2 mb-0">
                        <i class="bi bi-info-circle me-1"></i>Agrega las personas de contacto dentro de la empresa cliente.
                    </p>
                </div>
            </div>
        </div>

        {{-- ── Columna derecha ──────────────────────────────── --}}
        <div class="col-lg-4">

            {{-- Número (readonly) --}}
            @if(!$client)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <label class="form-label fw-semibold text-muted small text-uppercase">Número de cliente</label>
                        <div class="fs-4 fw-bold text-primary">{{ $clientNumber ?? '—' }}</div>
                        <small class="text-muted">Asignado automáticamente</small>
                    </div>
                </div>
            @endif

            {{-- CRM --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 px-4">
                    <h6 class="fw-bold text-primary mb-0"><i class="bi bi-diagram-3 me-1"></i> CRM</h6>
                </div>
                <div class="card-body p-4 pt-3 row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Estado <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            @foreach(\App\Models\Client::STATUS_LABELS as $val => $label)
                                <option value="{{ $val }}" {{ old('status', $client?->status ?? 'prospecto') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Origen de captación</label>
                        <select name="source" class="form-select">
                            <option value="">Sin especificar</option>
                            @foreach(\App\Models\Client::SOURCE_LABELS as $val => $label)
                                <option value="{{ $val }}" {{ old('source', $client?->source) === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Asesor asignado</label>
                        <select name="assigned_to" class="form-select">
                            <option value="">Sin asignar</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ (string)old('assigned_to', $client?->assigned_to) === (string)$u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Notas --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 px-4">
                    <h6 class="fw-bold text-primary mb-0"><i class="bi bi-sticky me-1"></i> Notas</h6>
                </div>
                <div class="card-body p-4 pt-3">
                    <textarea name="notes" class="form-control border-0 bg-light" rows="5"
                              placeholder="Observaciones generales sobre el cliente...">{{ old('notes', $client?->notes) }}</textarea>
                </div>
            </div>

            {{-- Acciones --}}
            <div class="d-flex flex-column gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> {{ $client ? 'Guardar cambios' : 'Registrar cliente' }}
                </button>
                <a href="{{ route('crm.clients.index') }}" class="btn btn-light border text-center">Cancelar</a>
            </div>
        </div>
    </form>
</div>

{{-- Template fila de contacto (oculto) --}}
<template id="contactRowTemplate">
    @include('crm.clients._contact_row', ['i' => '__IDX__', 'contact' => []])
</template>

@push('styles')
<style>
    .client-type-card {
        border: 2px solid #dee2e6; border-radius: 10px;
        padding: 10px 20px; cursor: pointer; transition: all 0.15s;
        background: #fff;
    }
    .client-type-card:hover { border-color: #0d6efd; background: #f0f5ff; }
    .client-type-card.selected { border-color: #0d6efd; background: #e8f0fe; font-weight: 600; }
    .client-type-card label { cursor: pointer; }
</style>
@endpush

@push('scripts')
<script>
(function () {
    // ─── Tipo de cliente: toggle razón social + sección contactos
    const typeCards = document.querySelectorAll('.client-type-card');
    const commercialWrap  = document.getElementById('commercialNameWrap');
    const contactsSection = document.getElementById('contactsSection');
    const nameLabel       = document.getElementById('nameLabel');

    function applyType(type) {
        const isEmpresa = type === 'empresa';
        commercialWrap.style.display  = isEmpresa ? '' : 'none';
        contactsSection.style.display = isEmpresa ? '' : 'none';
        nameLabel.innerHTML = isEmpresa
            ? 'Nombre del representante <span class="text-danger">*</span>'
            : 'Nombre completo <span class="text-danger">*</span>';
    }

    typeCards.forEach(card => {
        card.addEventListener('click', function () {
            typeCards.forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');
            this.querySelector('input[type=radio]').checked = true;
            applyType(this.dataset.type);
        });
    });

    // Aplicar estado inicial
    const checkedType = document.querySelector('input[name="type"]:checked')?.value || 'persona_natural';
    applyType(checkedType);

    // ─── Agregar fila de contacto
    let contactIdx = document.querySelectorAll('.contact-row').length;

    document.getElementById('addContactBtn')?.addEventListener('click', function () {
        const tpl = document.getElementById('contactRowTemplate').innerHTML
            .replace(/__IDX__/g, contactIdx++);
        const div = document.createElement('div');
        div.innerHTML = tpl;
        document.getElementById('contactsContainer').appendChild(div.firstElementChild);
        bindRemove();
    });

    function bindRemove() {
        document.querySelectorAll('.remove-contact-btn').forEach(btn => {
            btn.onclick = function () {
                this.closest('.contact-row').remove();
            };
        });
    }
    bindRemove();
})();
</script>
@endpush
