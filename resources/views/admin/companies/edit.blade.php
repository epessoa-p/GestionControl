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
            <div class="d-flex align-items-center gap-2 mb-1">
                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width:36px;height:36px;flex-shrink:0;">
                    <i class="bi bi-gear-wide text-primary"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0">Costos indirectos de producción</h5>
                    <small class="text-muted">Define cómo se calcula y distribuye el overhead en cada orden de producción</small>
                </div>
            </div>
            <hr class="mt-2 mb-3">

            @include('admin.companies._overhead_config')

            <div class="form-group mt-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-1"></i> Actualizar
                </button>
                <a href="{{ route('companies.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
