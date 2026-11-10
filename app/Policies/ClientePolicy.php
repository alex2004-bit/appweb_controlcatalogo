<?php

namespace App\Policies;

use App\Models\Cliente;
use App\Models\User;

class ClientePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_clientes');
    }

    public function view(User $user, Cliente $cliente): bool
    {
        return $user->can('view_clientes');
    }

    public function create(User $user): bool
    {
        return $user->can('create_clientes');
    }

    public function update(User $user, Cliente $cliente): bool
    {
        return $user->can('edit_clientes');
    }

    public function delete(User $user, Cliente $cliente): bool
    {
        return $user->can('delete_clientes');
    }
}
