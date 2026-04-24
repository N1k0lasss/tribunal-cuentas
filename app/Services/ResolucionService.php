<?php

namespace App\Services;

use App\Models\Resolucion;
use App\Models\Solicitud;
use Illuminate\Validation\ValidationException;

class ResolucionService
{
    public function asegurarResolucionFinal(Solicitud $solicitud): Resolucion
    {
        $solicitud->loadMissing('resolucion');

        if ($solicitud->resolucion !== null) {
            if (
                blank($solicitud->resolucion->numero)
                || blank($solicitud->resolucion->gde_expediente)
                || blank($solicitud->resolucion->gde_documento)
                || $solicitud->resolucion->fecha_emision === null
            ) {
                throw ValidationException::withMessages([
                    'resolucion' => 'La resolución final debe incluir número, fecha de emisión y referencias GDE completas.',
                ]);
            }

            return $solicitud->resolucion;
        }

        throw ValidationException::withMessages([
            'resolucion' => 'Debe registrarse la resolución final (N° de GDE y fecha) antes de completar la aprobación en Presidencia.',
        ]);
    }
}
