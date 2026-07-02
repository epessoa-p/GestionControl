@extends('layouts.app')

@section('page')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-shield-lock"></i> Roles</h1>
    <a href="{{ route('roles.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Nuevo Rol
    </a>
</div>

@if(auth()->user()->is_super_admin)
<form method="GET" class="mb-3 d-flex gap-2 align-items-center">
    <label class="text-muted small mb-0">Empresa:</label>
    <select name="company_id" class="form-select form-select-sm" style="max-width:280px" onchange="this.form.submit()">
        <option value="">Todas</option>
        @foreach($companies as $co)
            <option value="{{ $co->id }}" {{ (string)request('company_id') === (string)$co->id ? 'selected' : '' }}>{{ $co->name }}</option>
        @endforeach
    </select>
    @if(request('company_id'))<a href="{{ route('roles.index') }}" class="btn btn-sm btn-outline-secondary">Limpiar</a>@endif
</form>
@endif

<div class="card">
    <div class="card-body">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Empresa</th>
                    <th>Nombre</th>
                    <th>Slug</th>
                    <th>Descripción</th>
                    <th>Permisos</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($roles as $role)
                    <tr>
                        <td>
                            @if($role->company)
                                <span class="badge bg-light text-dark border"><i class="bi bi-building text-primary me-1"></i>{{ $role->company->name }}</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border"><i class="bi bi-globe me-1"></i>Global (plantilla)</span>
                            @endif
                        </td>
                        <td><strong>{{ $role->name }}</strong></td>
                        <td><code>{{ $role->slug }}</code></td>
                        <td>{{ $role->description ?? '-' }}</td>
                        <td>{{ $role->permissions->count() }} permisos</td>
                        <td>
                            <a href="{{ route('roles.edit', $role) }}" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('roles.destroy', $role) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="d-flex justify-content-center">
            {{ $roles->links() }}
        </div>
    </div>
</div>
@endsection
