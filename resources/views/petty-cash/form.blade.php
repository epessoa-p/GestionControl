<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><i class="bi bi-wallet2 text-primary me-2"></i>{{ $pettyCash ? 'Editar caja chica' : 'Nueva caja chica' }}</h1>
            <p class="text-muted mb-0">Configura un fondo de caja chica</p>
        </div>
        <a href="{{ route('petty-cash.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 px-4">
                    <h6 class="fw-bold text-primary mb-0"><i class="bi bi-gear me-1"></i> Configuración</h6>
                </div>
                <div class="card-body p-4 pt-3">
                    <form action="{{ $action }}" method="POST" class="row g-3">
                        @csrf
                        @if($method !== 'POST') @method($method) @endif

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $pettyCash?->name) }}" placeholder="Nombre de la caja chica" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Sucursal</label>
                            <select name="branch_id" class="form-select">
                                <option value="">Sin asignar</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" {{ (string)old('branch_id', $pettyCash?->branch_id) === (string)$b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Monto inicial <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" min="0" name="initial_amount" class="form-control @error('initial_amount') is-invalid @enderror" value="{{ old('initial_amount', $pettyCash?->initial_amount ?? 0) }}" required>
                            </div>
                            @error('initial_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12"><hr class="my-2"></div>
                        <div class="col-12 d-flex gap-2">
                            <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg me-1"></i> Guardar</button>
                            <a href="{{ route('petty-cash.index') }}" class="btn btn-light border">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
