<?php

namespace App\Services;

use App\Models\Delegacion;
use App\Models\Solicitud;
use App\Models\User;
use App\Models\WorkflowEvento;
use Illuminate\Support\Facades\Auth;

class WorkflowAuditoriaService
{
    public function registrar(
        Solicitud $solicitud,
        string $evento,
        ?string $estadoAnterior = null,
        ?string $estadoNuevo = null,
        ?string $etapa = null,
        ?string $detalle = null,
        ?Delegacion $delegacion = null,
        ?User $actor = null,
    ): WorkflowEvento {
        $user = $actor ?? Auth::user();

        return WorkflowEvento::create([
            'solicitud_id' => $solicitud->id,
            'evento' => $evento,
            'etapa' => $etapa,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $estadoNuevo,
            'actor_user_id' => $user?->id,
            'actor_agente_id' => $user?->agente?->id,
            'es_delegacion' => $delegacion !== null,
            'delegacion_id' => $delegacion?->id,
            'detalle' => $detalle,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'ocurrio_en' => now(),
        ]);
    }
}
