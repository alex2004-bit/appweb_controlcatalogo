<?php

namespace App\Policies;

use App\Models\Proveedor;
use App\Models\User;

class ProveedorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_proveedores');
    }

    public function view(User $user, Proveedor $proveedor): bool
    {
        return $user->can('view_proveedores');
    }

    public function create(User $user): bool
    {
        return $user->can('create_proveedores');
    }

    public function update(User $user, Proveedor $proveedor): bool
    {
        return $user->can('edit_proveedores');
    }

    public function delete(User $user, Proveedor $proveedor): bool
    {
        return $user->can('delete_proveedores');
    }
}
