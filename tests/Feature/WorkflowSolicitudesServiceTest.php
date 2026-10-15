<?php

use App\Models\Agente;
use App\Models\Solicitud;
use App\Models\TipoSolicitud;
use App\Models\User;
use App\Policies\SolicitudPolicy;
use App\Notifications\SolicitudResueltaNotification;
use App\Notifications\SolicitudSaludMaternidadAprobadaNotification;
use App\Services\WorkflowSolicitudesService;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    foreach (['administrador', 'agente', 'jefatura', 'secretaria_general', 'presidencia'] as $role) {
        Role::findOrCreate($role, 'web');
    }
});

it('resuelve salud y maternidad solo con secretaria y notifica por mail a jefatura y presidencia', function () {
    Notification::fake();

    $tipoSalud = TipoSolicitud::factory()->create([
        'categoria' => Solicitud::CATEGORIA_SALUD_MATERNIDAD,
    ]);

    $agenteSolicitante = Agente::factory()->create();
    $userSolicitante = User::factory()->create(['email' => 'agente@example.test']);
    $agenteSolicitante->update(['user_id' => $userSolicitante->id]);

    $solicitud = Solicitud::factory()->create([
        'agente_id' => $agenteSolicitante->id,
        'tipo_solicitud_id' => $tipoSalud->id,
        'estado' => Solicitud::ESTADO_PENDIENTE,
    ]);

    $userSecretaria = User::factory()->create();
    $userSecretaria->assignRole('secretaria_general');
    $agenteSecretaria = Agente::factory()->create(['user_id' => $userSecretaria->id]);

    $userJefatura = User::factory()->create(['email' => 'jefatura@example.test']);
    $userJefatura->assignRole('jefatura');

    $userPresidencia = User::factory()->create(['email' => 'presidencia@example.test']);
    $userPresidencia->assignRole('presidencia');

    $service = app(WorkflowSolicitudesService::class);
    $service->resolver($solicitud, $userSecretaria, 'aprobado', 'OK');

    $solicitud->refresh();

    expect($solicitud->estado)->toBe(Solicitud::ESTADO_APROBADO)
        ->and($solicitud->aprobaciones()->count())->toBe(1)
        ->and($solicitud->aprobaciones()->first()?->etapa)->toBe(Solicitud::ETAPA_SECRETARIA);

    Notification::assertSentOnDemand(
        SolicitudResueltaNotification::class,
        function ($notification, $channels, $notifiable) {
            return in_array('mail', $channels, true)
                && $notifiable->routes['mail'] === 'agente@example.test';
        }
    );

    Notification::assertSentOnDemand(
        SolicitudSaludMaternidadAprobadaNotification::class,
        function ($notification, $channels, $notifiable) {
            return in_array('mail', $channels, true)
                && $notifiable->routes['mail'] === 'jefatura@example.test';
        }
    );

    Notification::assertSentOnDemand(
        SolicitudSaludMaternidadAprobadaNotification::class,
        function ($notification, $channels, $notifiable) {
            return in_array('mail', $channels, true)
                && $notifiable->routes['mail'] === 'presidencia@example.test';
        }
    );
});

it('mantiene flujo completo para regimenes que no son salud y maternidad', function () {
    $tipoOrdinario = TipoSolicitud::factory()->create([
        'categoria' => 'Extraordinaria',
    ]);

    $solicitud = Solicitud::factory()->create([
        'tipo_solicitud_id' => $tipoOrdinario->id,
        'estado' => Solicitud::ESTADO_PENDIENTE,
    ]);

    expect($solicitud->flujoEtapas())->toBe([
        Solicitud::ETAPA_JEFATURA,
        Solicitud::ETAPA_SECRETARIA,
        Solicitud::ETAPA_DIRECCION,
    ])
        ->and($solicitud->etapaActual())->toBe(Solicitud::ETAPA_JEFATURA);
});

it('solo permite aprobar salud y maternidad a secretaria general', function () {
    $tipoSalud = TipoSolicitud::factory()->create([
        'categoria' => Solicitud::CATEGORIA_SALUD_MATERNIDAD,
    ]);

    $solicitud = Solicitud::factory()->create([
        'tipo_solicitud_id' => $tipoSalud->id,
        'estado' => Solicitud::ESTADO_PENDIENTE,
    ]);

    $userJefatura = User::factory()->create();
    $userJefatura->assignRole('jefatura');

    $userSecretaria = User::factory()->create();
    $userSecretaria->assignRole('secretaria_general');

    $userPresidencia = User::factory()->create();
    $userPresidencia->assignRole('presidencia');

    $policy = app(SolicitudPolicy::class);

    expect($policy->aprobar($userJefatura, $solicitud))->toBeFalse()
        ->and($policy->aprobar($userSecretaria, $solicitud))->toBeTrue()
        ->and($policy->aprobar($userPresidencia, $solicitud))->toBeFalse();
});
