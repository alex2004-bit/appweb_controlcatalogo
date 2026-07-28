<?php

namespace App\Http\Controllers\Admin\Seguridad;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Seguridad\UpdatePermissionsRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    public function index(): Response
    {
        request()->user()->can('view_roles') || abort(403);

        $permissions = Permission::orderBy('group')->orderBy('name')->get()->groupBy('group');

        return Inertia::render('admin/seguridad/permisos/Index', [
            'permissions' => $permissions,
        ]);
    }

    public function update(UpdatePermissionsRequest $request): RedirectResponse
    {
        $request->user()->can('edit_roles') || abort(403);

        $data = $request->validated();

        $role = Role::findById($data['role_id']);
        $role->syncPermissions($data['permissions'] ?? []);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Permisos actualizados correctamente.']);

        return back();
    }
}
