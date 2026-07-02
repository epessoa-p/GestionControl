<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->is_super_admin) {
            $users = User::with('companies')->paginate(15);
        } else {
            $company = $user->getCurrentCompany();
            $users = $company->users()->with('companies')->paginate(15);
        }

        $roleNames = Role::pluck('name', 'id');

        return view('admin.users.index', compact('users', 'roleNames'));
    }

    public function create()
    {
        $companies = $this->assignableCompanies();
        $rolesByCompany = $this->rolesByCompany($companies);

        return view('admin.users.create', compact('companies', 'rolesByCompany'));
    }

    public function store(StoreUserRequest $request)
    {
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
                'phone' => $request->phone,
                'is_super_admin' => auth()->user()->is_super_admin && $request->boolean('is_super_admin'),
            ]);

            $this->syncCompanyRoles($user, $request->input('companies', []));

            return redirect()->route('users.index')->with('success', 'Usuario creado exitosamente');
        } catch (\Throwable $exception) {
            Log::error('Error al crear usuario', [
                'user' => $request->only('name', 'email'),
                'message' => $exception->getMessage(),
            ]);

            return back()->withInput()->withErrors(['error' => 'No fue posible crear el usuario.']);
        }
    }

    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $companies = $this->assignableCompanies();
        $rolesByCompany = $this->rolesByCompany($companies);

        return view('admin.users.edit', compact('user', 'companies', 'rolesByCompany'));
    }

    public function update(StoreUserRequest $request, User $user)
    {
        try {
            $data = $request->validated();

            if (!auth()->user()->is_super_admin) {
                unset($data['is_super_admin']);
            }

            if (empty($data['password'])) {
                unset($data['password']);
            }

            $user->update($data);

            if ($request->has('companies')) {
                $this->syncCompanyRoles($user, $request->input('companies', []));
            }

            return redirect()->route('users.index')->with('success', 'Usuario actualizado exitosamente');
        } catch (\Throwable $exception) {
            Log::error('Error al actualizar usuario', [
                'user_id' => $user->id,
                'message' => $exception->getMessage(),
            ]);

            return back()->withInput()->withErrors(['error' => 'No fue posible actualizar el usuario.']);
        }
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->user()->id) {
            return back()->withErrors(['error' => 'No puedes eliminar tu propio usuario']);
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Usuario eliminado exitosamente');
    }

    /**
     * Empresas que el usuario autenticado puede administrar (asignar).
     */
    private function allowedCompanyIds()
    {
        $auth = auth()->user();
        return $auth->is_super_admin
            ? Company::pluck('id')
            : $auth->companies()->pluck('companies.id');
    }

    /** Empresas que el administrador autenticado puede asignar (modelos). */
    private function assignableCompanies()
    {
        $auth = auth()->user();
        return $auth->is_super_admin
            ? Company::orderBy('name')->get()
            : $auth->companies()->orderBy('name')->get();
    }

    /** Roles disponibles por empresa: [company_id => Collection<Role>]. */
    private function rolesByCompany($companies)
    {
        return $companies->mapWithKeys(fn($c) => [
            $c->id => Role::where('company_id', $c->id)->orderBy('name')->get(),
        ]);
    }

    /**
     * Sincroniza el rol del usuario por empresa (pivote company_user), tocando
     * únicamente las empresas que el administrador autenticado puede gestionar.
     * $map = [companyId => roleId|null]  (null/vacío = quitar de esa empresa)
     */
    private function syncCompanyRoles(User $user, array $map): void
    {
        $allowed = $this->allowedCompanyIds();

        foreach ($allowed as $companyId) {
            $roleId = $map[$companyId] ?? null;

            if ($roleId) {
                if ($user->companies()->where('companies.id', $companyId)->exists()) {
                    $user->companies()->updateExistingPivot($companyId, ['role_id' => $roleId, 'active' => true]);
                } else {
                    $user->companies()->attach($companyId, ['role_id' => $roleId, 'active' => true]);
                }
            } else {
                $user->companies()->detach($companyId);
            }
        }
    }

    /**
     * Asignar rol a usuario en una empresa
     */
    public function assignRole(User $user, Company $company, Role $role)
    {
        $authUser = auth()->user();

        if (!$authUser->is_super_admin && $authUser->getCurrentCompany()->id !== $company->id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $user->companies()->syncWithoutDetaching([$company->id => ['role_id' => $role->id]]);

        return back()->with('success', 'Rol asignado exitosamente');
    }
}
