<?php

namespace App\Services;

use App\Models\Aprobacion;
use App\Models\Documento;
use App\Models\Solicitud;
use App\Models\User;
use App\Notifications\SolicitudSaludMaternidadAprobadaNotification;
use App\Notifications\SolicitudResueltaNotification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class WorkflowSolicitudesService
{
    public function __construct(
        private readonly DelegacionService $delegacionService,
        private readonly WorkflowAuditoriaService $auditoriaService,
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
                'fecha' => now(),
            ]);

            if ($estado === 'rechazado') {
                $solicitud->update(['estado' => Solicitud::ESTADO_RECHAZADO]);
            } else {
                $siguienteEtapa = $solicitud->siguienteEtapa();

                if ($siguienteEtapa === null) {
                    $solicitud->update(['estado' => Solicitud::ESTADO_APROBADO]);
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
     * Procesa la resolución de una etapa desde la interfaz: valida los candados,
     * registra la aprobación/rechazo, persiste el número de resolución y guarda el
     * documento firmado de la etapa.
     *
     * @param  string|array<int, string>|null  $documentoFirmado
     */
    public function procesarAprobacion(
        Solicitud $solicitud,
        User $actor,
        string $estado,
        ?string $observacion = null,
        ?string $numeroResolucion = null,
        string|array|null $documentoFirmado = null,
        bool $confirmoDocumentoIntegrado = false,
    ): void {
        // El FileUpload puede entregar el estado como string (subida simple) o
        // como arreglo; normalizamos a la primera ruta por robustez.
        if (is_array($documentoFirmado)) {
            $documentoFirmado = Arr::first($documentoFirmado);
        }

        // Candado de servidor: no se puede aprobar sin el documento firmado,
        // aunque el botón de la interfaz se hubiera habilitado por error.
        if ($estado === 'aprobado' && blank($documentoFirmado)) {
            throw ValidationException::withMessages([
                'documento_firmado' => 'Debe adjuntar el documento de la solicitud firmado digitalmente para poder aprobar.',
            ]);
        }

        if ($estado === 'aprobado' && filled($documentoFirmado) && ! Storage::disk('local')->exists($documentoFirmado)) {
            throw ValidationException::withMessages([
                'documento_firmado' => 'El archivo firmado no se encontró en el servidor. Vuelva a adjuntarlo e intente nuevamente.',
            ]);
        }

        // Candado de servidor para Secretaría General: el número de resolución se
        // integra en el documento firmado. El número se carga y se persiste antes
        // (campo del formulario, al salir del campo) o llega como respaldo por
        // parámetro; en cualquiera de los dos casos la resolución debe existir
        // para poder aprobar.
        $esEtapaSecretaria = $solicitud->etapaActual() === Solicitud::ETAPA_SECRETARIA;

        if ($estado === 'aprobado' && $esEtapaSecretaria && blank($numeroResolucion) && ! $solicitud->resolucion()->exists()) {
            throw ValidationException::withMessages([
                'numero_resolucion' => 'Debe indicar el número de resolución antes de aprobar en Secretaría General.',
            ]);
        }

        if ($estado === 'aprobado' && $esEtapaSecretaria && ! $confirmoDocumentoIntegrado) {
            throw ValidationException::withMessages([
                'confirmo_documento_integrado' => 'Debe confirmar que descargó el documento integrado con el número de resolución y lo firmó antes de aprobar.',
            ]);
        }

        $aprobacion = $this->resolver($solicitud, $actor, $estado, $observacion);

        if (filled($numeroResolucion)) {
            // El número puede venir desde el modal (compatibilidad) o ya estar
            // persistido por el campo del formulario. En ambos casos se guarda;
            // si la resolución ya existe, se respetan su fecha y su detalle.
            $existente = $solicitud->resolucion()->first();

            $solicitud->resolucion()->updateOrCreate(
                ['solicitud_id' => $solicitud->id],
                [
                    'numero' => $numeroResolucion,
                    'fecha_emision' => $existente?->fecha_emision ?? now(),
                    'detalle' => $existente?->detalle ?? 'Creada automáticamente al aprobar la solicitud.',
                ]
            );
        }

        if ($estado === 'aprobado' && filled($documentoFirmado)) {
            $this->registrarDocumentoFirmado($solicitud, $aprobacion, $documentoFirmado, $actor);
        }

        $solicitud->load('aprobaciones');
    }

    /**
     * Guarda el documento firmado y lo vincula a la aprobación (etapa) correspondiente.
     */
    private function registrarDocumentoFirmado(Solicitud $solicitud, Aprobacion $aprobacion, string $ruta, User $actor): void
    {
        $etapaLabel = match ($aprobacion->etapa) {
            Solicitud::ETAPA_JEFATURA => 'Jefatura',
            Solicitud::ETAPA_SECRETARIA => 'Secretaría General',
            Solicitud::ETAPA_DIRECCION => 'Presidencia',
            default => $aprobacion->etapa,
        };

        Documento::conPermisoDocumentoSistema(function () use ($solicitud, $aprobacion, $ruta, $actor, $etapaLabel): void {
            $solicitud->documentos()->create([
                'aprobacion_id' => $aprobacion->id,
                'agente_id' => $actor->agente?->id,
                'tipo_documento' => Documento::TIPO_SOLICITUD_FIRMADA,
                'nombre_archivo' => "Solicitud {$solicitud->id} firmada - {$etapaLabel}",
                'ruta' => $ruta,
                'descripcion' => "Documento firmado digitalmente en la etapa {$etapaLabel}.",
            ]);
        });
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
