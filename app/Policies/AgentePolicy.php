<?php

namespace App\Policies;

use App\Models\Agente;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AgentePolicy
{
    public function before(User $user, string $ability): bool|null
    {
        if ($user->hasRole('administrador')) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['secretaria_general', 'presidencia']);
    }

    public function view(User $user, Agente $agente): bool
    {
        return $user->hasAnyRole(['secretaria_general', 'presidencia']);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Agente $agente): bool
    {
        return false;
    }

    public function delete(User $user, Agente $agente): bool
    {
        return false;
    }

    public function restore(User $user, Agente $agente): bool
    {
        return false;
    }

    public function forceDelete(User $user, Agente $agente): bool
    {
        return false;
    }
}
