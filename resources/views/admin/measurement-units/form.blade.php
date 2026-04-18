<div class="container-fluid" style="max-width: 900px;">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="mb-1">{{ $measurementUnit ? 'Editar unidad de medida' : 'Nueva unidad de medida' }}</h1>
            <p class="text-muted mb-0">Define el nombre y símbolo de la unidad que se usará en productos.</p>
        </div>
        <a href="{{ route('measurement-units.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="{{ $action }}" method="POST" class="row g-3">
                @csrf
                @if($method !== 'POST') @method($method) @endif

                <div class="col-md-6">
                    <label class="form-label">Nombre <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $measurementUnit?->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Símbolo <span class="text-danger">*</span></label>
                    <input type="text" name="symbol" class="form-control @error('symbol') is-invalid @enderror" value="{{ old('symbol', $measurementUnit?->symbol) }}" placeholder="Ej: kg, m, lt" required>
                    @error('symbol')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label">Descripción</label>
                    <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $measurementUnit?->description) }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="active" value="1" {{ old('active', $measurementUnit?->active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label">Activa</label>
                    </div>
                </div>

                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-save"></i> {{ $measurementUnit ? 'Guardar cambios' : 'Crear unidad' }}
                    </button>
                    <a href="{{ route('measurement-units.index') }}" class="btn btn-light border">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
