<?php

namespace App\Policies;

use App\Models\Resolucion;
use App\Models\User;

class ResolucionPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        // Las resoluciones no se eliminan nunca: ni siquiera un administrador
        // puede saltar esta restricción.
        if (in_array($ability, ['delete', 'forceDelete'], true)) {
            return false;
        }

        if ($user->hasRole('administrador') && $ability !== 'update') {
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

        if ($resolucion->solicitud_id === null) {
            return false;
        }

        return $user->agente?->id === $resolucion->solicitud?->agente_id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['secretaria_general']);
    }

    public function update(User $user, Resolucion $resolucion): bool
    {
        if ($resolucion->tienePdfFirmado()) {
            return false;
        }

        return $user->hasAnyRole(['secretaria_general', 'administrador']);
    }

    public function subirPdfFirmadoDelDia(User $user): bool
    {
        return $user->hasRole('secretaria_general');
    }

    public function reemplazarPdfFirmadoDelDia(User $user): bool
    {
        return $user->hasRole('administrador');
    }

    public function delete(User $user, Resolucion $resolucion): bool
    {
        return false;
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
