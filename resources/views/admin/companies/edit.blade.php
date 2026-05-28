@extends('layouts.app')

@section('page')
<h1><i class="bi bi-pencil"></i> Editar Empresa</h1>

<div class="card mt-4">
    <div class="card-body">
        <form action="{{ route('companies.update', $company) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group mb-3">
                <label for="name" class="form-label">Nombre</label>
                <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $company->name) }}" required>
            </div>

            <div class="form-group mb-3">
                <label for="ruc" class="form-label">RUC</label>
                <input type="text" id="ruc" name="ruc" class="form-control @error('ruc') is-invalid @enderror" value="{{ old('ruc', $company->ruc) }}">
            </div>

            <div class="form-group mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $company->email) }}">
            </div>

            <div class="form-group mb-3">
                <label for="phone" class="form-label">Teléfono</label>
                <input type="text" id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $company->phone) }}">
            </div>

            <div class="form-group mb-3">
                <label for="address" class="form-label">Dirección</label>
                <input type="text" id="address" name="address" class="form-control @error('address') is-invalid @enderror" value="{{ old('address', $company->address) }}">
            </div>

            <div class="form-group mb-3">
                <label for="description" class="form-label">Descripción</label>
                <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $company->description) }}</textarea>
            </div>

            {{-- ── Configuración de costos indirectos ── --}}
            <hr class="my-4">
            <h5 class="mb-3"><i class="bi bi-gear-wide me-2 text-primary"></i> Configuración de costos indirectos</h5>

            <div class="form-group mb-3">
                <label class="form-label fw-semibold">Método de distribución de overhead</label>
                <select name="overhead_distribution_method" id="overheadMethod" class="form-select">
                    <option value="manual" {{ old('overhead_distribution_method', $company->overhead_distribution_method ?? 'manual') === 'manual' ? 'selected' : '' }}>
                        Manual — el usuario ingresa el monto en cada producción
                    </option>
                    <option value="por_unidades" {{ old('overhead_distribution_method', $company->overhead_distribution_method) === 'por_unidades' ? 'selected' : '' }}>
                        Por unidades producidas — proporcional a la cantidad
                    </option>
                    <option value="por_orden" {{ old('overhead_distribution_method', $company->overhead_distribution_method) === 'por_orden' ? 'selected' : '' }}>
                        Por orden de producción — igual para cada orden
                    </option>
                    <option value="tasa_fija" {{ old('overhead_distribution_method', $company->overhead_distribution_method) === 'tasa_fija' ? 'selected' : '' }}>
                        Tasa fija — tarifa configurable × unidades producidas
                    </option>
                </select>
                <div class="form-text">Define cómo se sugiere el overhead al crear una producción.</div>
            </div>

            <div class="form-group mb-3" id="fixedRateRow" style="{{ old('overhead_distribution_method', $company->overhead_distribution_method) === 'tasa_fija' ? '' : 'display:none' }}">
                <label class="form-label fw-semibold">Tasa fija por unidad producida</label>
                <div class="input-group" style="max-width:280px">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.0001" min="0" name="overhead_fixed_rate"
                           class="form-control"
                           value="{{ old('overhead_fixed_rate', $company->overhead_fixed_rate ?? 0) }}"
                           placeholder="0.0000">
                </div>
                <div class="form-text">Monto de overhead asignado por cada unidad producida.</div>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Actualizar
                </button>
                <a href="{{ route('companies.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Cancelar
                </a>
            </div>
        </form>
        <script>
        document.getElementById('overheadMethod').addEventListener('change', function () {
            document.getElementById('fixedRateRow').style.display = this.value === 'tasa_fija' ? '' : 'none';
        });
        </script>
    </div>
</div>
@endsection
