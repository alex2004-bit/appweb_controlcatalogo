<?php

namespace App\Policies;

use App\Models\Consulta;
use App\Models\User;

class ConsultaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_consultas');
    }

    public function view(User $user, Consulta $consulta): bool
    {
        return $user->can('view_consultas');
    }

    public function create(User $user): bool
    {
        return $user->can('view_consultas');
    }

    public function update(User $user, Consulta $consulta): bool
    {
        return $user->can('edit_consultas');
    }

    public function delete(User $user, Consulta $consulta): bool
    {
        return $user->can('delete_consultas');
    }
}
