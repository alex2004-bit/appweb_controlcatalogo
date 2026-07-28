<?php

namespace App\Policies;

use App\Models\Galeria;
use App\Models\User;

class GaleriaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_galeria');
    }

    public function view(User $user, Galeria $galeria): bool
    {
        return $user->can('view_galeria');
    }

    public function create(User $user): bool
    {
        return $user->can('create_galeria');
    }

    public function update(User $user, Galeria $galeria): bool
    {
        return $user->can('edit_galeria');
    }

    public function delete(User $user, Galeria $galeria): bool
    {
        return $user->can('delete_galeria');
    }
}
