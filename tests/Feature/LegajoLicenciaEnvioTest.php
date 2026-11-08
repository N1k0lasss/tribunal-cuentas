<?php

use App\Exceptions\LegajoApiException;
use App\Exceptions\LegajoEnvioReintentableException;
use App\Filament\Admin\Pages\LegajoLicenciasEnvios;
use App\Filament\Admin\Resources\TipoSolicituds\TipoSolicitudResource;
use App\Jobs\EnviarLicenciaALegajos;
use App\Models\Agente;
use App\Models\Documento;
use App\Models\LegajoLicenciaEnvio;
use App\Models\Solicitud;
use App\Models\TipoSolicitud;
use App\Models\User;
use App\Notifications\EnvioLicenciaFallidoNotification;
use App\Services\LegajoApiService;
use App\Services\LegajoLicenciaEnvioService;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('envía una licencia aprobada con su pdf firmado en un único multipart y marca el envío', function () {
    Storage::fake('local');
    config([
        'services.legajos.url' => 'https://legajos.test/api',
        'services.legajos.token' => 'token-de-prueba',
    ]);

    $agente = Agente::factory()->create(['dni' => '12345678']);
    $tipo = TipoSolicitud::factory()->create(['nombre' => 'Licencia ordinaria']);
    $solicitud = Solicitud::factory()->create([
        'agente_id' => $agente->id,
        'tipo_solicitud_id' => $tipo->id,
        'estado' => Solicitud::ESTADO_APROBADO,
        'fecha_aprobacion' => '2026-09-20',
    ]);
    $rutaPdf = 'documentos/firmados/solicitud.pdf';
    Storage::disk('local')->put($rutaPdf, "%PDF-1.4\nPDF de prueba\n%%EOF");

    $documento = Documento::factory()->create([
        'solicitud_id' => $solicitud->id,
        'agente_id' => $agente->id,
        'ruta' => $rutaPdf,
    ]);

    $envio = app(LegajoLicenciaEnvioService::class)->programar(
        $solicitud->load(['agente', 'tipoSolicitud']),
        $documento,
    );

    Http::fake([
        'legajos.test/api/legajos/500/licencias' => Http::response(['ok' => true], 201),
        'legajos.test/api/legajos/por-dni/12345678' => Http::response([
            'persona' => ['dni' => '12345678'],
            'legajo' => ['id' => 500],
        ]),
    ]);

    app(LegajoLicenciaEnvioService::class)->procesarEnvio($envio->id);

    expect($envio->fresh()->estado)->toBe(LegajoLicenciaEnvio::ESTADO_ENVIADO)
        ->and($envio->fresh()->legajo_id)->toBe(500)
        ->and($envio->fresh()->http_status)->toBe(201);

    Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
        && $request->url() === 'https://legajos.test/api/legajos/500/licencias'
        && $request->isMultipart()
        && $request->hasHeader('Idempotency-Key', 'licencia-'.$solicitud->id)
        && $request->hasFile('archivo', "%PDF-1.4\nPDF de prueba\n%%EOF", 'solicitud_'.$solicitud->id.'.pdf')
        && str_contains($request->body(), 'name="solicitud_id"')
        && str_contains($request->body(), (string) $solicitud->id)
        && str_contains($request->body(), 'Licencia ordinaria')
        && str_contains($request->body(), '2026-09-20'));
});

it('no crea un segundo envío para una solicitud ya programada', function () {
    $agente = Agente::factory()->create();
    $solicitud = Solicitud::factory()->create(['agente_id' => $agente->id]);
    $documento = Documento::factory()->create([
        'solicitud_id' => $solicitud->id,
        'agente_id' => $agente->id,
    ]);
    $solicitud->load(['agente', 'tipoSolicitud']);

    $servicio = app(LegajoLicenciaEnvioService::class);
    $primero = $servicio->programar($solicitud, $documento);
    $segundo = $servicio->programar($solicitud, $documento);

    expect($primero->id)->toBe($segundo->id)
        ->and(LegajoLicenciaEnvio::query()->where('solicitud_id', $solicitud->id)->count())->toBe(1);
});

