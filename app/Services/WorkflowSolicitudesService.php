<?php

namespace App\Services;

use App\Models\Aprobacion;
use App\Models\Solicitud;
use App\Models\User;
use App\Notifications\SolicitudResueltaNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class WorkflowSolicitudesService
{
    public function __construct(
        private readonly DelegacionService $delegacionService,
        private readonly WorkflowAuditoriaService $auditoriaService,
        private readonly SaldoLicenciaService $saldoLicenciaService,
        private readonly ResolucionService $resolucionService,
    ) {}

    public function enviarParaRevision(Solicitud $solicitud, User $actor): void
    {
        if ($actor->agente?->id !== $solicitud->agente_id) {
            throw ValidationException::withMessages([
                'autorizacion' => 'Solo el agente titular puede enviar la solicitud a revisión.',
            ]);
        }

        if ($solicitud->estado !== Solicitud::ESTADO_PENDIENTE) {
            throw ValidationException::withMessages([
                'estado' => 'Solo se pueden enviar solicitudes en estado pendiente.',
            ]);
        }

        $estadoAnterior = $solicitud->estado;

        $solicitud->update([
            'estado' => Solicitud::ESTADO_EN_REVISION,
        ]);

        $this->auditoriaService->registrar(
            solicitud: $solicitud,
            evento: 'solicitud_enviada_revision',
            estadoAnterior: $estadoAnterior,
            estadoNuevo: $solicitud->estado,
            detalle: 'Solicitud enviada por agente al circuito de aprobación.',
            actor: $actor,
        );
    }

    public function resolver(Solicitud $solicitud, User $actor, string $estado, ?string $observacion = null): Aprobacion
    {
        return DB::transaction(function () use ($solicitud, $actor, $estado, $observacion): Aprobacion {
            $solicitud->loadMissing('aprobaciones', 'tipoSolicitud', 'agente');

            if ($solicitud->estado !== Solicitud::ESTADO_EN_REVISION) {
                throw ValidationException::withMessages([
                    'estado' => 'Solo se puede aprobar o rechazar una solicitud en revisión.',
                ]);
            }

            $etapa = $solicitud->etapaActual();

            if ($etapa === null) {
                throw ValidationException::withMessages([
                    'solicitud' => 'La solicitud no tiene una etapa disponible para resolver.',
                ]);
            }

            if (! in_array($estado, ['aprobado', 'rechazado'], true)) {
                throw ValidationException::withMessages([
                    'estado' => 'Estado de resolución inválido.',
                ]);
            }

            if (! $this->delegacionService->puedeActuarEnEtapa($actor, $solicitud, $etapa)) {
                throw ValidationException::withMessages([
                    'autorizacion' => 'No tiene autorización para resolver esta etapa.',
                ]);
            }

            if ($actor->agente?->id === null) {
                throw ValidationException::withMessages([
                    'autorizacion' => 'El usuario autenticado no tiene un agente asociado para firmar.',
                ]);
            }

            $delegacion = $this->delegacionService->getDelegacionActivaPara($actor, $etapa);

            if ($solicitud->aprobaciones()->where('etapa', $etapa)->exists()) {
                throw ValidationException::withMessages([
                    'solicitud' => 'La etapa actual ya fue resuelta previamente.',
                ]);
            }

            if ($estado === 'aprobado' && $etapa === Solicitud::ETAPA_DIRECCION) {
                $this->resolucionService->asegurarResolucionFinal($solicitud);
            }

            $estadoAnterior = $solicitud->estado;

            $aprobacion = Aprobacion::create([
                'solicitud_id' => $solicitud->id,
                'aprobado_por' => $actor->agente->id,
                'etapa' => $etapa,
                'estado' => $estado,
                'observacion' => $observacion,
                'fecha' => now(),
            ]);

            if ($estado === 'rechazado') {
                $solicitud->update(['estado' => Solicitud::ESTADO_RECHAZADO]);
            } else {
                $siguienteEtapa = $solicitud->siguienteEtapa();

                if ($siguienteEtapa === null) {
                    $solicitud->update(['estado' => Solicitud::ESTADO_APROBADO]);
                    $this->saldoLicenciaService->consumirSaldoPorAprobacion($solicitud);
                } else {
                    $solicitud->update(['estado' => Solicitud::ESTADO_EN_REVISION]);
                }
            }

            $this->auditoriaService->registrar(
                solicitud: $solicitud,
                evento: 'solicitud_resuelta',
                etapa: $etapa,
                estadoAnterior: $estadoAnterior,
                estadoNuevo: $solicitud->estado,
                detalle: $observacion,
                delegacion: $delegacion,
                actor: $actor,
            );

            if ($solicitud->estado === Solicitud::ESTADO_APROBADO || $solicitud->estado === Solicitud::ESTADO_RECHAZADO) {
                $email = $solicitud->agente?->user?->email;

                if ($email !== null) {
                    Notification::route('mail', $email)
                        ->notify(new SolicitudResueltaNotification($solicitud));
                }
            }

            return $aprobacion;
        });
    }
}
