<?php

namespace App\Policies;

use App\Models\Pago;
use App\Models\User;

class PagoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_pagos');
    }

    public function view(User $user, Pago $pago): bool
    {
        return $user->can('view_pagos');
    }

    public function create(User $user): bool
    {
        return $user->can('create_pagos');
    }

    public function update(User $user, Pago $pago): bool
    {
        return $user->can('edit_pagos');
    }

    public function delete(User $user, Pago $pago): bool
    {
        return $user->can('delete_pagos');
    }
}
