<?php

namespace App\Services;

use App\Models\Solicitud;
use App\Models\TipoSolicitud;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class CumplimientoLegalService
{
    public function __construct(
        private readonly SaldoLicenciaService $saldoLicenciaService,
    ) {}

    public function validarCreacion(Solicitud $solicitud): void
    {
        $solicitud->loadMissing('tipoSolicitud');
        $this->validarContratoHabilitado($solicitud);
        $this->validarRangoFechas($solicitud);
        $this->validarDuracionMaxima($solicitud);
        $this->validarSaldoDisponible($solicitud);
        $this->validarProrroga($solicitud);
        $this->calcularVentanaJustificacion($solicitud);
    }

    public function validarContratoHabilitado(Solicitud $solicitud): void
    {
        $solicitud->loadMissing('agente', 'tipoSolicitud');

        if ($solicitud->agente === null || $solicitud->tipoSolicitud === null) {
            return;
        }

        $tipoContrato = (string) $solicitud->agente->tipo_contrato;

        if ($tipoContrato === '' || ! $solicitud->tipoSolicitud->permiteContrato($tipoContrato)) {
            throw ValidationException::withMessages([
                'tipo_solicitud_id' => 'El régimen legal seleccionado no aplica al tipo de contratación del agente.',
            ]);
        }
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

    public function validarSaldoDisponible(Solicitud $solicitud): void
    {
        if ($solicitud->dias_solicitados === null || $solicitud->dias_solicitados <= 0) {
            return;
        }

        $solicitud->loadMissing('agente', 'tipoSolicitud');

        if ($solicitud->agente === null || $solicitud->tipoSolicitud === null) {
            return;
        }

        $disponible = $this->saldoLicenciaService->calcularDiasDisponibles($solicitud->agente, $solicitud->tipoSolicitud);
        $saldos = $this->saldoLicenciaService->obtenerSaldosVigentes($solicitud->agente, $solicitud->tipoSolicitud);

        if ($saldos->isEmpty()) {
            return;
        }

        if ($solicitud->dias_solicitados > $disponible) {
            throw ValidationException::withMessages([
                'dias_solicitados' => 'La cantidad de días solicitados supera el saldo disponible (año actual + 2 anteriores).',
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
}
