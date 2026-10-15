<?php

namespace App\Services;

use App\Models\Delegacion;
use App\Models\Solicitud;
use App\Models\User;

class DelegacionService
{
    public function getDelegacionActivaPara(User $user, string $etapa): ?Delegacion
    {
        return Delegacion::query()
            ->where('delegado_user_id', $user->id)
            ->where('etapa', $etapa)
            ->where('activa', true)
            ->where('vigente_desde', '<=', now())
            ->where('vigente_hasta', '>=', now())
            ->orderByDesc('vigente_desde')
            ->first();
    }

    public function puedeActuarEnEtapa(User $user, Solicitud $solicitud, ?string $etapa = null): bool
    {
        if ($user->hasRole('administrador')) {
            return true;
        }

        $etapaActual = $etapa ?? $solicitud->etapaActual();

        if ($etapaActual === null) {
            return false;
        }

        $rolRequerido = Solicitud::ETAPA_ROL_MAP[$etapaActual] ?? null;

        if ($rolRequerido !== null && $user->hasRole($rolRequerido)) {
            return true;
        }

        return $this->getDelegacionActivaPara($user, $etapaActual) !== null;
    }
}
