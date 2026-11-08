<?php

namespace App\Services;

use App\Exceptions\LegajoApiException;
use App\Exceptions\LegajoEnvioReintentableException;
use App\Jobs\EnviarLicenciaALegajos;
use App\Models\Documento;
use App\Models\LegajoLicenciaEnvio;
use App\Models\Solicitud;
use App\Models\User;
use App\Notifications\EnvioLicenciaFallidoNotification;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Throwable;

class LegajoLicenciaEnvioService
{
    private const SEGUNDOS_DE_GRACIA = 5;

    private const MINUTOS_PROCESANDO_COLGADO = 5;

    public function programar(Solicitud $solicitud, Documento $documento): LegajoLicenciaEnvio
    {
        $solicitud->loadMissing(['tipoSolicitud', 'agente', 'resolucion']);

        $seEnvia = $solicitud->tipoSolicitud->seEnviaALegajos();

        return LegajoLicenciaEnvio::query()->firstOrCreate(
            ['solicitud_id' => $solicitud->id],
            [
                'documento_id' => $documento->id,
                'dni' => $solicitud->agente->dni,
                'datos' => $this->datosDeSolicitud($solicitud),
                // Sin equivalente en Legajos no se envía: queda registrado
                // para auditoría y visible en el panel, sin generar un error
                // de validación (422) que parezca una falla del sistema.
                'estado' => $seEnvia
                    ? LegajoLicenciaEnvio::ESTADO_PENDIENTE
                    : LegajoLicenciaEnvio::ESTADO_NO_APLICA,
                'proximo_intento_at' => $seEnvia ? now() : null,
                'ultimo_error' => $seEnvia
                    ? null
                    : 'El tipo de solicitud no tiene un equivalente configurado en Legajos.',
            ],
        );
    }

    /**
     * Recalcula el envío con el mapeo actual del régimen y lo deja pendiente.
     *
     * Se usa al reencolar un `no_aplica` después de configurar el equivalente
     * en Legajos: no alcanza con volver a encolarlo, porque el payload guardado
     * conserva el mapeo viejo (o el nombre interno como respaldo).
     *
     * @return bool `false` si el tipo sigue sin equivalente y no debe enviarse.
     */
    public function actualizarDesdeSolicitud(LegajoLicenciaEnvio $envio): bool
    {
        $envio->loadMissing(['solicitud.tipoSolicitud', 'solicitud.agente', 'solicitud.resolucion']);

        $solicitud = $envio->solicitud;

        if ($solicitud === null || ! $solicitud->tipoSolicitud->seEnviaALegajos()) {
            return false;
        }

        $envio->update([
            'datos' => $this->datosDeSolicitud($solicitud),
            'estado' => LegajoLicenciaEnvio::ESTADO_PENDIENTE,
            'intentos' => 0,
            'proximo_intento_at' => now(),
            'iniciado_at' => null,
            'http_status' => null,
            'ultimo_error' => null,
        ]);

        return true;
    }

    /**
     * Arma el payload que se envía a Legajos a partir de la solicitud.
     *
     * @return array<string, int|string|null>
     */
    private function datosDeSolicitud(Solicitud $solicitud): array
    {
        $tipo = $solicitud->tipoSolicitud;

        return [
            'solicitud_id' => $solicitud->id,
            // Se envía el equivalente acordado con Legajos, no el nombre
            // interno: el mapeo vive en tipo_solicituds. El nombre queda solo
            // como respaldo para registros `no_aplica` (no se envían).
            'tipo_licencia' => $tipo->legajos_tipo_licencia ?? $tipo->nombre,
            'fecha_inicio' => $solicitud->fecha_inicio->toDateString(),
            'fecha_fin' => $solicitud->fecha_fin?->toDateString(),
            'dias_solicitados' => $solicitud->dias_solicitados,
            'motivo' => $solicitud->motivo,
            'nro_resolucion' => $solicitud->resolucion?->numero,
            'fecha_aprobacion' => $solicitud->fecha_aprobacion?->toDateString() ?? now()->toDateString(),
        ];
    }

    /**
     * Encola los envíos pendientes cuya hora de reintento ya venció. Es la red
     * de seguridad del outbox: recupera envíos interrumpidos o cuyo job se
     * perdió. El envío en sí lo ejecuta EnviarLicenciaALegajos.
     */
    public function encolarPendientes(int $limite = 25): int
    {
        $this->recuperarInterrumpidos();

        $ids = LegajoLicenciaEnvio::query()
            ->where('estado', LegajoLicenciaEnvio::ESTADO_PENDIENTE)
            ->where(fn ($query) => $query->whereNull('proximo_intento_at')->orWhere('proximo_intento_at', '<=', now()))
            ->orderBy('id')
            ->limit($limite)
            ->pluck('id');

        foreach ($ids as $id) {
            EnviarLicenciaALegajos::dispatch((int) $id)->afterCommit();
        }

        return $ids->count();
    }

