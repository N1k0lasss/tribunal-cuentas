<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$status = $kernel->bootstrap();

use App\Models\User;
use App\Models\Solicitud;
use App\Models\TipoSolicitud;
use App\Services\CumplimientoLegalService;
use Illuminate\Validation\ValidationException;

$usuario = User::where('email', 'agente@test.com')->first();
if (! $usuario) {
    echo "Usuario agente@test.com no encontrado\n";
    exit(1);
}
$agente = $usuario->agente;
if (! $agente) {
    echo "Agente sin perfil\n";
    exit(1);
}

$tipo = TipoSolicitud::first();
if (! $tipo) {
    echo "No hay tipos de solicitud en DB\n";
    exit(1);
}

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
    echo "Validación pasada: la solicitud sería creada correctamente.\n";
} catch (ValidationException $e) {
    echo "ValidationException capturada:\n";
    foreach ($e->errors() as $field => $msgs) {
        foreach ($msgs as $m) {
            echo " - $field: $m\n";
        }
    }
}
