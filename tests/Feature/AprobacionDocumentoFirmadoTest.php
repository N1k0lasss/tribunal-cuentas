<?php

use App\Filament\Admin\Resources\Solicituds\Pages\ListSolicituds;
use App\Filament\Admin\Resources\Solicituds\SolicitudResource;
use App\Models\Agente;
use App\Models\Documento;
use App\Models\Solicitud;
use App\Models\TipoSolicitud;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\Testing\TestAction;
use Filament\Schemas\Schema;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
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
 *
 * @return array{0: Solicitud, 1: User, 2: User}
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

/**
 * Genera un PDF mínimo y válido para usar como "documento firmado" en los tests.
 */
function crearPdfDePrueba(string $texto = 'Documento firmado por la etapa anterior'): string
{
    $pdf = new \setasign\Fpdi\Fpdi();
    $pdf->AddPage();
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->Cell(0, 10, $texto);

    return $pdf->Output('S');
}

/**
 * Extrae el texto de un PDF descomprimiendo sus streams (FPDF comprime con zlib).
 * Usa /Length para delimitar cada stream, porque el binario comprimido puede
 * contener la palabra "endstream".
 */
function extraerTextoPdf(string $bytes): string
{
    $texto = '';

    preg_match_all('/\/Length\s+(\d+)/', $bytes, $coincidencias, PREG_OFFSET_CAPTURE);

    foreach ($coincidencias[1] as $indice => $coincidencia) {
        $largo = (int) $coincidencia[0];
        $desde = $coincidencias[0][$indice][1] + strlen($coincidencias[0][$indice][0]);

        $posStream = strpos($bytes, 'stream', $desde);

        if ($posStream === false) {
            continue;
        }

        $inicio = $posStream + 6;

        if (($bytes[$inicio] ?? '') === "\r") {
            $inicio++;
        }

        if (($bytes[$inicio] ?? '') === "\n") {
            $inicio++;
        }

        $stream = substr($bytes, $inicio, $largo);
        $inflado = @gzuncompress($stream);

        $texto .= $inflado !== false ? $inflado : $stream;
    }

    return $texto;
}

/**
 * Aprueba la etapa de Jefatura (con un PDF firmado real) para dejar la solicitud
 * en Secretaría General, y devuelve un usuario de Secretaría.
 */
function avanzarSolicitudASecretaria(Solicitud $solicitud, User $userJefatura): User
{
    $rutaFirmaJefatura = 'documentos/firmados/jefatura.pdf';
    Storage::disk('local')->put($rutaFirmaJefatura, crearPdfDePrueba('Firma de Jefatura'));

    test()->actingAs($userJefatura);

    SolicitudResource::procesarAprobacion($solicitud, 'aprobado', null, null, $rutaFirmaJefatura);

    $userSecretaria = User::factory()->create();
    $userSecretaria->assignRole('secretaria_general');
    Agente::factory()->create(['user_id' => $userSecretaria->id]);

    return $userSecretaria;
}

it('no permite aprobar sin el documento firmado', function () {
    [$solicitud, $userJefatura] = crearSolicitudConJefatura();

    $this->actingAs($userJefatura);

    $exception = null;

    try {
        SolicitudResource::procesarAprobacion($solicitud, 'aprobado', null, null, null);
    } catch (ValidationException $e) {
        $exception = $e;
    }

    expect($exception)->not->toBeNull()
        ->and($exception->errors())->toHaveKey('documento_firmado');

    $solicitud->refresh();

    expect($solicitud->estado)->toBe(Solicitud::ESTADO_PENDIENTE)
        ->and($solicitud->aprobaciones()->count())->toBe(0)
        ->and($solicitud->documentos()->count())->toBe(0);
});

it('no permite aprobar si el archivo firmado no existe en el servidor', function () {
    Storage::fake('local');

    [$solicitud, $userJefatura] = crearSolicitudConJefatura();

    $this->actingAs($userJefatura);

    expect(fn () => SolicitudResource::procesarAprobacion(
        $solicitud,
        'aprobado',
        null,
        null,
        'documentos/firmados/inexistente.pdf',
    ))->toThrow(ValidationException::class);

    $solicitud->refresh();

    expect($solicitud->estado)->toBe(Solicitud::ESTADO_PENDIENTE)
        ->and($solicitud->aprobaciones()->count())->toBe(0);
});

