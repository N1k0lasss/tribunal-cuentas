<?php

use App\Jobs\EnviarLicenciaALegajos;
use App\Models\Agente;
use App\Models\LegajoLicenciaEnvio;
use App\Models\Solicitud;
use App\Models\TipoSolicitud;
use App\Models\User;
use App\Notifications\SolicitudResueltaNotification;
use App\Notifications\SolicitudSaludMaternidadAprobadaNotification;
use App\Policies\SolicitudPolicy;
use App\Services\LegajoLicenciaEnvioService;
use App\Services\WorkflowSolicitudesService;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
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

it('registra el envío pendiente al aprobar con la última firma y el pdf firmado', function () {
    Notification::fake();
    Bus::fake();
    Storage::fake('local');

    $tipoSalud = TipoSolicitud::factory()->create([
        'categoria' => Solicitud::CATEGORIA_SALUD_MATERNIDAD,
    ]);
    $agenteSolicitante = Agente::factory()->create(['dni' => '20987654']);
    $solicitud = Solicitud::factory()->create([
        'agente_id' => $agenteSolicitante->id,
        'tipo_solicitud_id' => $tipoSalud->id,
        'estado' => Solicitud::ESTADO_PENDIENTE,
    ]);

    $userSecretaria = User::factory()->create();
    $userSecretaria->assignRole('secretaria_general');
    Agente::factory()->create(['user_id' => $userSecretaria->id]);
    $rutaPdf = 'documentos/firmados/licencia-final.pdf';
    Storage::disk('local')->put($rutaPdf, "%PDF-1.4\nPDF firmado\n%%EOF");

    app(WorkflowSolicitudesService::class)->procesarAprobacion(
        $solicitud,
        $userSecretaria,
        'aprobado',
        null,
        'RESOL-2026-100',
        $rutaPdf,
        true,
    );

    $envio = LegajoLicenciaEnvio::query()->sole();

    Bus::assertDispatched(
        EnviarLicenciaALegajos::class,
        fn (EnviarLicenciaALegajos $job): bool => $job->envioId === $envio->id,
    );

    expect($solicitud->fresh()->estado)->toBe(Solicitud::ESTADO_APROBADO)
        ->and($envio->solicitud_id)->toBe($solicitud->id)
        ->and($envio->dni)->toBe('20987654')
        ->and($envio->documento->ruta)->toBe($rutaPdf)
        ->and($envio->datos['nro_resolucion'])->toBe('RESOL-2026-100')
        ->and($envio->estado)->toBe(LegajoLicenciaEnvio::ESTADO_PENDIENTE);
});

it('encola el envío al aprobar y el job envía la licencia sin bloquear la respuesta', function () {
    Notification::fake();
    Bus::fake();
    Storage::fake('local');
    config([
        'services.legajos.url' => 'https://legajos.test/api',
        'services.legajos.token' => 'token-de-prueba',
    ]);

    $tipoSalud = TipoSolicitud::factory()->create([
        'categoria' => Solicitud::CATEGORIA_SALUD_MATERNIDAD,
    ]);
    $agenteSolicitante = Agente::factory()->create(['dni' => '20987654']);
    $solicitud = Solicitud::factory()->create([
        'agente_id' => $agenteSolicitante->id,
        'tipo_solicitud_id' => $tipoSalud->id,
        'estado' => Solicitud::ESTADO_PENDIENTE,
    ]);

    $userSecretaria = User::factory()->create();
    $userSecretaria->assignRole('secretaria_general');
    Agente::factory()->create(['user_id' => $userSecretaria->id]);
    $rutaPdf = 'documentos/firmados/licencia-inmediata.pdf';
    Storage::disk('local')->put($rutaPdf, "%PDF-1.4\nPDF firmado\n%%EOF");

    Http::fake([
        'legajos.test/api/legajos/500/licencias' => Http::response(['ok' => true], 201),
        'legajos.test/api/legajos/por-dni/20987654' => Http::response([
            'persona' => ['dni' => '20987654'],
            'legajo' => ['id' => 500],
        ]),
    ]);

    app(WorkflowSolicitudesService::class)->procesarAprobacion(
        $solicitud,
        $userSecretaria,
        'aprobado',
        null,
        'RESOL-2026-101',
        $rutaPdf,
        true,
    );

    $envio = LegajoLicenciaEnvio::query()->sole();

    Bus::assertDispatched(
        EnviarLicenciaALegajos::class,
        fn (EnviarLicenciaALegajos $job): bool => $job->envioId === $envio->id,
    );

    // La aprobación solo encola: todavía no se llamó a Legajos.
    expect($envio->estado)->toBe(LegajoLicenciaEnvio::ESTADO_PENDIENTE);

    // El worker ejecuta el job y envía el PDF firmado.
    (new EnviarLicenciaALegajos($envio->id))->handle(app(LegajoLicenciaEnvioService::class));

    $envio->refresh();

    expect($envio->estado)->toBe(LegajoLicenciaEnvio::ESTADO_ENVIADO)
        ->and($envio->http_status)->toBe(201)
        ->and($envio->legajo_id)->toBe(500)
        ->and($envio->intentos)->toBe(1);
});

it('no encola el envío cuando el tipo de solicitud no tiene equivalente en Legajos', function () {
    Notification::fake();
    Bus::fake();
    Storage::fake('local');

    $tipo = TipoSolicitud::factory()->create([
        'categoria' => Solicitud::CATEGORIA_SALUD_MATERNIDAD,
        'unidad_medida' => 'horas',
        'legajos_tipo_licencia' => null,
    ]);
    $agenteSolicitante = Agente::factory()->create(['dni' => '20987655']);
    $solicitud = Solicitud::factory()->create([
        'agente_id' => $agenteSolicitante->id,
        'tipo_solicitud_id' => $tipo->id,
        'estado' => Solicitud::ESTADO_PENDIENTE,
        'fecha_fin' => null,
        'dias_solicitados' => null,
    ]);

    $userSecretaria = User::factory()->create();
    $userSecretaria->assignRole('secretaria_general');
    Agente::factory()->create(['user_id' => $userSecretaria->id]);

    $rutaPdf = 'documentos/firmados/licencia-sin-mapeo.pdf';
    Storage::disk('local')->put($rutaPdf, "%PDF-1.4\nPDF firmado\n%%EOF");

    app(WorkflowSolicitudesService::class)->procesarAprobacion(
        $solicitud,
        $userSecretaria,
        'aprobado',
        null,
        'RESOL-2026-102',
        $rutaPdf,
        true,
    );

    $envio = LegajoLicenciaEnvio::query()->sole();

    expect($envio->estado)->toBe(LegajoLicenciaEnvio::ESTADO_NO_APLICA)
        ->and($envio->proximo_intento_at)->toBeNull();

    Bus::assertNotDispatched(EnviarLicenciaALegajos::class);
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
