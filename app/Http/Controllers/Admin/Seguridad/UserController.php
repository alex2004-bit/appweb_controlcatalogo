<?php

namespace App\Http\Controllers\Admin\Seguridad;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Seguridad\StoreUserRequest;
use App\Http\Requests\Admin\Seguridad\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $request->user()->can('view_users') || abort(403);

        $search = $request->input('search');

        $users = User::with('roles')
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            }))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/seguridad/usuarios/Index', [
            'users' => $users,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        request()->user()->can('create_users') || abort(403);

        return Inertia::render('admin/seguridad/usuarios/Create', [
            'roles' => Role::all()->pluck('name'),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $request->user()->can('create_users') || abort(403);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        if ($request->filled('role')) {
            $user->assignRole($request->role);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Usuario creado correctamente.']);

        return to_route('admin.seguridad.users.index', ['current_team' => $request->user()->currentTeam]);
    }

    public function edit(User $user): Response
    {
        request()->user()->can('edit_users') || abort(403);

        $user->load('roles');

        return Inertia::render('admin/seguridad/usuarios/Edit', [
            'user' => $user,
            'roles' => Role::all()->pluck('name'),
            'userRole' => $user->roles->first()?->name,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $request->user()->can('edit_users') || abort(403);

        $data = $request->validated();

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        if ($request->filled('role')) {
            $user->syncRoles([$request->role]);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Usuario actualizado correctamente.']);

        return to_route('admin.seguridad.users.index', ['current_team' => $request->user()->currentTeam]);
    }

    public function destroy(User $user): RedirectResponse
    {
        request()->user()->can('delete_users') || abort(403);

        if ($user->id === auth()->id()) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'No puedes eliminar tu propio usuario.']);

            return back();
        }

        $user->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Usuario eliminado correctamente.']);

        return to_route('admin.seguridad.users.index', ['current_team' => auth()->user()->currentTeam]);
    }
}
