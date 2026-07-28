<?php

namespace App\Policies;

use App\Models\Servicio;
use App\Models\User;

class ServicioPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_servicios');
    }

    public function view(User $user, Servicio $servicio): bool
    {
        return $user->can('view_servicios');
    }

    public function create(User $user): bool
    {
        return $user->can('create_servicios');
    }

    public function update(User $user, Servicio $servicio): bool
    {
        return $user->can('edit_servicios');
    }

    public function delete(User $user, Servicio $servicio): bool
    {
        return $user->can('delete_servicios');
    }
}
