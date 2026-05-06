<?php

namespace App\Policies;

use App\Models\TipoSolicitud;
use App\Models\User;

class TipoSolicitudPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('administrador')) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            'agente',
            'jefatura',
            'secretaria_general',
            'presidencia',
        ]);
    }

    public function view(User $user, TipoSolicitud $tipoSolicitud): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return ! $user->hasRole('agente');
    }

    public function update(User $user, TipoSolicitud $tipoSolicitud): bool
    {
        return ! $user->hasRole('agente');
    }

    public function delete(User $user, TipoSolicitud $tipoSolicitud): bool
    {
        return ! $user->hasRole('agente');
    }

    public function restore(User $user, TipoSolicitud $tipoSolicitud): bool
    {
        return false;
    }

    public function forceDelete(User $user, TipoSolicitud $tipoSolicitud): bool
    {
        return false;
    }
}