    /**
     * Procesa un envío puntual. Lo llama el job; si el fallo es transitorio
     * lanza LegajoEnvioReintentableException para que la cola lo reintente.
     */
    public function procesarEnvio(int $envioId): void
    {
        $envio = $this->reclamar($envioId);

        if ($envio === null) {
            return;
        }

        try {
            $this->procesar($envio);
        } catch (LegajoEnvioReintentableException $exception) {
            // La cola es dueña de la espera y del reintento.
            throw $exception;
        } catch (Throwable $exception) {
            // Un fallo inesperado no debe dejar el envío en `procesando`
            // hasta que el planificador lo libere cinco minutos después.
            report($exception);

            $this->marcarError(
                $envio,
                'Error inesperado al enviar la licencia: '.$exception->getMessage(),
                null,
            );
        }
    }

    /**
     * Marca como error definitivo un envío cuyos reintentos se agotaron.
     */
    public function marcarFalloDefinitivo(int $envioId, ?Throwable $exception): void
    {
        $envio = LegajoLicenciaEnvio::query()->find($envioId);

        if ($envio === null || in_array($envio->estado, [
            LegajoLicenciaEnvio::ESTADO_ENVIADO,
            LegajoLicenciaEnvio::ESTADO_CONFLICTO,
            LegajoLicenciaEnvio::ESTADO_ERROR,
            LegajoLicenciaEnvio::ESTADO_NO_APLICA,
        ], true)) {
            return;
        }

        $detalle = $envio->ultimo_error
            ?? $exception?->getMessage()
            ?? 'Se agotaron los reintentos de envío.';

        $envio->update([
            'estado' => LegajoLicenciaEnvio::ESTADO_ERROR,
            'proximo_intento_at' => null,
            'ultimo_error' => mb_substr($detalle, 0, 4000),
        ]);

        Log::error('Se agotaron los reintentos de envío de licencia al Sistema de Legajos.', [
            'envio_id' => $envio->id,
            'solicitud_id' => $envio->solicitud_id,
            'intentos' => $envio->intentos,
        ]);

        $this->notificarAdministradores($envio, $detalle);
    }

    /**
     * Avisa a los administradores que un envío quedó en error definitivo.
     * Un fallo de mail no debe afectar el estado del envío ni el job.
     */
    private function notificarAdministradores(LegajoLicenciaEnvio $envio, string $detalle): void
    {
        try {
            $emails = User::query()
                ->role('administrador')
                ->whereNotNull('email')
                ->pluck('email')
                ->filter()
                ->unique();
        } catch (Throwable $exception) {
            Log::warning('No se pudieron obtener los administradores para notificar el fallo de envío.', [
                'envio_id' => $envio->id,
                'error' => $exception->getMessage(),
            ]);

            return;
        }

        foreach ($emails as $email) {
            try {
                Notification::route('mail', $email)
                    ->notify(new EnvioLicenciaFallidoNotification($envio, $detalle));
            } catch (Throwable $exception) {
                Log::warning('No se pudo notificar el fallo definitivo del envío de licencia.', [
                    'envio_id' => $envio->id,
                    'email' => $email,
                    'error' => $exception->getMessage(),
                ]);
            }
        }
    }

    private function recuperarInterrumpidos(): void
    {
        LegajoLicenciaEnvio::query()
            ->where('estado', LegajoLicenciaEnvio::ESTADO_PROCESANDO)
            ->where('iniciado_at', '<=', now()->subMinutes(self::MINUTOS_PROCESANDO_COLGADO))
            ->update([
                'estado' => LegajoLicenciaEnvio::ESTADO_PENDIENTE,
                'proximo_intento_at' => now(),
                'ultimo_error' => 'Se recuperó un envío interrumpido.',
            ]);
    }

    private function reclamar(int $id): ?LegajoLicenciaEnvio
    {
        return DB::transaction(function () use ($id): ?LegajoLicenciaEnvio {
            $envio = LegajoLicenciaEnvio::query()->lockForUpdate()->find($id);

            if ($envio === null) {
                return null;
            }

            if ($envio->estado === LegajoLicenciaEnvio::ESTADO_PROCESANDO) {
                // Solo se recupera si el proceso anterior quedó colgado.
                if ($envio->iniciado_at !== null
                    && $envio->iniciado_at->greaterThan(now()->subMinutes(self::MINUTOS_PROCESANDO_COLGADO))) {
                    return null;
                }
            } elseif ($envio->estado !== LegajoLicenciaEnvio::ESTADO_PENDIENTE) {
                return null;
            }

            if ($envio->estado === LegajoLicenciaEnvio::ESTADO_PENDIENTE
                && $envio->proximo_intento_at !== null
                && $envio->proximo_intento_at->greaterThan(now()->addSeconds(self::SEGUNDOS_DE_GRACIA))) {
                return null;
            }

            $envio->update([
                'estado' => LegajoLicenciaEnvio::ESTADO_PROCESANDO,
                'intentos' => $envio->intentos + 1,
                'iniciado_at' => now(),
            ]);

            return $envio->fresh(['documento']);
        });
    }

