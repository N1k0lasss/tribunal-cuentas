<?php

namespace App\Policies;

use App\Models\Aprobacion;
use App\Models\User;

class AprobacionPolicy
{
    /**
     * Administradores tienen acceso total al recurso.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('administrador')) {
            return true;
        }

        return null;
    }

    /**
     * Todos los usuarios autenticados pueden acceder al listado.
     * El scope en getEloquentQuery() se encarga de filtrar los registros visibles.
     */
    public function viewAny(User $user): bool
    {
        return ! $user->hasRole('agente');
    }

    /**
     * Un agente solo puede ver su propia Aprobacion.
     * Jefatura, Secretaría General y Presidencia pueden ver cualquier Aprobacion.
     */
    public function view(User $user, Aprobacion $aprobacion): bool
    {
        if ($user->hasAnyRole(['jefatura', 'secretaria_general', 'presidencia'])) {
            return true;
        }

        // Agente: solo puede ver sus propias Aprobaciones
        return $user->agente?->id === $aprobacion->solicitud->agente_id;
    }

    /**
     * Solo los agentes pueden crear Aprobaciones.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Un agente solo puede editar su Aprobacion si todavía está en estado "pendiente".
     * Una vez que entra al flujo de aprobación, nadie puede editar la Aprobacion.
     */
    public function update(User $user, Aprobacion $Aprobacion): bool
    {
        return false;
    }

    /**
     * Solo el agente dueño puede eliminar su Aprobacion, y solo si está en "pendiente".
     */
    public function delete(User $user, Aprobacion $Aprobacion): bool
    {
        return false;
    }

    /**
     * No se permite restaurar Aprobaciones.
     */
    public function restore(User $user, Aprobacion $Aprobacion): bool
    {
        return false;
    }

    /**
     * No se permite eliminar permanentemente Aprobaciones.
     */
    public function forceDelete(User $user, Aprobacion $Aprobacion): bool
    {
        return false;
    }
}