it('reintenta los errores transitorios con espera y no reenvía un conflicto 409 automáticamente', function () {
    Storage::fake('local');
    config([
        'services.legajos.url' => 'https://legajos.test',
        'services.legajos.token' => 'token-de-prueba',
    ]);

    $agente = Agente::factory()->create(['dni' => '12345678']);
    $solicitud = Solicitud::factory()->create(['agente_id' => $agente->id]);
    $rutaPdf = 'documentos/firmados/solicitud-reintento.pdf';
    Storage::disk('local')->put($rutaPdf, "%PDF-1.4\nPDF de prueba\n%%EOF");
    $documento = Documento::factory()->create([
        'solicitud_id' => $solicitud->id,
        'agente_id' => $agente->id,
        'ruta' => $rutaPdf,
    ]);
    $envio = app(LegajoLicenciaEnvioService::class)->programar(
        $solicitud->load(['agente', 'tipoSolicitud']),
        $documento,
    );

    $posts = 0;
    Http::fake(function (Request $request) use (&$posts) {
        if (str_contains($request->url(), '/por-dni/')) {
            return Http::response([
                'persona' => ['dni' => '12345678'],
                'legajo' => ['id' => 500],
            ]);
        }

        $posts++;

        return Http::response(
            ['message' => $posts === 1 ? 'No disponible' : 'Ya registrado'],
            $posts === 1 ? 503 : 409,
        );
    });

    $servicio = app(LegajoLicenciaEnvioService::class);

    expect(fn () => $servicio->procesarEnvio($envio->id))
        ->toThrow(LegajoEnvioReintentableException::class);

    $envio->refresh();
    $segundosHastaReintento = $envio->proximo_intento_at->timestamp - now()->timestamp;
    expect($envio->estado)->toBe(LegajoLicenciaEnvio::ESTADO_PENDIENTE)
        ->and($segundosHastaReintento)->toBeBetween(299, 300)
        ->and($posts)->toBe(1);

    $this->travel(5)->minutes();
    $servicio->procesarEnvio($envio->id);

    expect($envio->fresh()->estado)->toBe(LegajoLicenciaEnvio::ESTADO_CONFLICTO)
        ->and($envio->fresh()->http_status)->toBe(409)
        ->and($envio->fresh()->proximo_intento_at)->toBeNull()
        ->and($posts)->toBe(2);
});

it('ejecuta el envío cuando corre el job en cola', function () {
    Storage::fake('local');
    config([
        'services.legajos.url' => 'https://legajos.test/api',
        'services.legajos.token' => 'token-de-prueba',
    ]);

    $agente = Agente::factory()->create(['dni' => '12345678']);
    $solicitud = Solicitud::factory()->create(['agente_id' => $agente->id]);
    $rutaPdf = 'documentos/firmados/solicitud-job.pdf';
    Storage::disk('local')->put($rutaPdf, "%PDF-1.4\nPDF de prueba\n%%EOF");
    $documento = Documento::factory()->create([
        'solicitud_id' => $solicitud->id,
        'agente_id' => $agente->id,
        'ruta' => $rutaPdf,
    ]);
    $envio = app(LegajoLicenciaEnvioService::class)->programar(
        $solicitud->load(['agente', 'tipoSolicitud']),
        $documento,
    );

    Http::fake([
        'legajos.test/api/legajos/500/licencias' => Http::response(['ok' => true], 201),
        'legajos.test/api/legajos/por-dni/12345678' => Http::response([
            'persona' => ['dni' => '12345678'],
            'legajo' => ['id' => 500],
        ]),
    ]);

    EnviarLicenciaALegajos::dispatchSync($envio->id);

    expect($envio->fresh()->estado)->toBe(LegajoLicenciaEnvio::ESTADO_ENVIADO)
        ->and($envio->fresh()->http_status)->toBe(201);
});

