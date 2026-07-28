<?php

namespace App\Http\Controllers\Admin\Seguridad;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Seguridad\StoreRoleRequest;
use App\Http\Requests\Admin\Seguridad\UpdateRoleRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(Request $request): Response
    {
        $request->user()->can('view_roles') || abort(403);

        $search = $request->input('search');

        $roles = Role::with('permissions')
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/seguridad/roles/Index', [
            'roles' => $roles,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        request()->user()->can('create_roles') || abort(403);

        $permissions = Permission::all()->groupBy('group');

        return Inertia::render('admin/seguridad/roles/Create', [
            'permissionGroups' => $permissions,
        ]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $request->user()->can('create_roles') || abort(403);

        $role = Role::create(['name' => $request->name]);

        if ($request->filled('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Rol creado correctamente.']);

        return to_route('admin.seguridad.roles.index', ['current_team' => $request->user()->currentTeam]);
    }

    public function edit(Role $role): Response
    {
        request()->user()->can('edit_roles') || abort(403);

        $role->load('permissions');

        $permissions = Permission::all()->groupBy('group');

        return Inertia::render('admin/seguridad/roles/Edit', [
            'role' => $role,
            'permissionGroups' => $permissions,
            'rolePermissions' => $role->permissions->pluck('name'),
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $request->user()->can('edit_roles') || abort(403);

        $role->update(['name' => $request->name]);

        if ($request->filled('permissions')) {
            $role->syncPermissions($request->permissions);
        } else {
            $role->syncPermissions([]);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Rol actualizado correctamente.']);

        return to_route('admin.seguridad.roles.index', ['current_team' => $request->user()->currentTeam]);
    }

    public function destroy(Role $role): RedirectResponse
    {
        request()->user()->can('delete_roles') || abort(403);

        if ($role->name === 'Super Admin') {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'No puedes eliminar el rol Super Admin.']);

            return back();
        }

        $role->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Rol eliminado correctamente.']);

        return to_route('admin.seguridad.roles.index', ['current_team' => auth()->user()->currentTeam]);
    }
}
