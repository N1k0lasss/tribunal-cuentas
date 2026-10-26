<?php

use App\Filament\Admin\Widgets\SolicitudesPendientesWidget;
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

it('carga el widget de pendientes y aprueba desde la acción rápida', function () {
    $user = User::factory()->create();
    $user->assignRole('administrador');
    Agente::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    $tipo = TipoSolicitud::factory()->create(['categoria' => 'Extraordinaria']);
    $agente = Agente::factory()->create();

    $solicitud = Solicitud::factory()->create([
        'agente_id' => $agente->id,
        'tipo_solicitud_id' => $tipo->id,
        'estado' => Solicitud::ESTADO_PENDIENTE,
    ]);

    Livewire::test(SolicitudesPendientesWidget::class)
        ->mountAction(TestAction::make('aprobar')->table($solicitud))
        ->setActionData(['observacion' => 'Aprobado desde el dashboard'])
        ->callMountedAction();

    $solicitud->refresh();

    expect($solicitud->estado)->toBe(Solicitud::ESTADO_EN_REVISION)
        ->and($solicitud->aprobaciones()->count())->toBe(1);
});