it('marca error definitivo y avisa a los administradores cuando el job agota los reintentos', function () {
    Notification::fake();

    Role::findOrCreate('administrador', 'web');
    $administrador = User::factory()->create(['email' => 'admin@example.test']);
    $administrador->assignRole('administrador');

    $agente = Agente::factory()->create();
    $solicitud = Solicitud::factory()->create(['agente_id' => $agente->id]);
    $documento = Documento::factory()->create([
        'solicitud_id' => $solicitud->id,
        'agente_id' => $agente->id,
    ]);
    $envio = app(LegajoLicenciaEnvioService::class)->programar(
        $solicitud->load(['agente', 'tipoSolicitud']),
        $documento,
    );
    $envio->update(['estado' => LegajoLicenciaEnvio::ESTADO_PENDIENTE, 'intentos' => 5]);

    (new EnviarLicenciaALegajos($envio->id))->failed(new RuntimeException('Legajos no responde.'));

    expect($envio->fresh()->estado)->toBe(LegajoLicenciaEnvio::ESTADO_ERROR)
        ->and($envio->fresh()->proximo_intento_at)->toBeNull()
        ->and($envio->fresh()->ultimo_error)->toBe('Legajos no responde.');

    Notification::assertSentOnDemand(
        EnvioLicenciaFallidoNotification::class,
        fn ($notification, $channels, $notifiable): bool => in_array('mail', $channels, true)
            && $notifiable->routes['mail'] === 'admin@example.test'
            && $notification->envio->id === $envio->id,
    );
});

it('encola solo los envíos pendientes cuya hora de reintento ya venció', function () {
    Bus::fake();

    $servicio = app(LegajoLicenciaEnvioService::class);

    $crearEnvio = function (array $atributos) use ($servicio): LegajoLicenciaEnvio {
        $agente = Agente::factory()->create();
        $solicitud = Solicitud::factory()->create(['agente_id' => $agente->id]);
        $documento = Documento::factory()->create([
            'solicitud_id' => $solicitud->id,
            'agente_id' => $agente->id,
        ]);

        $envio = $servicio->programar($solicitud->load(['agente', 'tipoSolicitud']), $documento);
        $envio->update($atributos);

        return $envio->fresh();
    };

    $vencido = $crearEnvio([
        'estado' => LegajoLicenciaEnvio::ESTADO_PENDIENTE,
        'proximo_intento_at' => now()->subMinute(),
    ]);
    $sinFecha = $crearEnvio([
        'estado' => LegajoLicenciaEnvio::ESTADO_PENDIENTE,
        'proximo_intento_at' => null,
    ]);
    $futuro = $crearEnvio([
        'estado' => LegajoLicenciaEnvio::ESTADO_PENDIENTE,
        'proximo_intento_at' => now()->addHour(),
    ]);

    $encolados = $servicio->encolarPendientes();

    expect($encolados)->toBe(2);

    Bus::assertDispatched(
        EnviarLicenciaALegajos::class,
        fn (EnviarLicenciaALegajos $job): bool => $job->envioId === $vencido->id,
    );
    Bus::assertDispatched(
        EnviarLicenciaALegajos::class,
        fn (EnviarLicenciaALegajos $job): bool => $job->envioId === $sinFecha->id,
    );
    Bus::assertNotDispatched(
        EnviarLicenciaALegajos::class,
        fn (EnviarLicenciaALegajos $job): bool => $job->envioId === $futuro->id,
    );
});

