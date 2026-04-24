<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Solicitud;
use App\Models\TipoSolicitud;
use App\Services\CumplimientoLegalService;
use Illuminate\Validation\ValidationException;

$usuario = User::where('email', 'agente@test.com')->first();
$agente = $usuario->agente;

$tipo = TipoSolicitud::where('duracion_maxima_dias', 1)->first();
if (! $tipo) {
    echo "No se encontró TipoSolicitud con duracion_maxima_dias = 1\n";
    exit(1);
}

printf("Usando tipo: %s (id=%d)\n", $tipo->nombre, $tipo->id);

$sol = new Solicitud([
    'agente_id' => $agente->id,
    'tipo_solicitud_id' => $tipo->id,
    'fecha_solicitud' => now()->toDateString(),
    'fecha_inicio' => now()->addDays(1)->toDateString(),
    'fecha_fin' => now()->addDays(10)->toDateString(),
    'dias_solicitados' => 10,
]);

$service = $app->make(CumplimientoLegalService::class);

try {
    $service->validarCreacion($sol);
    echo "Validación pasada (unexpected).\n";
} catch (ValidationException $e) {
    echo "ValidationException capturada:\n";
    foreach ($e->errors() as $field => $msgs) {
        foreach ($msgs as $m) {
            echo " - $field: $m\n";
        }
    }
}