it('aprueba la solicitud y guarda el documento firmado vinculado a la aprobación', function () {
    Storage::fake('local');
    Storage::disk('local')->put('documentos/firmados/firma.pdf', '%PDF-1.4 fake');

    [$solicitud, $userJefatura] = crearSolicitudConJefatura();

    $this->actingAs($userJefatura);

    SolicitudResource::procesarAprobacion(
        $solicitud,
        'aprobado',
        'Todo en orden',
        null,
        'documentos/firmados/firma.pdf',
    );

    $solicitud->refresh();

    expect($solicitud->estado)->toBe(Solicitud::ESTADO_EN_REVISION)
        ->and($solicitud->aprobaciones()->count())->toBe(1);

    $aprobacion = $solicitud->aprobaciones()->first();

    expect($aprobacion)->not->toBeNull()
        ->and($aprobacion->etapa)->toBe(Solicitud::ETAPA_JEFATURA)
        ->and($aprobacion->estado)->toBe('aprobado');

    $documento = $solicitud->documentos()->first();

    expect($documento)->not->toBeNull()
        ->and($documento->tipo_documento)->toBe(Documento::TIPO_SOLICITUD_FIRMADA)
        ->and($documento->aprobacion_id)->toBe($aprobacion->id)
        ->and($documento->ruta)->toBe('documentos/firmados/firma.pdf');

    expect($solicitud->documentoFirmadoVigente()?->id)->toBe($documento->id);
});

it('permite rechazar sin exigir el documento firmado', function () {
    [$solicitud, $userJefatura] = crearSolicitudConJefatura();

    $this->actingAs($userJefatura);

    SolicitudResource::procesarAprobacion($solicitud, 'rechazado', 'Falta documentación');

    $solicitud->refresh();

    expect($solicitud->estado)->toBe(Solicitud::ESTADO_RECHAZADO)
        ->and($solicitud->documentos()->count())->toBe(0);
});

it('mantiene deshabilitado el botón confirmar hasta que se adjunta el documento firmado', function () {
    $livewire = new class
    {
        /** @var array<int, array<string, mixed>> */
        public array $mountedActions = [];
    };

    $submit = Action::make('submit');

    $configurar = SolicitudResource::modalSubmitActionDocumentoFirmado();
    $configurar($submit, $livewire);

    // Sin documento cargado: el botón queda deshabilitado.
    expect($submit->isDisabled())->toBeTrue();

    // Con documento cargado en el formulario del modal: se habilita.
    $livewire->mountedActions = [
        [
            'name' => 'aprobar',
            'arguments' => [],
            'context' => [],
            'data' => ['documento_firmado' => 'documentos/firmados/firma.pdf'],
        ],
    ];

    expect($submit->isDisabled())->toBeFalse();
});

it('mantiene deshabilitado el botón confirmar en Secretaría hasta confirmar la descarga', function () {
    $livewire = new class
    {
        /** @var array<int, array<string, mixed>> */
        public array $mountedActions = [];
    };

    $submit = Action::make('submit');

    $configurar = SolicitudResource::modalSubmitActionDocumentoFirmado();
    $configurar($submit, $livewire);

    // Documento cargado, pero sin la confirmación de Secretaría: deshabilitado.
    $livewire->mountedActions = [
        [
            'name' => 'aprobar',
            'arguments' => [],
            'context' => [],
            'data' => [
                'documento_firmado' => 'documentos/firmados/firma.pdf',
                'confirmo_documento_integrado' => false,
            ],
        ],
    ];

    expect($submit->isDisabled())->toBeTrue();

    // Con la confirmación marcada: habilitado.
    $livewire->mountedActions = [
        [
            'name' => 'aprobar',
            'arguments' => [],
            'context' => [],
            'data' => [
                'documento_firmado' => 'documentos/firmados/firma.pdf',
                'confirmo_documento_integrado' => true,
            ],
        ],
    ];

    expect($submit->isDisabled())->toBeFalse();
});

