<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><i class="bi bi-clipboard-check text-primary me-2"></i>{{ $tracking ? 'Editar seguimiento' : 'Nuevo seguimiento' }}</h1>
            <p class="text-muted mb-0">Registra y asigna actividades de seguimiento</p>
        </div>
        <a href="{{ route('trackings.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ $action }}" method="POST" class="row g-3">
                        @csrf
                        @if($method !== 'POST') @method($method) @endif

                        {{-- Section: General --}}
                        <div class="col-12">
                            <h6 class="fw-bold text-primary mb-0"><i class="bi bi-pencil-square me-1"></i> Datos generales</h6>
                            <hr class="mt-2 mb-0">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Título <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $tracking?->title) }}" placeholder="Describe la actividad" required>
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Tipo <span class="text-danger">*</span></label>
                            <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                @foreach(\App\Models\Tracking::TYPE_LABELS as $val => $label)
                                    <option value="{{ $val }}" {{ old('type', $tracking?->type) === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Estado <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                @foreach(\App\Models\Tracking::STATUS_LABELS as $val => $label)
                                    <option value="{{ $val }}" {{ old('status', $tracking?->status ?? 'pending') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Prioridad <span class="text-danger">*</span></label>
                            <select name="priority" class="form-select @error('priority') is-invalid @enderror" required>
                                @foreach(\App\Models\Tracking::PRIORITY_LABELS as $val => $label)
                                    <option value="{{ $val }}" {{ old('priority', $tracking?->priority ?? 'medium') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('priority')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Section: Assignment --}}
                        <div class="col-12 mt-2">
                            <h6 class="fw-bold text-primary mb-0"><i class="bi bi-person-check me-1"></i> Asignación</h6>
                            <hr class="mt-2 mb-0">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Asignado a</label>
                            <select name="assigned_to" class="form-select">
                                <option value="">Sin asignar</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ (string)old('assigned_to', $tracking?->assigned_to) === (string)$user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Fecha de vencimiento</label>
                            <input type="date" name="due_date" class="form-control" value="{{ old('due_date', $tracking?->due_date?->format('Y-m-d')) }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Descripción</label>
                            <textarea name="description" class="form-control" rows="4" placeholder="Detalles adicionales del seguimiento...">{{ old('description', $tracking?->description) }}</textarea>
                        </div>

                        <div class="col-12">
                            <hr class="my-2">
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg me-1"></i> Guardar</button>
                            <a href="{{ route('trackings.index') }}" class="btn btn-light border">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
