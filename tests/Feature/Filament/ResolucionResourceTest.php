<?php

use App\Filament\Admin\Resources\Resolucions\Pages\ListResolucions;
use App\Filament\Admin\Resources\Resolucions\ResolucionResource;
use App\Models\Agente;
use App\Models\Area;
use App\Models\Documento;
use App\Models\Resolucion;
use App\Models\Solicitud;
use App\Models\TipoSolicitud;
use App\Models\User;
use App\Services\ResolucionDiariaService;
use Filament\Actions\Testing\TestAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    foreach (['administrador', 'agente', 'jefatura', 'secretaria_general', 'presidencia', 'personal'] as $role) {
        Role::findOrCreate($role, 'web');
    }

    $this->actingAs(usuarioResolucion('administrador'));
});

function usuarioResolucion(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

it('can render Resolucions list page', function () {
    $this->get(ResolucionResource::getUrl('index'))
        ->assertSuccessful();
});

it('no renderiza la página crear de resoluciones', function () {
    expect(fn () => ResolucionResource::getUrl('create'))->toThrow(Exception::class);
});

it('puede editar una resolución diaria', function () {
    $resolucion = Resolucion::factory()->daily()->create();

    $this->get(ResolucionResource::getUrl('edit', ['record' => $resolucion]))
        ->assertSuccessful();
});

it('permite subir el PDF firmado del día a secretaria general', function () {
    $this->actingAs(usuarioResolucion('secretaria_general'));

    expect(Gate::allows('subirPdfFirmadoDelDia', Resolucion::class))->toBeTrue();
});

it('permite al administrador subir el PDF firmado del día', function () {
    $this->actingAs(usuarioResolucion('administrador'));

    expect(Gate::allows('subirPdfFirmadoDelDia', Resolucion::class))->toBeTrue();
});

it('niega subir el PDF firmado del día a jefatura, presidencia, agente y personal', function (string $role) {
    $this->actingAs(usuarioResolucion($role));

    expect(Gate::denies('subirPdfFirmadoDelDia', Resolucion::class))->toBeTrue();
})->with(['jefatura', 'presidencia', 'agente', 'personal']);

it('muestra las acciones de generar y subir firmado en el encabezado para secretaria general', function () {
    $this->actingAs(usuarioResolucion('secretaria_general'));

    Livewire::test(ListResolucions::class)
        ->assertActionVisible('generar_resolucion_dia')
        ->assertActionVisible('subir_resolucion_firmada_dia');
});

it('genera la resolución del día con todas las solicitudes registradas hoy y la descarga', function () {
    Storage::fake('local');

    $area = Area::factory()->create();
    $agente = Agente::factory()->create(['area_id' => $area->id]);
    $tipo = TipoSolicitud::factory()->create();

    $solicitud = Solicitud::factory()->create([
        'agente_id' => $agente->id,
        'tipo_solicitud_id' => $tipo->id,
        'estado' => Solicitud::ESTADO_APROBADO,
        'fecha_solicitud' => ResolucionDiariaService::hoy(),
        'fecha_inicio' => ResolucionDiariaService::hoy()->addMonth(),
        'fecha_fin' => ResolucionDiariaService::hoy()->addMonth()->addDays(2),
    ]);

    Livewire::test(ListResolucions::class)
        ->mountAction(TestAction::make('generar_resolucion_dia'))
        ->setActionData([
            'numero' => 'RES-001-2026',
            'detalle' => 'Resumen diario',
        ])
        ->callMountedAction();

    $resolucion = Resolucion::query()->diarias()->first();

    expect($resolucion)->not->toBeNull()
        ->and($resolucion->numero)->toBe('RES-001-2026')
        ->and($resolucion->solicitudes)->toHaveCount(1)
        ->and($resolucion->solicitudes->first()->id)->toBe($solicitud->id);
});

it('incluye todas las solicitudes registradas hoy sin importar su estado', function () {
    Storage::fake('local');

    $solicitudAyer = Solicitud::factory()->create([
        'estado' => Solicitud::ESTADO_APROBADO,
        'fecha_solicitud' => ResolucionDiariaService::hoy()->subDay(),
    ]);

    $solicitudAprobadaHoy = Solicitud::factory()->create([
        'estado' => Solicitud::ESTADO_APROBADO,
        'fecha_solicitud' => ResolucionDiariaService::hoy(),
    ]);

    $solicitudPendienteHoy = Solicitud::factory()->create([
        'estado' => Solicitud::ESTADO_PENDIENTE,
        'fecha_solicitud' => ResolucionDiariaService::hoy(),
    ]);

    $solicitudRechazadaHoy = Solicitud::factory()->create([
        'estado' => Solicitud::ESTADO_RECHAZADO,
        'fecha_solicitud' => ResolucionDiariaService::hoy(),
    ]);

    Livewire::test(ListResolucions::class)
        ->mountAction(TestAction::make('generar_resolucion_dia'))
        ->setActionData(['numero' => 'RES-002-2026'])
        ->callMountedAction();

    $resolucion = Resolucion::query()->diarias()->firstOrFail();

    expect($resolucion->solicitudes->pluck('id')->all())
        ->toContain($solicitudAprobadaHoy->id)
        ->toContain($solicitudPendienteHoy->id)
        ->toContain($solicitudRechazadaHoy->id)
        ->not->toContain($solicitudAyer->id);
});

it('registra la fecha de aprobación al aprobar una solicitud', function () {
    $solicitud = Solicitud::factory()->create([
        'estado' => Solicitud::ESTADO_PENDIENTE,
    ]);

    $solicitud->update(['estado' => Solicitud::ESTADO_APROBADO]);

    expect($solicitud->refresh()->fecha_aprobacion?->toDateString())
        ->toBe(ResolucionDiariaService::hoy()->toDateString());
});

it('no genera la resolución si no hay solicitudes registradas para el día', function () {
    Livewire::test(ListResolucions::class)
        ->mountAction(TestAction::make('generar_resolucion_dia'))
        ->setActionData([
            'numero' => 'RES-002-2026',
        ])
        ->callMountedAction();

    expect(Resolucion::query()->diarias()->count())->toBe(0);
});

it('guarda el PDF firmado de la resolución del día sin vincularlo a una solicitud', function () {
    Storage::fake('local');

    Resolucion::factory()->daily()->create([
        'numero' => 'RES-003-2026',
    ]);

    Livewire::test(ListResolucions::class)
        ->mountAction(TestAction::make('subir_resolucion_firmada_dia'))
        ->setActionData([
            'archivo' => UploadedFile::fake()->create('resolucion-diaria-firmada.pdf', 100, 'application/pdf'),
        ])
        ->callMountedAction();

    $documento = Documento::query()
        ->whereHas('resolucion', fn ($q) => $q->diarias())
        ->where('tipo_documento', Documento::TIPO_RESOLUCION_FIRMADA)
        ->first();

    expect($documento)->not->toBeNull()
        ->and($documento->solicitud_id)->toBeNull()
        ->and($documento->ruta)->not->toBeEmpty();

    Storage::disk('local')->assertExists($documento->ruta);
});

it('permite al administrador reemplazar el PDF firmado anterior del día y borra el archivo viejo', function () {
    Storage::fake('local');

    $resolucion = Resolucion::factory()->daily()->create([
        'numero' => 'RES-004-2026',
    ]);

    Livewire::test(ListResolucions::class)
        ->mountAction(TestAction::make('subir_resolucion_firmada_dia'))
        ->setActionData([
            'archivo' => UploadedFile::fake()->create('resolucion-diaria-firmada.pdf', 100, 'application/pdf'),
        ])
        ->callMountedAction();

    $anterior = Documento::query()
        ->where('resolucion_id', $resolucion->id)
        ->where('tipo_documento', Documento::TIPO_RESOLUCION_FIRMADA)
        ->firstOrFail();

    Storage::disk('local')->assertExists($anterior->ruta);

    Livewire::test(ListResolucions::class)
        ->mountAction(TestAction::make('subir_resolucion_firmada_dia'))
        ->setActionData([
            'archivo' => UploadedFile::fake()->create('resolucion-diaria-firmada-nueva.pdf', 100, 'application/pdf'),
        ])
        ->callMountedAction();

    $documentos = Documento::query()
        ->where('resolucion_id', $resolucion->id)
        ->where('tipo_documento', Documento::TIPO_RESOLUCION_FIRMADA)
        ->get();

    expect($documentos)->toHaveCount(1);

    $actual = $documentos->first();

    expect($actual->ruta)->not->toBe($anterior->ruta);

    Storage::disk('local')->assertMissing($anterior->ruta);
    Storage::disk('local')->assertExists($actual->ruta);
});

it('impide que secretaría general reemplace un PDF firmado existente', function () {
    Storage::fake('local');

    $resolucion = Resolucion::factory()->daily()->create([
        'numero' => 'RES-004-BIS-2026',
    ]);

    $documento = Documento::conPermisoDocumentoSistema(fn () => Documento::factory()->create([
        'tipo_documento' => Documento::TIPO_RESOLUCION_FIRMADA,
        'solicitud_id' => null,
        'resolucion_id' => $resolucion->id,
        'ruta' => 'documentos/firmados/anterior.pdf',
        'nombre_archivo' => 'anterior.pdf',
    ]));

    Storage::disk('local')->put($documento->ruta, 'pdf anterior');

    $this->actingAs(usuarioResolucion('secretaria_general'));

    Livewire::test(ListResolucions::class)
        ->mountAction(TestAction::make('subir_resolucion_firmada_dia'))
        ->setActionData([
            'archivo' => UploadedFile::fake()->create('resolucion-nueva.pdf', 100, 'application/pdf'),
        ])
        ->callMountedAction();

    $documentos = Documento::query()
        ->where('resolucion_id', $resolucion->id)
        ->where('tipo_documento', Documento::TIPO_RESOLUCION_FIRMADA)
        ->get();

    expect($documentos)->toHaveCount(1)
        ->and($documentos->first()->ruta)->toBe('documentos/firmados/anterior.pdf');

    Storage::disk('local')->assertExists('documentos/firmados/anterior.pdf');
});

it('no permite regenerar ni editar una resolución que ya tiene PDF firmado', function () {
    Storage::fake('local');

    $resolucion = Resolucion::factory()->daily()->create([
        'numero' => 'RES-005-2026',
    ]);

    Documento::conPermisoDocumentoSistema(fn () => $resolucion->documentos()->create([
        'tipo_documento' => Documento::TIPO_RESOLUCION_FIRMADA,
        'nombre_archivo' => 'resolucion-firmada.pdf',
        'ruta' => 'documentos/firmados/resolucion-firmada.pdf',
        'solicitud_id' => null,
    ]));

    Storage::disk('local')->put('documentos/firmados/resolucion-firmada.pdf', 'pdf');

    Livewire::test(ListResolucions::class)
        ->assertActionDisabled('generar_resolucion_dia');

    expect(Gate::denies('update', $resolucion))->toBeTrue();
});

it('el pdf de la resolución solo incluye la firma de secretaría general', function () {
    $resolucion = Resolucion::factory()->daily()->create();

    $html = view('pdf.resolucion-diaria', ['resolucion' => $resolucion])->render();

    expect($html)
        ->toContain('Secretaría General')
        ->not->toContain('Jefatura')
        ->not->toContain('Presidencia');
});

it('previsualiza el pdf de la resolución inline', function () {
    $resolucion = Resolucion::factory()->daily()->create();

    $response = $this->get(route('resolucion.pdf', $resolucion));

    $response->assertSuccessful();

    expect($response->headers->get('Content-Type'))->toContain('application/pdf')
        ->and($response->headers->get('Content-Disposition'))->toContain('inline');
});

it('descarga el pdf de la resolución como adjunto', function () {
    $resolucion = Resolucion::factory()->daily()->create();

    $response = $this->get(route('resolucion.pdf.descargar', $resolucion));

    $response->assertSuccessful();

    expect($response->headers->get('Content-Disposition'))->toContain('attachment');
});

it('prohíbe borrar resoluciones incluso al administrador', function () {
    $resolucion = Resolucion::factory()->create();

    expect(Gate::denies('delete', $resolucion))->toBeTrue()
        ->and($resolucion->delete())->toBeFalse();

    expect(Resolucion::find($resolucion->id))->not->toBeNull();
});

it('solo muestra resoluciones diarias en el listado', function () {
    $diaria = Resolucion::factory()->daily()->create();
    $individual = Resolucion::factory()->create();

    Livewire::test(ListResolucions::class)
        ->assertCanSeeTableRecords([$diaria])
        ->assertCanNotSeeTableRecords([$individual]);
});
