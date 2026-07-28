<?php

namespace App\Policies;

use App\Models\Producto;
use App\Models\User;

class ProductoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_productos');
    }

    public function view(User $user, Producto $producto): bool
    {
        return $user->can('view_productos');
    }

    public function create(User $user): bool
    {
        return $user->can('create_productos');
    }

    public function update(User $user, Producto $producto): bool
    {
        return $user->can('edit_productos');
    }

    public function delete(User $user, Producto $producto): bool
    {
        return $user->can('delete_productos');
    }
}