it('reencola desde el panel un envío en error y reinicia el ciclo de reintentos', function () {
    Bus::fake();

    Role::findOrCreate('administrador', 'web');
    $administrador = User::factory()->create();
    $administrador->assignRole('administrador');

    $agente = Agente::factory()->create();
    $solicitud = Solicitud::factory()->create(['agente_id' => $agente->id]);
    $documento = Documento::factory()->create([
        'solicitud_id' => $solicitud->id,
        'agente_id' => $agente->id,
    ]);
    $envio = app(LegajoLicenciaEnvioService::class)->programar(
        $solicitud->load(['agente', 'tipoSolicitud']),
        $documento,
    );
    $envio->update([
        'estado' => LegajoLicenciaEnvio::ESTADO_ERROR,
        'intentos' => 5,
        'http_status' => 500,
        'iniciado_at' => now()->subMinutes(10),
        'ultimo_error' => 'Legajos no responde.',
    ]);

    $this->actingAs($administrador);

    Livewire::test(LegajoLicenciasEnvios::class)
        ->callAction(TestAction::make('reintentar')->table($envio));

    $envio->refresh();

    expect($envio->estado)->toBe(LegajoLicenciaEnvio::ESTADO_PENDIENTE)
        ->and($envio->intentos)->toBe(0)
        ->and($envio->proximo_intento_at)->not->toBeNull()
        ->and($envio->iniciado_at)->toBeNull()
        ->and($envio->http_status)->toBeNull()
        ->and($envio->ultimo_error)->toBeNull();

    Bus::assertDispatched(
        EnviarLicenciaALegajos::class,
        fn (EnviarLicenciaALegajos $job): bool => $job->envioId === $envio->id,
    );
});

it('permite reintentar desde el panel un envío en conflicto 409', function () {
    Bus::fake();

    Role::findOrCreate('administrador', 'web');
    $administrador = User::factory()->create();
    $administrador->assignRole('administrador');

    $agente = Agente::factory()->create();
    $solicitud = Solicitud::factory()->create(['agente_id' => $agente->id]);
    $documento = Documento::factory()->create([
        'solicitud_id' => $solicitud->id,
        'agente_id' => $agente->id,
    ]);
    $envio = app(LegajoLicenciaEnvioService::class)->programar(
        $solicitud->load(['agente', 'tipoSolicitud']),
        $documento,
    );
    $envio->update([
        'estado' => LegajoLicenciaEnvio::ESTADO_CONFLICTO,
        'intentos' => 1,
        'http_status' => 409,
        'ultimo_error' => 'Ya existe una licencia registrada para esta solicitud.',
    ]);

    $this->actingAs($administrador);

    Livewire::test(LegajoLicenciasEnvios::class)
        ->assertActionVisible(TestAction::make('reintentar')->table($envio))
        ->callAction(TestAction::make('reintentar')->table($envio));

    expect($envio->fresh()->estado)->toBe(LegajoLicenciaEnvio::ESTADO_PENDIENTE);

    Bus::assertDispatched(
        EnviarLicenciaALegajos::class,
        fn (EnviarLicenciaALegajos $job): bool => $job->envioId === $envio->id,
    );
});

it('permite editar el equivalente en Legajos desde el régimen legal', function () {
    Role::findOrCreate('administrador', 'web');
    $administrador = User::factory()->create();
    $administrador->assignRole('administrador');

    $tipo = TipoSolicitud::factory()->create([
        'legajos_tipo_licencia' => 'Receso de Enero',
    ]);

    $this->actingAs($administrador)
        ->get(TipoSolicitudResource::getUrl('edit', ['record' => $tipo]))
        ->assertSuccessful()
        ->assertSee('Tipo equivalente en Legajos');
});

