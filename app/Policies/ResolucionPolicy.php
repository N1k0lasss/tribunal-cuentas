<?php

namespace App\Policies;

use App\Models\Resolucion;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ResolucionPolicy
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
        return $user->hasAnyRole(['jefatura', 'secretaria_general', 'presidencia']);
    }

    public function view(User $user, Resolucion $resolucion): bool
    {
        if ($user->hasAnyRole(['jefatura', 'secretaria_general', 'presidencia'])) {
            return true;
        }

        return $user->agente?->id === $resolucion->solicitud->agente_id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['secretaria_general']);
    }

    public function update(User $user, Resolucion $resolucion): bool
    {
        return $user->hasAnyRole(['secretaria_general']);
    }

    public function delete(User $user, Resolucion $resolucion): bool
    {
        return $user->hasAnyRole(['secretaria_general']);
    }

    public function restore(User $user, Resolucion $resolucion): bool
    {
        return false;
    }

    public function forceDelete(User $user, Resolucion $resolucion): bool
    {
        return false;
    }
}
