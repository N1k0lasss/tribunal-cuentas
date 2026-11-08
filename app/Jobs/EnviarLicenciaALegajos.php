<?php

namespace App\Jobs;

use App\Services\LegajoLicenciaEnvioService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Throwable;

class EnviarLicenciaALegajos implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries;

    public int $timeout;

    public int $uniqueFor;

    public function __construct(public readonly int $envioId)
    {
        $this->tries = (int) config('services.legajos.envio.tries', 5);
        $this->timeout = (int) config('services.legajos.envio.timeout', 30);
        $this->uniqueFor = (int) config('services.legajos.envio.unique_for', 7200);
        $this->onQueue((string) config('services.legajos.envio.queue', 'legajos'));
    }

    /**
     * Evita que se encolen dos jobs para el mismo envío (por ejemplo, si el
     * planificador de respaldo y la aprobación coinciden).
     */
    public function uniqueId(): string
    {
        return (string) $this->envioId;
    }

    /**
     * Esperas entre reintentos: 5, 15, 30 y 60 minutos.
     *
     * @return list<int>
     */
    public function backoff(): array
    {
        return array_map('intval', (array) config('services.legajos.envio.reintentos', [300, 900, 1800, 3600]));
    }

    /**
     * Segunda barrera contra ejecuciones simultáneas del mismo envío.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('legajo-envio-'.$this->envioId))
                ->releaseAfter(30)
                ->expireAfter(120),
        ];
    }

    public function handle(LegajoLicenciaEnvioService $envios): void
    {
        $envios->procesarEnvio($this->envioId);
    }

    public function failed(?Throwable $exception): void
    {
        app(LegajoLicenciaEnvioService::class)->marcarFalloDefinitivo($this->envioId, $exception);
    }
}