it('deshabilita el botón confirmar del modal real hasta que se adjunta el documento firmado', function () {
    Storage::fake('local');
    Storage::disk('local')->put('documentos/firmados/firma.pdf', '%PDF-1.4 fake');

    [$solicitud, $userJefatura] = crearSolicitudConJefatura();

    $this->actingAs($userJefatura);

    Livewire::test(ListSolicituds::class)
        ->mountAction(TestAction::make('aprobar')->table($solicitud))
        ->assertActionDisabled('submit')
        ->setActionData(['documento_firmado' => 'documentos/firmados/firma.pdf'])
        ->assertActionEnabled('submit');
});

it('en el modal real de Secretaría el botón confirmar exige adjuntar y confirmar la descarga', function () {
    Storage::fake('local');
    Storage::disk('local')->put('documentos/firmados/firma.pdf', '%PDF-1.4 fake');

    [$solicitud, $userJefatura] = crearSolicitudConJefatura();
    $userSecretaria = avanzarSolicitudASecretaria($solicitud, $userJefatura);

    $solicitud->refresh();

    $this->actingAs($userSecretaria);

    // Adjuntar el documento todavía no alcanza: falta la confirmación.
    Livewire::test(ListSolicituds::class)
        ->mountAction(TestAction::make('aprobar')->table($solicitud))
        ->assertActionDisabled('submit')
        ->setActionData(['documento_firmado' => 'documentos/firmados/firma.pdf'])
        ->assertActionDisabled('submit')
        // Al marcar la confirmación (y con el número cargado) se habilita.
        ->setActionData([
            'confirmo_documento_integrado' => true,
            'numero_resolucion' => 'RESOL-MODAL-REAL',
        ])
        ->assertActionEnabled('submit');
});

it('aprueba desde el modal subiendo el documento firmado (flujo completo)', function () {
    Storage::fake('local');

    [$solicitud, $userJefatura] = crearSolicitudConJefatura();

    $this->actingAs($userJefatura);

    Livewire::test(ListSolicituds::class)
        ->mountAction(TestAction::make('aprobar')->table($solicitud))
        ->setActionData([
            'documento_firmado' => UploadedFile::fake()->create('firma.pdf', 20, 'application/pdf'),
        ])
        ->callMountedAction();

    $solicitud->refresh();

    expect($solicitud->estado)->toBe(Solicitud::ESTADO_EN_REVISION);

    $documento = $solicitud->documentos()->first();

    expect($documento)->not->toBeNull()
        ->and($documento->tipo_documento)->toBe(Documento::TIPO_SOLICITUD_FIRMADA)
        ->and($documento->aprobacion_id)->not->toBeNull();

    Storage::disk('local')->assertExists($documento->ruta);
});

it('permite descargar el PDF de la solicitud a los aprobadores y al agente dueño', function () {
    [$solicitud, $userJefatura, $userSolicitante] = crearSolicitudConJefatura();

    // Jefatura (aprobador) puede descargar.
    $this->actingAs($userJefatura);

    $respuesta = $this->get(route('solicitud.pdf', $solicitud));

    $respuesta->assertOk();

    expect($respuesta->streamedContent())->toContain('%PDF');

    // El agente dueño también puede descargar.
    $this->actingAs($userSolicitante)
        ->get(route('solicitud.pdf', $solicitud))
        ->assertOk();

    // Un usuario ajeno recibe 403.
    $intruso = User::factory()->create();

    $this->actingAs($intruso)
        ->get(route('solicitud.pdf', $solicitud))
        ->assertForbidden();
});

it('incluye el número de resolución guardado en la plantilla del PDF', function () {
    [$solicitud] = crearSolicitudConJefatura();

    $solicitud->resolucion()->create([
        'numero' => 'RESOL-2026-777',
        'fecha_emision' => now(),
    ]);

    $solicitud->load(['aprobaciones.aprobador', 'documentos', 'tipoSolicitud', 'agente.area', 'resolucion']);

    $html = view('pdf.solicitud', ['solicitud' => $solicitud, 'numeroResolucion' => null])->render();

    expect($html)->toContain('Resolución N°')
        ->and($html)->toContain('RESOL-2026-777');
});

it('incluye en el PDF el número de resolución enviado por query string sin persistir', function () {
    [$solicitud] = crearSolicitudConJefatura();

    $solicitud->load(['aprobaciones.aprobador', 'documentos', 'tipoSolicitud', 'agente.area', 'resolucion']);

    $html = view('pdf.solicitud', [
        'solicitud' => $solicitud,
        'numeroResolucion' => 'RESOL-BORRADOR-123',
    ])->render();

    expect($html)->toContain('RESOL-BORRADOR-123');
});

