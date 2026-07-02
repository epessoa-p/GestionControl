@extends('layouts.app')

@section('title', 'Nuevo usuario')

@section('page')
<div class="container-fluid" style="max-width:820px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-person-plus text-primary me-2"></i>Nuevo usuario</h1>
        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Volver</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form action="{{ route('users.store') }}" method="POST">
        @csrf

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white"><strong><i class="bi bi-person me-1"></i> Datos del usuario</strong></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nombre de usuario <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        <div class="form-text">Sin espacios.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contraseña <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirmar contraseña <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                    </div>
                    @if(auth()->user()->is_super_admin)
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_super_admin" value="1" id="isSuper" {{ old('is_super_admin') ? 'checked' : '' }}>
                            <label class="form-check-label" for="isSuper">
                                <strong>Super administrador</strong> <span class="text-muted small">(acceso global a todo el sistema)</span>
                            </label>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white"><strong><i class="bi bi-building me-1"></i> Empresas y roles</strong></div>
            <div class="card-body">
                @forelse($companies as $company)
                    <div class="row g-2 align-items-center mb-2">
                        <div class="col-md-5">
                            <span class="fw-semibold"><i class="bi bi-building text-muted me-1"></i>{{ $company->name }}</span>
                        </div>
                        <div class="col-md-7">
                            <select name="companies[{{ $company->id }}]" class="form-select form-select-sm">
                                <option value="">— Sin acceso —</option>
                                @foreach($rolesByCompany[$company->id] ?? [] as $role)
                                    <option value="{{ $role->id }}" {{ (string)old('companies.'.$company->id) === (string)$role->id ? 'selected' : '' }}>
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @empty
                    <div class="alert alert-warning mb-0"><i class="bi bi-exclamation-triangle me-1"></i>No hay empresas disponibles para asignar.</div>
                @endforelse
                <div class="form-text">Elige el rol del usuario en cada empresa. Deja "Sin acceso" para no vincularlo.</div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Crear usuario</button>
        </div>
    </form>
</div>
@endsection
