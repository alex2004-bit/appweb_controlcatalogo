<?php

namespace App\Policies;

use App\Models\Cotizacion;
use App\Models\User;

class CotizacionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_cotizaciones');
    }

    public function view(User $user, Cotizacion $cotizacion): bool
    {
        return $user->can('view_cotizaciones');
    }

    public function create(User $user): bool
    {
        return $user->can('create_cotizaciones');
    }

    public function update(User $user, Cotizacion $cotizacion): bool
    {
        return $user->can('edit_cotizaciones');
    }

    public function delete(User $user, Cotizacion $cotizacion): bool
    {
        return $user->can('delete_cotizaciones');
    }
}