it('no muestra el número de resolución cuando la solicitud no tiene ninguno', function () {
    [$solicitud] = crearSolicitudConJefatura();

    $solicitud->load(['aprobaciones.aprobador', 'documentos', 'tipoSolicitud', 'agente.area', 'resolucion']);

    $html = view('pdf.solicitud', ['solicitud' => $solicitud, 'numeroResolucion' => null])->render();

    expect($html)->not->toContain('Resolución N°');
});

it('acepta el número de resolución por query string al descargar el PDF', function () {
    [$solicitud, $userJefatura] = crearSolicitudConJefatura();

    $this->actingAs($userJefatura)
        ->get(route('solicitud.pdf', ['solicitud' => $solicitud, 'numero' => 'RESOL-QS-1']))
        ->assertOk();
});

it('incluye el número de resolución en el enlace de descarga del modal de secretaría', function () {
    $userSecretaria = User::factory()->create();
    $userSecretaria->assignRole('secretaria_general');
    $this->actingAs($userSecretaria);

    $tipo = TipoSolicitud::factory()->create(['categoria' => Solicitud::CATEGORIA_SALUD_MATERNIDAD]);
    $solicitud = Solicitud::factory()->create([
        'tipo_solicitud_id' => $tipo->id,
        'estado' => Solicitud::ESTADO_PENDIENTE,
    ]);

    $schema = Schema::make(Livewire::test(ListSolicituds::class)->instance())
        ->components(SolicitudResource::getAprobarFormSchema($solicitud));

    // El campo de número de resolución debe existir para Secretaría.
    $nombres = collect($schema->getComponents())->map(fn ($componente) => $componente->getName());

    expect($nombres)->toContain('numero_resolucion')
        ->and($nombres)->toContain('solicitud_documento')
        ->and($nombres)->toContain('confirmo_documento_integrado');

    // Al cargar el número, el enlace de descarga del PDF lo incluye.
    $schema->fill(['numero_resolucion' => 'RESOL-MODAL-55']);

    $placeholder = collect($schema->getComponents())
        ->first(fn ($componente) => $componente->getName() === 'solicitud_documento');

    expect((string) $placeholder->getContent())->toContain('numero=RESOL-MODAL-55')
        ->and((string) $placeholder->getContent())->toContain('pdf-integrado');
});

it('muestra el número de resolución en el detalle de la solicitud', function () {
    $userSecretaria = User::factory()->create();
    $userSecretaria->assignRole('secretaria_general');
    $this->actingAs($userSecretaria);

    $solicitud = Solicitud::factory()->create(['estado' => Solicitud::ESTADO_PENDIENTE]);
    $solicitud->resolucion()->create([
        'numero' => 'RESOL-DETALLE-1',
        'fecha_emision' => now(),
    ]);

    $livewire = Livewire::test(ListSolicituds::class)->instance();
    $infolist = SolicitudResource::infolist(Schema::make($livewire))->record($solicitud);

    $nombres = collect($infolist->getFlatComponents(withHidden: true))
        ->map(fn ($component) => method_exists($component, 'getName') ? $component->getName() : null)
        ->filter()
        ->values();

    expect($nombres)->toContain('resolucion.numero')
        ->and($nombres)->toContain('resolucion.fecha_emision');
});

it('integra el número de resolución en el PDF firmado por la etapa anterior (Secretaría)', function () {
    Storage::fake('local');

    [$solicitud, $userJefatura] = crearSolicitudConJefatura();

    // Jefatura firma (sube un PDF real) y la solicitud avanza a Secretaría.
    $rutaFirmaJefatura = 'documentos/firmados/jefatura.pdf';
    Storage::disk('local')->put($rutaFirmaJefatura, crearPdfDePrueba('Firma de Jefatura'));

    $this->actingAs($userJefatura);
    SolicitudResource::procesarAprobacion($solicitud, 'aprobado', null, null, $rutaFirmaJefatura);

    $solicitud->refresh();

    expect($solicitud->etapaActual())->toBe(Solicitud::ETAPA_SECRETARIA)
        ->and($solicitud->documentoFirmadoVigente())->not->toBeNull();

    $userSecretaria = User::factory()->create();
    $userSecretaria->assignRole('secretaria_general');
    $this->actingAs($userSecretaria);

    $respuesta = $this->get(route('solicitud.pdf.integrado', [
        'solicitud' => $solicitud,
        'numero' => 'RESOL-MERGE-77',
    ]));

    $respuesta->assertOk();

    $contenido = $respuesta->streamedContent();

    // Es un PDF y el número quedó estampado dentro del archivo firmado.
    expect($contenido)->toContain('%PDF')
        ->and(extraerTextoPdf($contenido))->toContain('RESOL-MERGE-77');
});

