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
        return true;
    }

    public function view(User $user, TipoSolicitud $tipoSolicitud): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, TipoSolicitud $tipoSolicitud): bool
    {
        return false;
    }

    public function delete(User $user, TipoSolicitud $tipoSolicitud): bool
    {
        return false;
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
