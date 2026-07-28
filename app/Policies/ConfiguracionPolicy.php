<?php

namespace App\Policies;

use App\Models\User;

class ConfiguracionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_configuracion');
    }

    public function view(User $user): bool
    {
        return $user->can('view_configuracion');
    }

    public function create(User $user): bool
    {
        return $user->can('edit_configuracion');
    }

    public function update(User $user): bool
    {
        return $user->can('edit_configuracion');
    }

    public function delete(User $user): bool
    {
        return $user->can('edit_configuracion');
    }
}