it('registra no_aplica y no llama a Legajos cuando el tipo no tiene equivalente', function () {
    Storage::fake('local');
    config([
        'services.legajos.url' => 'https://legajos.test/api',
        'services.legajos.token' => 'token-de-prueba',
    ]);

    $agente = Agente::factory()->create(['dni' => '12345678']);
    $tipo = TipoSolicitud::factory()->create([
        'nombre' => 'Franquicia por Estudio',
        'unidad_medida' => 'horas',
        'legajos_tipo_licencia' => null,
    ]);
    $solicitud = Solicitud::factory()->create([
        'agente_id' => $agente->id,
        'tipo_solicitud_id' => $tipo->id,
        'fecha_fin' => null,
        'dias_solicitados' => null,
    ]);
    $documento = Documento::factory()->create([
        'solicitud_id' => $solicitud->id,
        'agente_id' => $agente->id,
    ]);

    Http::fake();

    $servicio = app(LegajoLicenciaEnvioService::class);
    $envio = $servicio->programar($solicitud->load(['agente', 'tipoSolicitud']), $documento);

    expect($envio->estado)->toBe(LegajoLicenciaEnvio::ESTADO_NO_APLICA)
        ->and($envio->proximo_intento_at)->toBeNull()
        ->and($envio->datos['tipo_licencia'])->toBe('Franquicia por Estudio');

    $servicio->procesarEnvio($envio->id);

    expect($envio->fresh()->estado)->toBe(LegajoLicenciaEnvio::ESTADO_NO_APLICA);

    Http::assertNothingSent();
});

it('permite reencolar un no_aplica cuando se configura el equivalente en Legajos', function () {
    Role::findOrCreate('administrador', 'web');
    $administrador = User::factory()->create();
    $administrador->assignRole('administrador');

    $agente = Agente::factory()->create();
    $tipo = TipoSolicitud::factory()->create([
        'nombre' => 'Franquicia por Estudio',
        'legajos_tipo_licencia' => null,
    ]);
    $solicitud = Solicitud::factory()->create([
        'agente_id' => $agente->id,
        'tipo_solicitud_id' => $tipo->id,
    ]);
    $documento = Documento::factory()->create([
        'solicitud_id' => $solicitud->id,
        'agente_id' => $agente->id,
    ]);
    $envio = app(LegajoLicenciaEnvioService::class)->programar(
        $solicitud->load(['agente', 'tipoSolicitud']),
        $documento,
    );

    expect($envio->estado)->toBe(LegajoLicenciaEnvio::ESTADO_NO_APLICA);

    // Legajos acordó el equivalente: se carga en el régimen legal.
    $tipo->update(['legajos_tipo_licencia' => 'Franquicia por Estudio (Legajos)']);

    Bus::fake();
    $this->actingAs($administrador);

    Livewire::test(LegajoLicenciasEnvios::class)
        ->callAction(TestAction::make('reintentar')->table($envio));

    $envio->refresh();

    expect($envio->estado)->toBe(LegajoLicenciaEnvio::ESTADO_PENDIENTE)
        ->and($envio->datos['tipo_licencia'])->toBe('Franquicia por Estudio (Legajos)')
        ->and($envio->intentos)->toBe(0)
        ->and($envio->proximo_intento_at)->not->toBeNull();

    Bus::assertDispatched(
        EnviarLicenciaALegajos::class,
        fn (EnviarLicenciaALegajos $job): bool => $job->envioId === $envio->id,
    );
});

it('no reencola un no_aplica si el tipo sigue sin equivalente en Legajos', function () {
    Role::findOrCreate('administrador', 'web');
    $administrador = User::factory()->create();
    $administrador->assignRole('administrador');

    $agente = Agente::factory()->create();
    $tipo = TipoSolicitud::factory()->create([
        'nombre' => 'Franquicia por Estudio',
        'legajos_tipo_licencia' => null,
    ]);
    $solicitud = Solicitud::factory()->create([
        'agente_id' => $agente->id,
        'tipo_solicitud_id' => $tipo->id,
    ]);
    $documento = Documento::factory()->create([
        'solicitud_id' => $solicitud->id,
        'agente_id' => $agente->id,
    ]);
    $envio = app(LegajoLicenciaEnvioService::class)->programar(
        $solicitud->load(['agente', 'tipoSolicitud']),
        $documento,
    );

    Bus::fake();
    $this->actingAs($administrador);

    Livewire::test(LegajoLicenciasEnvios::class)
        ->callAction(TestAction::make('reintentar')->table($envio));

    expect($envio->fresh()->estado)->toBe(LegajoLicenciaEnvio::ESTADO_NO_APLICA);

    Bus::assertNotDispatched(EnviarLicenciaALegajos::class);
});

