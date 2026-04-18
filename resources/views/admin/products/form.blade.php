<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">{{ $product ? 'Editar producto' : 'Nuevo producto' }}</h1>
            <p class="text-muted mb-0">Define catálogo, costos y categoría de crédito asociada.</p>
        </div>
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Volver</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="{{ $action }}" method="POST" class="row g-3">
                @csrf
                @if($method !== 'POST') @method($method) @endif

                @if($companies->count() > 1)
                    <div class="col-md-6">
                        <label class="form-label">Empresa</label>
                        <select name="company_id" class="form-select">
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ (string) old('company_id', $product?->company_id) === (string) $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="col-md-6">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $product?->name) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">SKU</label>
                    <input type="text" name="sku" class="form-control" value="{{ old('sku', $product?->sku) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Unidad</label>
                    <div class="d-flex gap-2">
                        <select name="measurement_unit_id" class="form-select js-measurement-unit-select @error('measurement_unit_id') is-invalid @enderror" required>
                            <option value="">Selecciona una unidad</option>
                            @foreach($measurementUnits as $measurementUnit)
                                <option value="{{ $measurementUnit->id }}" {{ (string) old('measurement_unit_id', $product ? $product->measurement_unit_id : 1) === (string) $measurementUnit->id ? 'selected' : '' }}>
                                    {{ $measurementUnit->name }} ({{ $measurementUnit->symbol }})
                                </option>
                            @endforeach
                        </select>
                        <a href="{{ route('measurement-units.index') }}" class="btn btn-outline-secondary" title="Gestionar unidades">
                            <i class="bi bi-gear"></i>
                        </a>
                    </div>
                    @error('measurement_unit_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Costo</label>
                    <input type="number" step="0.01" name="cost" class="form-control" value="{{ old('cost', $product?->cost) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Precio</label>
                    <input type="number" step="0.01" name="price" class="form-control" value="{{ old('price', $product?->price) }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Categoría</label>
                    <input type="text" name="category" class="form-control" value="{{ old('category', $product?->category) }}" placeholder="Ej: Materia prima, Producto terminado">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Stock actual</label>
                    <input type="number" step="0.01" name="current_stock" class="form-control" value="{{ old('current_stock', $product?->current_stock ?? 0) }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Stock mínimo</label>
                    <input type="number" step="0.01" name="min_stock" class="form-control" value="{{ old('min_stock', $product?->min_stock ?? 0) }}">
                </div>

                <div class="col-12">
                    <label class="form-label">Descripción</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description', $product?->description) }}</textarea>
                </div>

                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="active" value="1" {{ old('active', $product?->active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label">Activo</label>
                    </div>
                </div>

                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-primary" type="submit">Guardar</button>
                    <a href="{{ route('products.index') }}" class="btn btn-light border">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container .select2-selection--single {
        height: calc(2.25rem + 2px);
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        padding: 0.25rem 0.5rem;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 1.8rem;
        padding-left: 0.25rem;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: calc(2.25rem + 2px);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(function () {
        $('.js-measurement-unit-select').select2({
            width: '100%',
            placeholder: 'Selecciona una unidad',
            allowClear: true
        });
    });
</script>
@endpush