it('la descarga integrada cae al PDF del sistema si no hay documento firmado previo', function () {
    Storage::fake('local');

    [$solicitud, $userJefatura] = crearSolicitudConJefatura();

    $this->actingAs($userJefatura);

    $respuesta = $this->get(route('solicitud.pdf.integrado', [
        'solicitud' => $solicitud,
        'numero' => 'RESOL-FALLBACK-1',
    ]));

    $respuesta->assertOk();

    expect($respuesta->streamedContent())->toContain('%PDF');
});

it('protege la descarga integrada para usuarios ajenos a la solicitud', function () {
    Storage::fake('local');

    [$solicitud] = crearSolicitudConJefatura();

    $intruso = User::factory()->create();

    $this->actingAs($intruso)
        ->get(route('solicitud.pdf.integrado', ['solicitud' => $solicitud, 'numero' => 'X']))
        ->assertForbidden();
});

it('exige en Secretaría confirmar la descarga del documento integrado antes de aprobar', function () {
    Storage::fake('local');

    [$solicitud, $userJefatura] = crearSolicitudConJefatura();
    $userSecretaria = avanzarSolicitudASecretaria($solicitud, $userJefatura);

    $solicitud->refresh();

    expect($solicitud->etapaActual())->toBe(Solicitud::ETAPA_SECRETARIA);

    $this->actingAs($userSecretaria);
    Storage::disk('local')->put('documentos/firmados/secretaria.pdf', crearPdfDePrueba('Firma de Secretaría'));

    // Sin la confirmación: el candado del servidor lo rechaza.
    $exception = null;

    try {
        SolicitudResource::procesarAprobacion(
            $solicitud,
            'aprobado',
            null,
            'RESOL-SEC-1',
            'documentos/firmados/secretaria.pdf',
            false,
        );
    } catch (ValidationException $e) {
        $exception = $e;
    }

    expect($exception)->not->toBeNull()
        ->and($exception->errors())->toHaveKey('confirmo_documento_integrado');

    $solicitud->refresh();

    expect($solicitud->etapaActual())->toBe(Solicitud::ETAPA_SECRETARIA);

    // Confirmando: aprueba y avanza a Presidencia.
    SolicitudResource::procesarAprobacion(
        $solicitud,
        'aprobado',
        null,
        'RESOL-SEC-1',
        'documentos/firmados/secretaria.pdf',
        true,
    );

    $solicitud->refresh();

    expect($solicitud->etapaActual())->toBe(Solicitud::ETAPA_DIRECCION)
        ->and($solicitud->resolucion?->numero)->toBe('RESOL-SEC-1');
});

it('exige el número de resolución para aprobar en Secretaría', function () {
    Storage::fake('local');

    [$solicitud, $userJefatura] = crearSolicitudConJefatura();
    $userSecretaria = avanzarSolicitudASecretaria($solicitud, $userJefatura);

    $solicitud->refresh();

    $this->actingAs($userSecretaria);
    Storage::disk('local')->put('documentos/firmados/secretaria.pdf', crearPdfDePrueba('Firma de Secretaría'));

    $exception = null;

    try {
        SolicitudResource::procesarAprobacion(
            $solicitud,
            'aprobado',
            null,
            null,
            'documentos/firmados/secretaria.pdf',
            true,
        );
    } catch (ValidationException $e) {
        $exception = $e;
    }

    expect($exception)->not->toBeNull()
        ->and($exception->errors())->toHaveKey('numero_resolucion');

    $solicitud->refresh();

    expect($solicitud->etapaActual())->toBe(Solicitud::ETAPA_SECRETARIA);
});