it('marca error si ocurre una excepción inesperada durante el envío', function () {
    Storage::fake('local');
    config([
        'services.legajos.url' => 'https://legajos.test/api',
        'services.legajos.token' => 'token-de-prueba',
    ]);

    $agente = Agente::factory()->create();
    $solicitud = Solicitud::factory()->create(['agente_id' => $agente->id]);
    $rutaPdf = 'documentos/firmados/solicitud-error-inesperado.pdf';
    Storage::disk('local')->put($rutaPdf, "%PDF-1.4\nPDF de prueba\n%%EOF");
    $documento = Documento::factory()->create([
        'solicitud_id' => $solicitud->id,
        'agente_id' => $agente->id,
        'ruta' => $rutaPdf,
    ]);
    $envio = app(LegajoLicenciaEnvioService::class)->programar(
        $solicitud->load(['agente', 'tipoSolicitud']),
        $documento,
    );

    $this->mock(LegajoApiService::class, function ($mock): void {
        $mock->shouldReceive('obtenerIdLegajo')
            ->once()
            ->andThrow(new RuntimeException('Fallo inesperado del entorno.'));
    });

    app(LegajoLicenciaEnvioService::class)->procesarEnvio($envio->id);

    $envio->refresh();

    expect($envio->estado)->toBe(LegajoLicenciaEnvio::ESTADO_ERROR)
        ->and($envio->http_status)->toBeNull()
        ->and($envio->proximo_intento_at)->toBeNull()
        ->and($envio->ultimo_error)->toContain('Error inesperado');
});

it('conserva el código 401 al relanzar un error reciente memorizado', function () {
    config([
        'services.legajos.url' => 'https://legajos.test/api',
        'services.legajos.token' => 'token-de-prueba',
    ]);

    Http::fake(['*' => Http::response([], 401)]);

    $servicio = app(LegajoApiService::class);
    $codigos = [];

    foreach ([1, 2] as $intento) {
        try {
            $servicio->obtenerPorDni('12345678');
        } catch (LegajoApiException $exception) {
            $codigos[] = $exception->getCode();
        }
    }

    expect($codigos)->toBe([401, 401]);

    // El segundo intento se responde desde la memoria de errores, sin HTTP.
    Http::assertSentCount(1);
});

it('permite cerrar manualmente un conflicto cuando Legajos ya tiene la licencia', function () {
    Role::findOrCreate('administrador', 'web');
    $administrador = User::factory()->create();
    $administrador->assignRole('administrador');

    $agente = Agente::factory()->create();
    $solicitud = Solicitud::factory()->create(['agente_id' => $agente->id]);
    $documento = Documento::factory()->create([
        'solicitud_id' => $solicitud->id,
        'agente_id' => $agente->id,
    ]);
    $envio = app(LegajoLicenciaEnvioService::class)->programar(
        $solicitud->load(['agente', 'tipoSolicitud']),
        $documento,
    );
    $envio->update([
        'estado' => LegajoLicenciaEnvio::ESTADO_CONFLICTO,
        'http_status' => 409,
        'ultimo_error' => 'Ya existe una licencia registrada para esta solicitud.',
    ]);

    $this->actingAs($administrador);

    Livewire::test(LegajoLicenciasEnvios::class)
        ->assertActionVisible(TestAction::make('marcar_enviado')->table($envio))
        ->callAction(TestAction::make('marcar_enviado')->table($envio));

    $envio->refresh();

    expect($envio->estado)->toBe(LegajoLicenciaEnvio::ESTADO_ENVIADO)
        ->and($envio->enviado_at)->not->toBeNull()
        ->and($envio->http_status)->toBeNull()
        ->and($envio->ultimo_error)->toBeNull();
});

