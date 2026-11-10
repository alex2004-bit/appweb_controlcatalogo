<?php

namespace App\Policies;

use App\Models\Compra;
use App\Models\User;

class CompraPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_compras');
    }

    public function view(User $user, Compra $compra): bool
    {
        return $user->can('view_compras');
    }

    public function create(User $user): bool
    {
        return $user->can('create_compras');
    }

    public function receive(User $user, Compra $compra): bool
    {
        return $user->can('receive_compras');
    }

    public function cancel(User $user, Compra $compra): bool
    {
        return $user->can('cancel_compras');
    }
}
