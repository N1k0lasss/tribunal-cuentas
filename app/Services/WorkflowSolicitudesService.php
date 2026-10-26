<?php

namespace App\Services;

use App\Models\Aprobacion;
use App\Models\Solicitud;
use App\Models\User;
use App\Notifications\SolicitudResueltaNotification;
use App\Notifications\SolicitudSaludMaternidadAprobadaNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class WorkflowSolicitudesService
{
    public function __construct(
        private readonly DelegacionService $delegacionService,
        private readonly WorkflowAuditoriaService $auditoriaService,
        private readonly SaldoLicenciaService $saldoLicenciaService,
    ) {}

    public function resolver(Solicitud $solicitud, User $actor, string $estado, ?string $observacion = null): Aprobacion
    {
        return DB::transaction(function () use ($solicitud, $actor, $estado, $observacion): Aprobacion {
            $solicitud->loadMissing('aprobaciones', 'tipoSolicitud', 'agente');

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

            $estadoAnterior = $solicitud->estado;

            $aprobacion = Aprobacion::create([
                'solicitud_id' => $solicitud->id,
                'aprobado_por' => $actor->agente->id,
                'etapa' => $etapa,
                'estado' => $estado,
                'observacion' => $observacion,
                'fecha' => now(config('app.business_timezone')),
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

                if ($solicitud->estado === Solicitud::ESTADO_APROBADO && $solicitud->esCategoriaSaludYMaternidad()) {
                    $this->notificarAprobacionSaludYMaternidad($solicitud);
                }
            }

            return $aprobacion;
        });
    }

    /**
     * Procesa la aprobación o el rechazo de una etapa desde la interfaz.
     */
    public function procesarAprobacion(
        Solicitud $solicitud,
        User $actor,
        string $estado,
        ?string $observacion = null,
    ): void {
        $this->resolver($solicitud, $actor, $estado, $observacion);

        $solicitud->load('aprobaciones');
    }

    private function notificarAprobacionSaludYMaternidad(Solicitud $solicitud): void
    {
        $emails = User::query()
            ->role(['jefatura', 'presidencia'])
            ->whereNotNull('email')
            ->pluck('email')
            ->filter()
            ->unique()
            ->values();

        foreach ($emails as $email) {
            Notification::route('mail', $email)
                ->notify(new SolicitudSaludMaternidadAprobadaNotification($solicitud));
        }
    }
}
