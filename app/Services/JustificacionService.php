<?php

namespace App\Services;

use App\Models\Documento;
use App\Models\Solicitud;
use Illuminate\Validation\ValidationException;

class JustificacionService
{
    private const TIPOS_JUSTIFICACION_VALIDOS = [
        'Certificado Médico',
        'Donación de Sangre',
        'Justificativo',
    ];

    public function registrarDocumento(Documento $documento): void
    {
        if ($documento->solicitud_id === null) {
            return;
        }

        /** @var Solicitud|null $solicitud */
        $solicitud = $documento->solicitud()->with('tipoSolicitud')->first();

        if ($solicitud === null || ! $solicitud->requiereJustificacionMedica()) {
            return;
        }

        if (! in_array($documento->tipo_documento, self::TIPOS_JUSTIFICACION_VALIDOS, true)) {
            throw ValidationException::withMessages([
                'tipo_documento' => 'Para esta solicitud debe adjuntar un certificado o justificativo médico válido.',
            ]);
        }

        if ($solicitud->justificacion_vence_en !== null && now()->greaterThan($solicitud->justificacion_vence_en)) {
            throw ValidationException::withMessages([
                'tipo_documento' => 'Se excedió el plazo legal de 48 horas para presentar el certificado médico.',
            ]);
        }

        app(CumplimientoLegalService::class)->registrarPresentacionJustificacion($solicitud);
    }
}
