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

    <form action="{{ $action }}" method="POST" enctype="multipart/form-data" class="row g-4" id="clientForm">
        @csrf
        @if($method !== 'POST') @method($method) @endif

        {{-- ── Columna izquierda ──────────────────────────── --}}
        <div class="col-lg-8">

            {{-- Identificación --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-2 px-4">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-person-vcard me-1 text-primary"></i> Identificación</h6>
                </div>
                <div class="card-body p-4 pt-3 row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Tipo <span class="text-danger">*</span></label>
                        <div class="d-flex gap-3 flex-wrap">
                            @foreach(\App\Models\Client::TYPE_LABELS as $val => $label)
                                <div class="client-type-card {{ old('type', $client?->type ?? 'persona_natural') === $val ? 'selected' : '' }}"
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
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small" id="nameLabel">Nombre completo <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-sm @error('name') is-invalid @enderror"
                               value="{{ old('name', $client?->name) }}" required placeholder="Nombre del cliente">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6" id="commercialNameWrap">
                        <label class="form-label fw-semibold small">Razón social / Nombre comercial</label>
                        <input type="text" name="commercial_name" class="form-control form-control-sm"
                               value="{{ old('commercial_name', $client?->commercial_name) }}" placeholder="Nombre comercial">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Tipo de documento</label>
                        <select name="document_type" class="form-select form-select-sm">
                            <option value="">Sin especificar</option>
                            @foreach(\App\Models\Client::DOCUMENT_LABELS as $val => $label)
                                <option value="{{ $val }}" {{ old('document_type', $client?->document_type) === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold small">Número de documento</label>
                        <input type="text" name="document_number" class="form-control form-control-sm @error('document_number') is-invalid @enderror"
                               value="{{ old('document_number', $client?->document_number) }}" placeholder="Ej: 1712345678">
                        @error('document_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            {{-- Contacto --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-2 px-4">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-telephone me-1 text-primary"></i> Contacto</h6>
                </div>
                <div class="card-body p-4 pt-3 row g-3">
                    <div class="col-md-12">
                        <label class="form-label fw-semibold small">Correo electrónico</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $client?->email) }}" placeholder="correo@empresa.com">
                        </div>
                        @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Teléfono fijo</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                            <input type="text" name="phone" class="form-control"
                                   value="{{ old('phone', $client?->phone) }}" placeholder="02-2345678">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Móvil / WhatsApp</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi bi-phone"></i></span>
                            <input type="text" name="mobile" class="form-control"
                                   value="{{ old('mobile', $client?->mobile) }}" placeholder="0991234567">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Ubicación + Mapa --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-2 px-4">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-geo-alt me-1 text-primary"></i> Ubicación</h6>
                </div>
                <div class="card-body p-4 pt-3 row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Dirección</label>
                        <textarea name="address" class="form-control form-control-sm" rows="2"
                                  placeholder="Calle, número, sector...">{{ old('address', $client?->address) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Ciudad</label>
                        <input type="text" name="city" class="form-control form-control-sm"
                               value="{{ old('city', $client?->city) }}" placeholder="Quito">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">País</label>
                        <input type="text" name="country" class="form-control form-control-sm"
                               value="{{ old('country', $client?->country ?? 'Ecuador') }}" placeholder="Ecuador">
                    </div>

                    {{-- Mapa interactivo --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold small d-flex align-items-center justify-content-between">
                            <span><i class="bi bi-map me-1"></i> Ubicación en mapa</span>
                            @if($client?->latitude)
                                <span class="badge bg-success-subtle text-success fw-normal" id="coordsBadge">
                                    {{ number_format((float)$client->latitude, 5) }}, {{ number_format((float)$client->longitude, 5) }}
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary fw-normal" id="coordsBadge" style="display:none!important"></span>
                            @endif
                        </label>
                        {{-- Barra de búsqueda --}}
                        <div class="map-search-bar mb-2 d-flex gap-2">
                            <div class="input-group input-group-sm flex-grow-1">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" id="mapSearchInput" class="form-control"
                                       placeholder="Buscar dirección en el mapa..." autocomplete="off">
                            </div>
                            <button type="button" id="mapGpsBtn" class="btn btn-sm btn-outline-primary" title="Usar mi ubicación">
                                <i class="bi bi-crosshair"></i> GPS
                            </button>
                        </div>
                        {{-- Sugerencias de búsqueda --}}
                        <div id="mapSuggestions" class="map-suggestions d-none"></div>
                        {{-- Mapa --}}
                        <div id="clientMap" class="client-map-wrap"></div>
                        <small class="text-muted mt-1 d-block">
                            <i class="bi bi-info-circle me-1"></i>Haz clic en el mapa o arrastra el marcador para fijar la ubicación.
                        </small>
                        {{-- Coords ocultos --}}
                        <input type="hidden" name="latitude"  id="mapLat"
                               value="{{ old('latitude',  $client?->latitude) }}">
                        <input type="hidden" name="longitude" id="mapLng"
                               value="{{ old('longitude', $client?->longitude) }}">
                    </div>
                </div>
            </div>

            {{-- Contactos adicionales --}}
            <div class="card border-0 shadow-sm mb-4" id="contactsSection">
                <div class="card-header bg-white border-bottom py-2 px-4 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-people me-1 text-primary"></i> Personas de contacto</h6>
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

            {{-- Foto del cliente --}}
            <div class="card border-0 shadow-sm mb-4 client-photo-card">
                <div class="card-header bg-white border-bottom py-2 px-3">
                    <h6 class="fw-bold mb-0 text-dark small"><i class="bi bi-person-bounding-box me-1 text-primary"></i> Foto del cliente</h6>
                </div>
                <div class="card-body p-3 text-center">
                    {{-- Preview --}}
                    <div class="photo-preview-wrap mb-3" id="photoPreviewWrap">
                        @if($client?->photo)
                            <img src="{{ $client->photoUrl() }}" alt="Foto" class="photo-preview-img" id="photoPreviewImg">
                            <div class="photo-preview-overlay">
                                <button type="button" class="btn btn-sm btn-light btn-change-photo">
                                    <i class="bi bi-arrow-repeat me-1"></i>Cambiar
                                </button>
                            </div>
                        @else
                            <div class="photo-placeholder" id="photoPlaceholder">
                                <i class="bi bi-person-bounding-box"></i>
                            </div>
                            <img src="" alt="" class="photo-preview-img d-none" id="photoPreviewImg">
                        @endif
                    </div>

                    {{-- Botones --}}
                    <div class="d-flex gap-2 justify-content-center mb-2">
                        <button type="button" id="photoUploadBtn" class="btn btn-sm btn-outline-primary px-3">
                            <i class="bi bi-upload me-1"></i> Subir
                        </button>
                        <button type="button" id="photoCameraBtn" class="btn btn-sm btn-outline-secondary px-3">
                            <i class="bi bi-camera me-1"></i> Cámara
                        </button>
                    </div>
                    <small class="text-muted d-block">JPG, PNG, WebP · máx. 5MB</small>

                    {{-- Input oculto --}}
                    <input type="file" name="photo" id="photoInput" accept="image/*" class="d-none">

                    {{-- Eliminar foto existente --}}
                    @if($client?->photo)
                        <button type="button" class="btn btn-sm btn-link text-danger p-0 mt-2"
                                onclick="confirmDeleteForm('deletePhotoForm', '¿Eliminar la foto del cliente?')">
                            <i class="bi bi-trash me-1"></i> Eliminar foto
                        </button>
                    @endif
                </div>
            </div>

            {{-- Documentos adjuntos --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-2 px-3">
                    <h6 class="fw-bold mb-0 text-dark small"><i class="bi bi-folder2-open me-1 text-primary"></i> Documentos adjuntos</h6>
                    <small class="text-muted">CI, facturas, contratos u otros archivos del cliente.</small>
                </div>
                <div class="card-body p-3">
                    <div class="docs-grid">
                        @foreach(\App\Models\ClientDocument::TYPES as $docType)
                            @php
                                $existingDoc = $client?->documentByType($docType);
                                $label = \App\Models\ClientDocument::TYPE_LABELS[$docType];
                                $icon  = \App\Models\ClientDocument::TYPE_ICONS[$docType];
                            @endphp
                            <div class="doc-card {{ $existingDoc ? 'doc-card--has-file' : '' }}" data-type="{{ $docType }}" id="docCard_{{ $docType }}">
                                @if($existingDoc)
                                    {{-- Documento existente --}}
                                    <a href="{{ $existingDoc->url() }}" target="_blank" class="doc-thumb-link">
                                        <img src="{{ $existingDoc->url() }}" alt="{{ $label }}" class="doc-thumb">
                                        <div class="doc-thumb-overlay"><i class="bi bi-eye fs-5"></i></div>
                                    </a>
                                    <div class="doc-card-footer">
                                        <span class="doc-card-label">{{ $label }}</span>
                                        <div class="d-flex gap-1 mt-1">
                                            <button type="button" class="btn btn-xs btn-outline-secondary flex-fill doc-replace-btn"
                                                    data-type="{{ $docType }}" title="Reemplazar">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </button>
                                            @if($client)
                                                <button type="button" class="btn btn-xs btn-outline-danger flex-fill"
                                                        onclick="confirmDeleteForm('deleteDocForm_{{ $docType }}', '¿Eliminar este documento?')"
                                                        title="Eliminar">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    {{-- Sin documento --}}
                                    <div class="doc-empty-icon"><i class="bi {{ $icon }}"></i></div>
                                    <div class="doc-card-label">{{ $label }}</div>
                                    <div class="doc-card-actions">
                                        <button type="button" class="btn btn-xs btn-outline-primary doc-upload-btn"
                                                data-type="{{ $docType }}">
                                            <i class="bi bi-upload me-1"></i>Subir
                                        </button>
                                        <button type="button" class="btn btn-xs btn-outline-secondary doc-camera-btn"
                                                data-type="{{ $docType }}">
                                            <i class="bi bi-camera"></i>
                                        </button>
                                    </div>
                                @endif
                                {{-- Input oculto por tipo --}}
                                <input type="file" name="documents[{{ $docType }}]"
                                       id="docInput_{{ $docType }}" accept="image/*" class="d-none">
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Número (solo create) --}}
            @if(!$client)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-3">
                        <label class="form-label fw-semibold text-muted small text-uppercase">Número de cliente</label>
                        <div class="fs-4 fw-bold text-primary">{{ $clientNumber ?? '—' }}</div>
                        <small class="text-muted">Asignado automáticamente</small>
                    </div>
                </div>
            @endif

            {{-- CRM --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-2 px-3">
                    <h6 class="fw-bold mb-0 text-dark small"><i class="bi bi-diagram-3 me-1 text-primary"></i> CRM</h6>
                </div>
                <div class="card-body p-3 row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Estado <span class="text-danger">*</span></label>
                        <select name="status" class="form-select form-select-sm @error('status') is-invalid @enderror" required>
                            @foreach(\App\Models\Client::STATUS_LABELS as $val => $label)
                                <option value="{{ $val }}" {{ old('status', $client?->status ?? 'prospecto') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Origen de captación</label>
                        <select name="source" class="form-select form-select-sm">
                            <option value="">Sin especificar</option>
                            @foreach(\App\Models\Client::SOURCE_LABELS as $val => $label)
                                <option value="{{ $val }}" {{ old('source', $client?->source) === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Asesor asignado</label>
                        <select name="assigned_to" class="form-select form-select-sm">
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
                <div class="card-header bg-white border-bottom py-2 px-3">
                    <h6 class="fw-bold mb-0 text-dark small"><i class="bi bi-sticky me-1 text-primary"></i> Notas</h6>
                </div>
                <div class="card-body p-3">
                    <textarea name="notes" class="form-control form-control-sm border-0 bg-light" rows="4"
                              placeholder="Observaciones generales...">{{ old('notes', $client?->notes) }}</textarea>
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

{{-- Template fila de contacto --}}
<template id="contactRowTemplate">
    @include('crm.clients._contact_row', ['i' => '__IDX__', 'contact' => []])
</template>

{{-- Formularios de eliminación (fuera del form principal para evitar anidación) --}}
@if($client?->photo)
    <form id="deletePhotoForm" action="{{ route('crm.clients.photo.destroy', $client) }}" method="POST" class="d-none">
        @csrf @method('DELETE')
    </form>
@endif
@if($client)
    @foreach(\App\Models\ClientDocument::TYPES as $_docType)
        @php $_doc = $client->documentByType($_docType); @endphp
        @if($_doc)
            <form id="deleteDocForm_{{ $_docType }}"
                  action="{{ route('crm.clients.documents.destroy', [$client, $_doc]) }}"
                  method="POST" class="d-none">
                @csrf @method('DELETE')
            </form>
        @endif
    @endforeach
@endif

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
    /* ── Tipo de cliente ─────────────────────────────────── */
    .client-type-card {
        border: 2px solid #dee2e6; border-radius: 10px;
        padding: 8px 18px; cursor: pointer; transition: all .15s;
        background: #fff;
    }
    .client-type-card:hover  { border-color: #0d6efd; background: #f0f5ff; }
    .client-type-card.selected { border-color: #0d6efd; background: #e8f0fe; font-weight: 600; }
    .client-type-card label  { cursor: pointer; }

    /* ── Foto del cliente ────────────────────────────────── */
    .photo-preview-wrap {
        position: relative;
        width: 110px; height: 110px;
        margin: 0 auto;
        border-radius: 50%;
        overflow: hidden;
        background: #f0f5ff;
        border: 3px solid #e8f0fe;
    }
    .photo-preview-img {
        width: 100%; height: 100%; object-fit: cover;
        border-radius: 50%;
    }
    .photo-placeholder {
        width: 100%; height: 100%;
        display: flex; align-items: center; justify-content: center;
        font-size: 2.8rem; color: #aac4ff;
    }
    .photo-preview-overlay {
        position: absolute; inset: 0;
        background: rgba(0,0,0,.45);
        display: flex; align-items: center; justify-content: center;
        opacity: 0; transition: opacity .2s;
        border-radius: 50%;
    }
    .photo-preview-wrap:hover .photo-preview-overlay { opacity: 1; }

    /* ── Documentos ──────────────────────────────────────── */
    .docs-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }
    .doc-card {
        border: 2px dashed #dee2e6;
        border-radius: 12px;
        padding: 12px 8px;
        text-align: center;
        cursor: pointer;
        transition: border-color .15s, background .15s;
        background: #fafbfc;
        min-height: 110px;
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        gap: 6px;
    }
    .doc-card:hover         { border-color: #0d6efd; background: #f0f5ff; }
    .doc-card--has-file     { border-style: solid; border-color: #b0c4ff; padding: 0; overflow: hidden; background: #fff; }
    .doc-card--new-preview  { border-style: solid; border-color: #198754; background: #f0faf4; }
    .doc-empty-icon         { font-size: 1.6rem; color: #adb5bd; }
    .doc-card-label         { font-size: .75rem; font-weight: 600; color: #666; }
    .doc-card-actions       { display: flex; gap: 4px; margin-top: 2px; }
    .doc-thumb-link         { display: block; position: relative; width: 100%; height: 76px; overflow: hidden; }
    .doc-thumb              { width: 100%; height: 76px; object-fit: cover; }
    .doc-thumb-overlay      {
        position: absolute; inset: 0;
        background: rgba(0,0,0,.4);
        display: flex; align-items: center; justify-content: center;
        color: #fff; opacity: 0; transition: opacity .2s;
    }
    .doc-thumb-link:hover .doc-thumb-overlay { opacity: 1; }
    .doc-card--has-file { position: relative; }
    .doc-card-footer    { padding: 6px 8px; width: 100%; background: #f8f9fa; }
    .btn-xs             { padding: 2px 6px; font-size: .72rem; }

    /* ── Mapa ────────────────────────────────────────────── */
    .client-map-wrap {
        height: 290px;
        border-radius: 12px;
        overflow: hidden;
        border: 2px solid #dee2e6;
        position: relative;
        z-index: 0;
    }
    .map-search-bar .form-control { font-size: .85rem; }
    .map-suggestions {
        position: absolute;
        z-index: 9999;
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        box-shadow: 0 4px 16px rgba(0,0,0,.1);
        max-height: 200px;
        overflow-y: auto;
        width: 100%;
    }
    .map-suggestion-item {
        padding: 8px 14px; font-size: .84rem; cursor: pointer;
        border-bottom: 1px solid #f0f0f0;
    }
    .map-suggestion-item:hover { background: #f0f5ff; }
    .map-suggestion-item:last-child { border-bottom: none; }
    .map-search-bar { position: relative; }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
function confirmDeleteForm(formId, msg) {
    if (confirm(msg)) document.getElementById(formId).submit();
}

(function () {
    // ─── Tipo de cliente ──────────────────────────────────
    const typeCards        = document.querySelectorAll('.client-type-card');
    const commercialWrap   = document.getElementById('commercialNameWrap');
    const contactsSection  = document.getElementById('contactsSection');
    const nameLabel        = document.getElementById('nameLabel');

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
    applyType(document.querySelector('input[name="type"]:checked')?.value || 'persona_natural');

    // ─── Contactos adicionales ────────────────────────────
    let contactIdx = document.querySelectorAll('.contact-row').length;
    document.getElementById('addContactBtn')?.addEventListener('click', function () {
        const tpl = document.getElementById('contactRowTemplate').innerHTML.replace(/__IDX__/g, contactIdx++);
        const div = document.createElement('div');
        div.innerHTML = tpl;
        document.getElementById('contactsContainer').appendChild(div.firstElementChild);
        bindRemove();
    });
    function bindRemove() {
        document.querySelectorAll('.remove-contact-btn').forEach(btn => {
            btn.onclick = function () { this.closest('.contact-row').remove(); };
        });
    }
    bindRemove();

    // ─── Foto del cliente ─────────────────────────────────
    const photoInput    = document.getElementById('photoInput');
    const photoPreview  = document.getElementById('photoPreviewImg');
    const photoHolder   = document.getElementById('photoPlaceholder');
    const photoWrap     = document.getElementById('photoPreviewWrap');

    // Botón subir (exterior) + botón cambiar (overlay)
    document.querySelectorAll('#photoUploadBtn, .btn-change-photo').forEach(btn => {
        btn.addEventListener('click', () => {
            photoInput.removeAttribute('capture');
            photoInput.click();
        });
    });
    document.getElementById('photoCameraBtn')?.addEventListener('click', () => {
        photoInput.setAttribute('capture', 'user');
        photoInput.click();
        photoInput.addEventListener('change', function once() {
            photoInput.removeAttribute('capture');
            photoInput.removeEventListener('change', once);
        });
    });
    photoInput?.addEventListener('change', function () {
        if (!this.files[0]) return;
        const reader = new FileReader();
        reader.onload = e => {
            photoPreview.src = e.target.result;
            photoPreview.classList.remove('d-none');
            if (photoHolder) photoHolder.style.display = 'none';
        };
        reader.readAsDataURL(this.files[0]);
    });

    // ─── Documentos ──────────────────────────────────────
    document.querySelectorAll('.doc-upload-btn, .doc-camera-btn, .doc-replace-btn').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            const type    = this.dataset.type;
            const input   = document.getElementById('docInput_' + type);
            const isCamera = this.classList.contains('doc-camera-btn');

            if (isCamera) {
                input.setAttribute('capture', 'environment');
            } else {
                input.removeAttribute('capture');
            }
            input.click();
        });
    });

    // Click en la tarjeta vacía también abre el input
    document.querySelectorAll('.doc-card:not(.doc-card--has-file)').forEach(card => {
        card.addEventListener('click', function () {
            const type  = this.dataset.type;
            const input = document.getElementById('docInput_' + type);
            if (input) input.click();
        });
    });

    // Preview tras seleccionar
    document.querySelectorAll('[id^="docInput_"]').forEach(input => {
        input.addEventListener('change', function () {
            if (!this.files[0]) return;
            const type = this.id.replace('docInput_', '');
            const card = document.getElementById('docCard_' + type);
            const reader = new FileReader();
            reader.onload = e => {
                card.innerHTML = `
                    <div style="width:100%;height:76px;overflow:hidden;">
                        <img src="${e.target.result}" style="width:100%;height:76px;object-fit:cover;">
                    </div>
                    <div class="doc-card-footer">
                        <span class="doc-card-label text-success"><i class="bi bi-check-circle me-1"></i>Listo</span>
                    </div>`;
                card.classList.add('doc-card--has-file', 'doc-card--new-preview');
                // Re-attach the hidden input
                const newInput = document.createElement('input');
                newInput.type = 'file';
                newInput.name = `documents[${type}]`;
                newInput.id   = `docInput_${type}`;
                newInput.accept = 'image/*';
                newInput.className = 'd-none';
                card.appendChild(newInput);
                // Transferir el archivo al nuevo input
                const dt = new DataTransfer();
                dt.items.add(this.files[0]);
                newInput.files = dt.files;
                // Limpiar el viejo input
                this.remove();
                // Reenlazar listener
                newInput.addEventListener('change', arguments.callee);
            };
            reader.readAsDataURL(this.files[0]);
        });
    });

    // ─── Mapa Leaflet ─────────────────────────────────────
    const latInput  = document.getElementById('mapLat');
    const lngInput  = document.getElementById('mapLng');
    const badge     = document.getElementById('coordsBadge');
    const defaultLat  = {{ $client?->latitude ?? -1.8312 }};
    const defaultLng  = {{ $client?->longitude ?? -78.1834 }};
    const defaultZoom = {{ $client?->latitude ? 15 : 6 }};

    const map = L.map('clientMap').setView([defaultLat, defaultLng], defaultZoom);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 19,
    }).addTo(map);

    const markerIcon = L.divIcon({
        className: '',
        html: '<div style="background:#0d6efd;width:18px;height:18px;border-radius:50%;border:3px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,.35);"></div>',
        iconSize: [18, 18], iconAnchor: [9, 9],
    });

    let marker = null;

    @if($client?->latitude)
        marker = L.marker([{{ $client->latitude }}, {{ $client->longitude }}], { icon: markerIcon, draggable: true }).addTo(map);
        marker.on('dragend', e => updateCoords(e.target.getLatLng()));
    @endif

    function setMarker(latlng) {
        if (marker) { marker.setLatLng(latlng); }
        else {
            marker = L.marker(latlng, { icon: markerIcon, draggable: true }).addTo(map);
            marker.on('dragend', e => updateCoords(e.target.getLatLng()));
        }
        updateCoords(latlng);
        map.setView(latlng, Math.max(map.getZoom(), 15));
    }

    function updateCoords(latlng) {
        latInput.value = latlng.lat.toFixed(7);
        lngInput.value = latlng.lng.toFixed(7);
        const label = latlng.lat.toFixed(5) + ', ' + latlng.lng.toFixed(5);
        badge.textContent = label;
        badge.style.removeProperty('display');
        badge.className = 'badge bg-success-subtle text-success fw-normal';
    }

    map.on('click', e => setMarker(e.latlng));

    // GPS
    document.getElementById('mapGpsBtn')?.addEventListener('click', function () {
        if (!navigator.geolocation) { alert('Geolocalización no disponible.'); return; }
        this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        this.disabled = true;
        const btn = this;
        navigator.geolocation.getCurrentPosition(
            pos => {
                setMarker({ lat: pos.coords.latitude, lng: pos.coords.longitude });
                btn.innerHTML = '<i class="bi bi-crosshair"></i> GPS';
                btn.disabled = false;
            },
            () => {
                btn.innerHTML = '<i class="bi bi-crosshair"></i> GPS';
                btn.disabled = false;
                alert('No se pudo obtener la ubicación.');
            }
        );
    });

    // Búsqueda de dirección (Nominatim)
    let searchTimeout = null;
    const searchInput = document.getElementById('mapSearchInput');
    const suggestions = document.getElementById('mapSuggestions');

    searchInput?.addEventListener('input', function () {
        clearTimeout(searchTimeout);
        const q = this.value.trim();
        if (q.length < 3) { suggestions.classList.add('d-none'); return; }
        searchTimeout = setTimeout(async () => {
            try {
                const res  = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(q)}&limit=5&accept-language=es`);
                const data = await res.json();
                if (!data.length) { suggestions.classList.add('d-none'); return; }
                suggestions.innerHTML = data.map(r =>
                    `<div class="map-suggestion-item" data-lat="${r.lat}" data-lon="${r.lon}">${r.display_name}</div>`
                ).join('');
                suggestions.classList.remove('d-none');
                suggestions.querySelectorAll('.map-suggestion-item').forEach(item => {
                    item.addEventListener('click', function () {
                        setMarker({ lat: parseFloat(this.dataset.lat), lng: parseFloat(this.dataset.lon) });
                        searchInput.value = this.textContent;
                        suggestions.classList.add('d-none');
                    });
                });
            } catch (e) { suggestions.classList.add('d-none'); }
        }, 500);
    });

    // Cerrar sugerencias al hacer clic fuera
    document.addEventListener('click', e => {
        if (!searchInput.contains(e.target) && !suggestions.contains(e.target)) {
            suggestions.classList.add('d-none');
        }
    });
})();
</script>
@endpush
