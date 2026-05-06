<?php

namespace App\Models\Concerns;

use App\Models\Solicitud;
use App\Services\CumplimientoLegalService;
use Illuminate\Validation\ValidationException;

trait InteractsWithWorkflowRules
{
    protected static function bootInteractsWithWorkflowRules(): void
    {
        static::creating(function (Solicitud $solicitud): void {
            app(CumplimientoLegalService::class)->validarCreacion($solicitud);
        });

        static::updating(function (Solicitud $solicitud): void {
            if (! $solicitud->isDirty(['estado', 'justificacion_presentada_en', 'prorroga_vence_en']) && $solicitud->estaEnFlujo()) {
                throw ValidationException::withMessages([
                    'solicitud' => 'No se permite modificar datos de una solicitud que ya ingresó al flujo.',
                ]);
            }
        });
    }
}
