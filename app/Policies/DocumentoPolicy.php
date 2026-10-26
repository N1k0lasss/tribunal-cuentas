<?php

namespace App\Policies;

use App\Models\Documento;
use App\Models\User;

class DocumentoPolicy
{
    public function before(User $user, string $ability, Documento|string|null $documento = null): ?bool
    {
        if (in_array($ability, ['restore', 'forceDelete'], true)) {
            return false;
        }

        if ($documento instanceof Documento
            && $documento->esSistema()
            && in_array($ability, ['update', 'delete'], true)) {
            return false;
        }

        if ($user->hasRole('administrador')) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['jefatura', 'secretaria_general', 'presidencia']);
    }

    public function view(User $user, Documento $documento): bool
    {
        if ($user->hasAnyRole(['jefatura', 'secretaria_general', 'presidencia'])) {
            return true;
        }

        if ($documento->solicitud_id === null) {
            return false;
        }

        return $user->agente?->id === $documento->solicitud?->agente_id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['secretaria_general']);
    }

    public function update(User $user, Documento $documento): bool
    {
        return $user->hasAnyRole(['secretaria_general']);
    }

    public function delete(User $user, Documento $documento): bool
    {
        return $user->hasAnyRole(['secretaria_general']);
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasAnyRole(['secretaria_general']);
    }

    public function restore(User $user, Documento $documento): bool
    {
        return false;
    }

    public function forceDelete(User $user, Documento $documento): bool
    {
        return false;
    }
}
