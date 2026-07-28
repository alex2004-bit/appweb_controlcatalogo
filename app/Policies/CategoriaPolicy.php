<?php

namespace App\Policies;

use App\Models\Categoria;
use App\Models\User;

class CategoriaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_categorias');
    }

    public function view(User $user, Categoria $categoria): bool
    {
        return $user->can('view_categorias');
    }

    public function create(User $user): bool
    {
        return $user->can('create_categorias');
    }

    public function update(User $user, Categoria $categoria): bool
    {
        return $user->can('edit_categorias');
    }

    public function delete(User $user, Categoria $categoria): bool
    {
        return $user->can('delete_categorias');
    }
}
