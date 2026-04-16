<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><i class="bi bi-megaphone text-primary me-2"></i>{{ $promoter ? 'Editar promotor' : 'Nuevo promotor' }}</h1>
            <p class="text-muted mb-0">Datos del promotor o vendedor</p>
        </div>
        <a href="{{ route('promoters.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ $action }}" method="POST" class="row g-3">
                        @csrf
                        @if($method !== 'POST') @method($method) @endif

                        <div class="col-12">
                            <h6 class="fw-bold text-primary mb-0"><i class="bi bi-person me-1"></i> Datos personales</h6>
                            <hr class="mt-2 mb-0">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $promoter?->name) }}" placeholder="Nombre completo" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Teléfono</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $promoter?->phone) }}" placeholder="Número de contacto">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $promoter?->email) }}" placeholder="correo@ejemplo.com">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Personal vinculado</label>
                            <select name="personal_id" class="form-select">
                                <option value="">Sin vincular</option>
                                @foreach($personals as $p)
                                    <option value="{{ $p->id }}" {{ (string)old('personal_id', $promoter?->personal_id) === (string)$p->id ? 'selected' : '' }}>{{ $p->full_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 mt-2">
                            <h6 class="fw-bold text-primary mb-0"><i class="bi bi-percent me-1"></i> Comisión</h6>
                            <hr class="mt-2 mb-0">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Porcentaje de comisión <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0" max="100" name="commission_rate" class="form-control @error('commission_rate') is-invalid @enderror" value="{{ old('commission_rate', $promoter?->commission_rate ?? 0) }}" required>
                                <span class="input-group-text">%</span>
                            </div>
                            @error('commission_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold d-block">&nbsp;</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="active" value="1" id="activeSwitch" {{ old('active', $promoter?->active ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="activeSwitch">Promotor activo</label>
                            </div>
                        </div>

                        <div class="col-12"><hr class="my-2"></div>
                        <div class="col-12 d-flex gap-2">
                            <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg me-1"></i> Guardar</button>
                            <a href="{{ route('promoters.index') }}" class="btn btn-light border">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
