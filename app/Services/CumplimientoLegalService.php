<?php

namespace App\Services;

use App\Exceptions\LegajoApiException;
use App\Models\Agente;
use App\Models\Solicitud;
use App\Models\TipoSolicitud;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class CumplimientoLegalService
{
    public function __construct(
        private readonly LegajoApiService $legajoApi,
    ) {}

    public function validarCreacion(Solicitud $solicitud): void
    {
        $solicitud->loadMissing('tipoSolicitud');
        $this->validarRangoFechas($solicitud);
        $this->validarDuracionMaxima($solicitud);
        $this->validarEstadoLegajo($solicitud);
        $this->validarProrroga($solicitud);
        $this->validarAntiguedad($solicitud);
        $this->calcularVentanaJustificacion($solicitud);
    }

    public function validarRangoFechas(Solicitud $solicitud): void
    {
        if ($solicitud->fecha_fin === null || $solicitud->fecha_inicio === null) {
            return;
        }

        $fechaInicio = Carbon::parse($solicitud->fecha_inicio);
        $fechaFin = Carbon::parse($solicitud->fecha_fin);

        if ($fechaFin->lessThan($fechaInicio)) {
            throw ValidationException::withMessages([
                'fecha_fin' => 'La fecha fin no puede ser anterior a la fecha inicio.',
            ]);
        }
    }

    public function validarDuracionMaxima(Solicitud $solicitud): void
    {
        /** @var TipoSolicitud|null $tipo */
        $tipo = $solicitud->tipoSolicitud;

        if ($tipo === null || $tipo->duracion_maxima_dias === null || $solicitud->dias_solicitados === null) {
            return;
        }

        if ($solicitud->dias_solicitados > $tipo->duracion_maxima_dias) {
            throw ValidationException::withMessages([
                'dias_solicitados' => 'La cantidad de días excede el máximo legal para este régimen.',
            ]);
        }
    }

    public function validarProrroga(Solicitud $solicitud): void
    {
        if (! $solicitud->es_prorroga) {
            return;
        }

        $solicitud->loadMissing('solicitudOrigen');

        if ($solicitud->solicitudOrigen === null) {
            throw ValidationException::withMessages([
                'solicitud_origen_id' => 'Debe seleccionar una solicitud origen para registrar la prórroga.',
            ]);
        }

        if (! in_array($solicitud->solicitudOrigen->estado, [Solicitud::ESTADO_EN_REVISION, Solicitud::ESTADO_APROBADO], true)) {
            throw ValidationException::withMessages([
                'solicitud_origen_id' => 'Solo se puede prorrogar una solicitud activa o aprobada.',
            ]);
        }

        $vencimiento = $solicitud->solicitudOrigen->fecha_fin?->copy()->addHours(48);

        if ($vencimiento === null || now()->greaterThan($vencimiento)) {
            throw ValidationException::withMessages([
                'solicitud_origen_id' => 'La prórroga excede el plazo legal de 48 horas desde la finalización prevista.',
            ]);
        }

        $solicitud->prorroga_vence_en = $vencimiento;
    }

    public function calcularVentanaJustificacion(Solicitud $solicitud): void
    {
        if (! $solicitud->requiereJustificacionMedica()) {
            return;
        }

        $base = $solicitud->fecha_inicio?->copy() ?? now();
        $solicitud->justificacion_vence_en = $base->addHours(48);
    }

    public function registrarPresentacionJustificacion(Solicitud $solicitud): void
    {
        $solicitud->justificacion_presentada_en = now();
        $solicitud->save();
    }

    public function validarAntiguedad(Solicitud $solicitud): void
    {
        $solicitud->loadMissing('agente', 'tipoSolicitud');

        $antiguedadRequerida = $solicitud->tipoSolicitud?->antiguedad_minima_meses;
        if (! $antiguedadRequerida || ! $solicitud->agente) {
            return;
        }

        $mesesAntiguedad = $this->mesesDeAntiguedad($solicitud->agente);

        if ($mesesAntiguedad === null) {
            return;
        }

        if ($mesesAntiguedad < $antiguedadRequerida) {
            throw ValidationException::withMessages([
                'tipo_solicitud_id' => "Se requiere una antigüedad mínima de {$antiguedadRequerida} meses para solicitar esta licencia (antigüedad actual: {$mesesAntiguedad} meses).",
            ]);
        }
    }

    /**
     * Valida que el agente tenga un legajo activo en el Sistema de Legajos.
     *
     * El estado se consulta en vivo (fuente de verdad); si la API no responde
     * o el agente no figura, se usa el `estado_legajo` local como respaldo.
     * Si no se puede determinar el estado, NO bloquea (misma política que la
     * antigüedad: nunca impedir el alta por una caída de la API).
     */
    public function validarEstadoLegajo(Solicitud $solicitud): void
    {
        $solicitud->loadMissing('agente');

        $agente = $solicitud->agente;

        if ($agente === null) {
            return;
        }

        $estado = null;

        try {
            $datos = $this->legajoApi->datosBasicos((string) $agente->dni);
            $estado = $datos['estado'] ?? null;
        } catch (LegajoApiException) {
            $estado = null;
        }

        $estado ??= $agente->estado_legajo;

        if ($estado === null) {
            return;
        }

        if ((int) $estado !== 1) {
            throw ValidationException::withMessages([
                'agente_id' => 'El agente no tiene un legajo activo en el Sistema de Legajos (figura dado de baja).',
            ]);
        }
    }

    /**
     * Antigüedad en meses de un agente. La fecha de ingreso la aporta el
     * Sistema de Legajos por API (fuente de verdad, ver
     * integracion-legajos-api.md §2.1); si la API no responde o el agente no
     * figura con legajo activo, se usa la fecha local de `agentes` como
     * respaldo para no cortar el alta de solicitudes.
     */
    private function mesesDeAntiguedad(Agente $agente): ?int
    {
        try {
            $fecha = $this->legajoApi->fechaIngreso((string) $agente->dni);
        } catch (LegajoApiException) {
            $fecha = null;
        }

        $fecha ??= $agente->fecha_ingreso;

        if ($fecha === null) {
            return null;
        }

        // Carbon 3 devuelve diffInMonths() como float: se trunca a meses completos.
        return (int) Carbon::parse($fecha)->diffInMonths(now());
    }
}
