<?php

use App\Filament\Admin\Resources\Solicituds\Pages\ListSolicituds;
use App\Filament\Admin\Resources\Solicituds\SolicitudResource;
use App\Models\Agente;
use App\Models\Solicitud;
use App\Models\TipoSolicitud;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    foreach (['administrador', 'agente', 'jefatura', 'secretaria_general', 'presidencia'] as $role) {
        Role::findOrCreate($role, 'web');
    }

    Notification::fake();
});

/**
 * Crea una solicitud en estado pendiente (flujo ordinario) junto con un usuario
 * de jefatura habilitado para resolver la primera etapa.
 */
function crearSolicitudConJefatura(): array
{
    $tipo = TipoSolicitud::factory()->create(['categoria' => 'Extraordinaria']);

    $agenteSolicitante = Agente::factory()->create();
    $userSolicitante = User::factory()->create();
    $userSolicitante->assignRole('agente');
    $agenteSolicitante->update(['user_id' => $userSolicitante->id]);

    $solicitud = Solicitud::factory()->create([
        'agente_id' => $agenteSolicitante->id,
        'tipo_solicitud_id' => $tipo->id,
        'estado' => Solicitud::ESTADO_PENDIENTE,
    ]);

    $userJefatura = User::factory()->create();
    $userJefatura->assignRole('jefatura');
    Agente::factory()->create(['user_id' => $userJefatura->id]);

    return [$solicitud, $userJefatura, $userSolicitante];
}

it('aprueba la solicitud solo con confirmación, sin documento firmado ni número de resolución', function () {
    [$solicitud, $userJefatura] = crearSolicitudConJefatura();

    $this->actingAs($userJefatura);

    SolicitudResource::procesarAprobacion($solicitud, 'aprobado', 'Todo en orden');

    $solicitud->refresh();

    expect($solicitud->estado)->toBe(Solicitud::ESTADO_EN_REVISION)
        ->and($solicitud->aprobaciones()->count())->toBe(1)
        ->and($solicitud->documentos()->count())->toBe(0);
});

it('permite rechazar sin exigir el documento firmado', function () {
    [$solicitud, $userJefatura] = crearSolicitudConJefatura();

    $this->actingAs($userJefatura);

    SolicitudResource::procesarAprobacion($solicitud, 'rechazado', 'Falta documentación');

    $solicitud->refresh();

    expect($solicitud->estado)->toBe(Solicitud::ESTADO_RECHAZADO)
        ->and($solicitud->documentos()->count())->toBe(0);
});

it('aprueba desde el modal sin cargar archivo', function () {
    [$solicitud, $userJefatura] = crearSolicitudConJefatura();

    $this->actingAs($userJefatura);

    Livewire::test(ListSolicituds::class)
        ->mountAction(TestAction::make('aprobar')->table($solicitud))
        ->setActionData([
            'observacion' => 'Sin observaciones',
        ])
        ->callMountedAction();

    $solicitud->refresh();

    expect($solicitud->estado)->toBe(Solicitud::ESTADO_EN_REVISION);
});

it('muestra solo observación en el modal de aprobar', function () {
    [$solicitud, $userJefatura] = crearSolicitudConJefatura();

    $this->actingAs($userJefatura);

    Livewire::test(ListSolicituds::class)
        ->mountAction(TestAction::make('aprobar')->table($solicitud))
        ->assertFormFieldExists('observacion')
        ->assertFormFieldDoesNotExist('documento_firmado')
        ->assertFormFieldDoesNotExist('numero_resolucion')
        ->assertFormFieldDoesNotExist('confirmo_documento_integrado');
});