it('permite al administrador consultar el estado de los envíos', function () {
    Role::findOrCreate('administrador', 'web');
    $administrador = User::factory()->create();
    $administrador->assignRole('administrador');

    $this->actingAs($administrador)
        ->get('/sistema/envios-licencias-legajos')
        ->assertOk();
});

it('marca error sin llamar a Legajos cuando falta la fecha de fin', function () {
    Storage::fake('local');
    config([
        'services.legajos.url' => 'https://legajos.test/api',
        'services.legajos.token' => 'token-de-prueba',
    ]);

    $agente = Agente::factory()->create(['dni' => '12345678']);
    $tipo = TipoSolicitud::factory()->create(['unidad_medida' => 'dias']);
    $solicitud = Solicitud::factory()->create([
        'agente_id' => $agente->id,
        'tipo_solicitud_id' => $tipo->id,
        'fecha_fin' => null,
        'dias_solicitados' => null,
    ]);
    $rutaPdf = 'documentos/firmados/solicitud-sin-fecha-fin.pdf';
    Storage::disk('local')->put($rutaPdf, "%PDF-1.4\nPDF de prueba\n%%EOF");
    $documento = Documento::factory()->create([
        'solicitud_id' => $solicitud->id,
        'agente_id' => $agente->id,
        'ruta' => $rutaPdf,
    ]);

    Http::fake();

    $envio = app(LegajoLicenciaEnvioService::class)->programar(
        $solicitud->load(['agente', 'tipoSolicitud']),
        $documento,
    );

    app(LegajoLicenciaEnvioService::class)->procesarEnvio($envio->id);

    $envio->refresh();

    expect($envio->estado)->toBe(LegajoLicenciaEnvio::ESTADO_ERROR)
        ->and($envio->http_status)->toBe(422)
        ->and($envio->proximo_intento_at)->toBeNull()
        ->and($envio->ultimo_error)->toContain('Falta fecha_fin');

    Http::assertNothingSent();
});

it('marca error sin llamar a Legajos cuando dias_solicitados es cero', function () {
    Storage::fake('local');
    config([
        'services.legajos.url' => 'https://legajos.test/api',
        'services.legajos.token' => 'token-de-prueba',
    ]);

    $agente = Agente::factory()->create(['dni' => '12345678']);
    $tipo = TipoSolicitud::factory()->create(['unidad_medida' => 'dias']);
    $solicitud = Solicitud::factory()->create([
        'agente_id' => $agente->id,
        'tipo_solicitud_id' => $tipo->id,
        'dias_solicitados' => 0,
    ]);
    $rutaPdf = 'documentos/firmados/solicitud-sin-dias.pdf';
    Storage::disk('local')->put($rutaPdf, "%PDF-1.4\nPDF de prueba\n%%EOF");
    $documento = Documento::factory()->create([
        'solicitud_id' => $solicitud->id,
        'agente_id' => $agente->id,
        'ruta' => $rutaPdf,
    ]);

    Http::fake();

    $envio = app(LegajoLicenciaEnvioService::class)->programar(
        $solicitud->load(['agente', 'tipoSolicitud']),
        $documento,
    );

    app(LegajoLicenciaEnvioService::class)->procesarEnvio($envio->id);

    $envio->refresh();

    expect($envio->estado)->toBe(LegajoLicenciaEnvio::ESTADO_ERROR)
        ->and($envio->http_status)->toBe(422)
        ->and($envio->ultimo_error)->toContain('dias_solicitados debe ser al menos 1');

    Http::assertNothingSent();
});

