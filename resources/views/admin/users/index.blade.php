@extends('layouts.app')

@section('page')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-people"></i> Usuarios</h1>
    <a href="{{ route('users.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Nuevo Usuario
    </a>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Empresas / Rol</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td><strong>{{ $user->name }}</strong></td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->phone ?? '-' }}</td>
                        <td>
                            @forelse($user->companies as $company)
                                <span class="badge bg-light text-dark border me-1 mb-1 d-inline-flex align-items-center gap-1">
                                    <i class="bi bi-building text-primary"></i>{{ $company->name }}
                                    <span class="text-muted">·</span>
                                    <span class="text-success">{{ $roleNames[$company->pivot->role_id] ?? '—' }}</span>
                                </span>
                            @empty
                                @if($user->is_super_admin)
                                    <span class="badge bg-danger-subtle text-danger"><i class="bi bi-globe me-1"></i>Acceso global</span>
                                @else
                                    <span class="text-muted small">Sin empresa asignada</span>
                                @endif
                            @endforelse
                        </td>
                        <td>
                            @if($user->is_super_admin)
                                <span class="badge bg-danger">Super Admin</span>
                            @else
                                <span class="badge bg-primary">Usuario</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $user->active ? 'bg-success' : 'bg-danger' }}">
                                {{ $user->active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @if(auth()->user()->id !== $user->id)
                                <form action="{{ route('users.destroy', $user) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="d-flex justify-content-center">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
