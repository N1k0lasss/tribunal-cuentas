<?php

namespace App\Console\Commands;

use App\Services\LegajoLicenciaEnvioService;
use Illuminate\Console\Command;

class EnviarLicenciasLegajos extends Command
{
    protected $signature = 'app:enviar-licencias-legajos';

    protected $description = 'Encola los envíos de licencias aprobadas pendientes de sincronización';

    public function handle(LegajoLicenciaEnvioService $envios): int
    {
        $encolados = $envios->encolarPendientes();

        $this->info("Envíos de licencias encolados: {$encolados}.");

        return self::SUCCESS;
    }
}
