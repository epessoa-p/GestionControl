{{-- Fila de contacto adicional --}}
<div class="contact-row border rounded-3 p-3 mb-2 bg-light position-relative">
    <div class="row g-2">
        <div class="col-md-5">
            <input type="text" name="contacts[{{ $i }}][name]"
                   class="form-control form-control-sm"
                   placeholder="Nombre del contacto"
                   value="{{ $contact['name'] ?? '' }}">
        </div>
        <div class="col-md-3">
            <input type="text" name="contacts[{{ $i }}][position]"
                   class="form-control form-control-sm"
                   placeholder="Cargo"
                   value="{{ $contact['position'] ?? '' }}">
        </div>
        <div class="col-md-3">
            <input type="email" name="contacts[{{ $i }}][email]"
                   class="form-control form-control-sm"
                   placeholder="Email"
                   value="{{ $contact['email'] ?? '' }}">
        </div>
        <div class="col-md-1 d-flex align-items-center justify-content-end">
            <button type="button" class="btn btn-sm btn-outline-danger remove-contact-btn" title="Eliminar fila">
                <i class="bi bi-x"></i>
            </button>
        </div>
        <div class="col-md-3">
            <input type="text" name="contacts[{{ $i }}][phone]"
                   class="form-control form-control-sm"
                   placeholder="Teléfono"
                   value="{{ $contact['phone'] ?? '' }}">
        </div>
        <div class="col-md-5">
            <input type="text" name="contacts[{{ $i }}][notes]"
                   class="form-control form-control-sm"
                   placeholder="Notas"
                   value="{{ $contact['notes'] ?? '' }}">
        </div>
        <div class="col-md-4 d-flex align-items-center gap-2">
            <input type="checkbox" name="contacts[{{ $i }}][is_primary]"
                   class="form-check-input" id="primary_{{ $i }}"
                   value="1" {{ !empty($contact['is_primary']) ? 'checked' : '' }}>
            <label class="form-check-label small" for="primary_{{ $i }}">Contacto principal</label>
        </div>
    </div>
</div>