it('marca error cuando la fecha de fin es anterior a la de inicio', function () {
    Storage::fake('local');
    config([
        'services.legajos.url' => 'https://legajos.test/api',
        'services.legajos.token' => 'token-de-prueba',
    ]);

    $agente = Agente::factory()->create(['dni' => '12345678']);
    $tipo = TipoSolicitud::factory()->create(['unidad_medida' => 'dias']);
    $solicitud = Solicitud::factory()->create([
        'agente_id' => $agente->id,
        'tipo_solicitud_id' => $tipo->id,
    ]);
    $rutaPdf = 'documentos/firmados/solicitud-fechas-invertidas.pdf';
    Storage::disk('local')->put($rutaPdf, "%PDF-1.4\nPDF de prueba\n%%EOF");
    $documento = Documento::factory()->create([
        'solicitud_id' => $solicitud->id,
        'agente_id' => $agente->id,
        'ruta' => $rutaPdf,
    ]);

    Http::fake();

    $envio = app(LegajoLicenciaEnvioService::class)->programar(
        $solicitud->load(['agente', 'tipoSolicitud']),
        $documento,
    );
    $envio->update([
        'datos' => array_merge($envio->datos, [
            'fecha_inicio' => '2026-11-10',
            'fecha_fin' => '2026-11-01',
        ]),
    ]);

    app(LegajoLicenciaEnvioService::class)->procesarEnvio($envio->id);

    $envio->refresh();

    expect($envio->estado)->toBe(LegajoLicenciaEnvio::ESTADO_ERROR)
        ->and($envio->http_status)->toBe(422)
        ->and($envio->ultimo_error)->toContain('anterior a fecha_inicio');

    Http::assertNothingSent();
});

it('recalcula el payload al reintentar un error con datos corregidos', function () {
    Bus::fake();

    Role::findOrCreate('administrador', 'web');
    $administrador = User::factory()->create();
    $administrador->assignRole('administrador');

    $agente = Agente::factory()->create();
    $tipo = TipoSolicitud::factory()->create(['unidad_medida' => 'dias']);
    $solicitud = Solicitud::factory()->create([
        'agente_id' => $agente->id,
        'tipo_solicitud_id' => $tipo->id,
        'estado' => Solicitud::ESTADO_PENDIENTE,
        'fecha_inicio' => '2026-11-10',
        'fecha_fin' => null,
        'dias_solicitados' => null,
    ]);
    $documento = Documento::factory()->create([
        'solicitud_id' => $solicitud->id,
        'agente_id' => $agente->id,
    ]);
    $envio = app(LegajoLicenciaEnvioService::class)->programar(
        $solicitud->load(['agente', 'tipoSolicitud']),
        $documento,
    );
    $envio->update([
        'estado' => LegajoLicenciaEnvio::ESTADO_ERROR,
        'http_status' => 422,
        'ultimo_error' => 'Validación local: Falta fecha_fin. No se envió a Legajos.',
    ]);

    // Alguien corrigió la solicitud después del error.
    $solicitud->update([
        'fecha_fin' => '2026-11-14',
        'dias_solicitados' => 5,
    ]);

    $this->actingAs($administrador);

    Livewire::test(LegajoLicenciasEnvios::class)
        ->callAction(TestAction::make('reintentar')->table($envio));

    $envio->refresh();

    expect($envio->estado)->toBe(LegajoLicenciaEnvio::ESTADO_PENDIENTE)
        ->and($envio->datos['fecha_fin'])->toBe('2026-11-14')
        ->and($envio->datos['dias_solicitados'])->toBe(5)
        ->and($envio->intentos)->toBe(0)
        ->and($envio->ultimo_error)->toBeNull();

    Bus::assertDispatched(
        EnviarLicenciaALegajos::class,
        fn (EnviarLicenciaALegajos $job): bool => $job->envioId === $envio->id,
    );
});