    private function procesar(LegajoLicenciaEnvio $envio): void
    {
        if (! $envio->documento->rutaValida() || ! $envio->documento->archivoExiste()) {
            $this->marcarError($envio, 'No se encontró el PDF firmado en el almacenamiento privado.', 422);

            return;
        }

        try {
            $legajoId = app(LegajoApiService::class)->obtenerIdLegajo($envio->dni);

            if ($legajoId === null) {
                $this->marcarError($envio, 'No se encontró un legajo activo para el DNI asociado a la solicitud.', 404);

                return;
            }

            $envio->update(['legajo_id' => $legajoId]);

            $response = app(LegajoApiService::class)->enviarLicenciaAprobada(
                $legajoId,
                $envio->datos,
                Storage::disk('local')->path($envio->documento->ruta),
            );
        } catch (ConnectionException|LegajoApiException $exception) {
            $status = $exception instanceof LegajoApiException ? (int) $exception->getCode() : 0;

            if ($this->esErrorNoReintentable($status)) {
                $this->marcarError($envio, $exception->getMessage(), $status);

                return;
            }

            $this->reprogramar($envio, $exception->getMessage(), $status > 0 ? $status : null);

            throw new LegajoEnvioReintentableException($exception->getMessage());
        }

        $status = $response->status();

        if (in_array($status, [200, 201], true)) {
            $envio->update([
                'estado' => LegajoLicenciaEnvio::ESTADO_ENVIADO,
                'enviado_at' => now(),
                'http_status' => $status,
                'ultimo_error' => null,
                'proximo_intento_at' => null,
            ]);

            return;
        }

        $detalle = mb_substr($response->body(), 0, 4000);

        if ($status === 409) {
            $envio->update([
                'estado' => LegajoLicenciaEnvio::ESTADO_CONFLICTO,
                'http_status' => $status,
                'ultimo_error' => 'Legajos respondió 409. Confirmar si la solicitud ya está registrada. '.$detalle,
                'proximo_intento_at' => null,
            ]);

            return;
        }

        if ($this->esErrorNoReintentable($status)) {
            $this->marcarError($envio, "Legajos rechazó el envío (HTTP {$status}): {$detalle}", $status);

            return;
        }

        // 5xx, 408, 425 o 429: lo reintenta el job con su propia espera.
        $this->reprogramar($envio, "HTTP {$status}: {$detalle}", $status);

        throw new LegajoEnvioReintentableException("HTTP {$status}: {$detalle}");
    }

    private function esErrorNoReintentable(int $status): bool
    {
        return $status >= 400
            && $status < 500
            && ! in_array($status, [408, 425, 429], true);
    }

    private function reprogramar(LegajoLicenciaEnvio $envio, string $detalle, ?int $status = null): void
    {
        $retrasos = array_map('intval', (array) config('services.legajos.envio.reintentos', [300, 900, 1800, 3600]));
        $indice = min(max($envio->intentos - 1, 0), count($retrasos) - 1);
        $siguienteIntento = now()->addSeconds($retrasos[$indice]);

        $envio->update([
            'estado' => LegajoLicenciaEnvio::ESTADO_PENDIENTE,
            'http_status' => $status,
            'ultimo_error' => mb_substr($detalle, 0, 4000),
            'proximo_intento_at' => $siguienteIntento,
        ]);

        Log::warning('Se reintentará el envío de licencia al Sistema de Legajos.', [
            'solicitud_id' => $envio->solicitud_id,
            'intento' => $envio->intentos,
            'proximo_intento_at' => $siguienteIntento->toIso8601String(),
            'http_status' => $status,
        ]);
    }

    private function marcarError(LegajoLicenciaEnvio $envio, string $detalle, ?int $status): void
    {
        $envio->update([
            'estado' => LegajoLicenciaEnvio::ESTADO_ERROR,
            'http_status' => $status,
            'ultimo_error' => mb_substr($detalle, 0, 4000),
            'proximo_intento_at' => null,
        ]);

        Log::error('No se pudo enviar la licencia al Sistema de Legajos.', [
            'solicitud_id' => $envio->solicitud_id,
            'http_status' => $status,
        ]);
    }
}
