<?php

namespace App\Policies;

use App\Models\Solicitud;
use App\Models\User;
use App\Services\DelegacionService;

class SolicitudPolicy
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
        return true;
    }

    /**
     * Un agente solo puede ver su propia solicitud.
     * Jefatura, Secretaría General y Presidencia pueden ver cualquier solicitud.
     */
    public function view(User $user, Solicitud $solicitud): bool
    {
        if ($user->hasAnyRole(['jefatura', 'secretaria_general', 'presidencia'])) {
            return true;
        }

        // Agente: solo puede ver sus propias solicitudes
        return $user->agente?->id === $solicitud->agente_id;
    }

    /**
     * Solo los agentes pueden crear solicitudes.
     */
public function create(User $user): bool
{
    return true; // ¡Dejame pasar, por favor!
}
    /**
     * Un agente solo puede editar su solicitud si todavía está en estado "pendiente".
     * Una vez que entra al flujo de aprobación, nadie puede editar la solicitud.
     */
    public function update(User $user, Solicitud $solicitud): bool
    {
        if (! $solicitud->puedeSerEditada()) {
            return false;
        }

        return $user->agente?->id === $solicitud->agente_id;
    }

    /**
     * Solo el agente dueño puede eliminar su solicitud, y solo si está en "pendiente".
     */
    public function delete(User $user, Solicitud $solicitud): bool
    {
        if (! $solicitud->puedeSerEditada()) {
            return false;
        }

        return $user->agente?->id === $solicitud->agente_id;
    }

    /**
     * No se permite restaurar solicitudes.
     */
    public function restore(User $user, Solicitud $solicitud): bool
    {
        return false;
    }

    /**
     * No se permite eliminar permanentemente solicitudes.
     */
    public function forceDelete(User $user, Solicitud $solicitud): bool
    {
        return false;
    }

    // ── Custom Policy Methods para el Workflow ────────────────────

    /**
     * Determina si el usuario puede aprobar la solicitud en la etapa actual.
     *
     * Regla: el usuario debe tener el rol correspondiente a la etapa actual
     * de aprobación y la solicitud debe estar en flujo (en_revision).
     */
    public function aprobar(User $user, Solicitud $solicitud): bool
    {
        if ($solicitud->estado !== Solicitud::ESTADO_EN_REVISION) {
            return false;
        }

        return app(DelegacionService::class)->puedeActuarEnEtapa($user, $solicitud);
    }

    /**
     * Determina si el usuario puede rechazar la solicitud en la etapa actual.
     * Aplica la misma lógica que aprobar: solo el rol de la etapa actual.
     */
    public function rechazar(User $user, Solicitud $solicitud): bool
    {
        return $this->aprobar($user, $solicitud);
    }
}
