<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        $this->ensureAccess();
        $user = auth()->user();

        $query = Role::with(['permissions', 'company']);
        if ($user->is_super_admin) {
            // Ve TODOS los roles (todas las empresas + plantillas); filtro opcional.
            if (request()->filled('company_id')) {
                $query->where('company_id', request('company_id'));
            }
        } else {
            $query->where('company_id', $this->getCompanyId());
        }

        $roles = $query->orderByRaw('company_id IS NULL DESC')->orderBy('company_id')->orderBy('name')->paginate(20)->withQueryString();
        $companies = $user->is_super_admin ? Company::orderBy('name')->get() : collect();

        return view('admin.roles.index', compact('roles', 'companies'));
    }

    public function create()
    {
        $this->ensureAccess();
        $permissions = Permission::all()->groupBy('module');
        $companies = auth()->user()->is_super_admin ? Company::orderBy('name')->get() : collect();
        return view('admin.roles.create', compact('permissions', 'companies'));
    }

    public function store()
    {
        $this->ensureAccess();
        $user = auth()->user();

        // Super admin elige la empresa; el admin de empresa usa la suya.
        $companyId = $user->is_super_admin ? request('company_id') : $this->getCompanyId();

        $rules = [
            'name' => 'required|string|max:255',
            'slug' => ['required', 'string', 'max:255', Rule::unique('roles', 'slug')->where(fn($q) => $q->where('company_id', $companyId))],
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
        ];
        if ($user->is_super_admin) {
            $rules['company_id'] = 'required|exists:companies,id';
        }
        $validated = request()->validate($rules);

        $role = Role::create([
            'company_id'  => $companyId,
            'name'        => $validated['name'],
            'slug'        => $validated['slug'],
            'description' => $validated['description'] ?? null,
        ]);

        if (request('permissions')) {
            $role->permissions()->attach(request('permissions'));
        }

        return redirect()->route('roles.index')->with('success', 'Rol creado exitosamente');
    }

    public function show(Role $role)
    {
        $this->ensureAccess();
        $this->authorizeRole($role);
        $role->load('permissions');
        return view('admin.roles.show', compact('role'));
    }

    public function edit(Role $role)
    {
        $this->ensureAccess();
        $this->authorizeRole($role);
        $permissions = Permission::all()->groupBy('module');
        $role->load('permissions');
        return view('admin.roles.edit', compact('role', 'permissions'));
    }

    public function update(Role $role)
    {
        $this->ensureAccess();
        $this->authorizeRole($role);

        $validated = request()->validate([
            'name' => 'required|string|max:255',
            'slug' => ['required', 'string', 'max:255', Rule::unique('roles', 'slug')->where(fn($q) => $q->where('company_id', $role->company_id))->ignore($role->id)],
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
        ]);

        $role->update($validated);

        $role->permissions()->sync(request('permissions', []));

        return redirect()->route('roles.index')->with('success', 'Rol actualizado exitosamente');
    }

    public function destroy(Role $role)
    {
        $this->ensureAccess();
        $this->authorizeRole($role);

        // Prevenir eliminar roles que están en uso
        if ($role->users()->count() > 0) {
            return back()->withErrors(['error' => 'No puedes eliminar un rol que está siendo utilizado']);
        }

        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Rol eliminado exitosamente');
    }

    // ─── Autorización / helpers ─────────────────────────────────────

    private function getCompanyId(): ?int
    {
        $user = auth()->user();
        return $user->is_super_admin
            ? ($user->getCurrentCompany()?->id ?? session('current_company_id'))
            : $user->getCurrentCompany()?->id;
    }

    /** Solo super admin o el admin de la empresa actual pueden gestionar roles. */
    private function ensureAccess(): void
    {
        $user = auth()->user();
        if ($user->is_super_admin) {
            return;
        }
        if (!$user->hasRoleInCompany('admin', $user->getCurrentCompany())) {
            abort(403);
        }
    }

    /** El rol debe pertenecer a la empresa del usuario (o ser super admin). */
    private function authorizeRole(Role $role): void
    {
        if (auth()->user()->is_super_admin) {
            return;
        }
        if ($role->company_id !== $this->getCompanyId()) {
            abort(403);
        }
    }
}
