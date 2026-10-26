<?php

use App\Filament\Admin\Pages\InformeDiarioPage;
use App\Models\Agente;
use App\Models\Area;
use App\Models\InformeDiario;
use App\Models\InformeDiarioDetalle;
use App\Models\Solicitud;
use App\Models\TipoSolicitud;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Role setup
    Role::findOrCreate('administrador');
    Role::findOrCreate('secretaria_general');
    Role::findOrCreate('agente');
});

test('informe diario page can be accessed by administrador', function () {
    $user = User::factory()->create();
    $user->assignRole('administrador');

    $this->actingAs($user);

    expect(InformeDiarioPage::canAccess())->toBeTrue();
});

test('informe diario page cannot be accessed by regular agente role', function () {
    $user = User::factory()->create();
    $user->assignRole('agente');

    $this->actingAs($user);

    expect(InformeDiarioPage::canAccess())->toBeFalse();
});

test('informe diario saves and persists agent novelties correctly', function () {
    $admin = User::factory()->create();
    $admin->assignRole('administrador');

    $area = Area::create(['nombre' => 'Mesa de Entradas', 'codigo' => 'ME']);
    $agente = Agente::create([
        'nombre' => 'Juan',
        'apellido' => 'Pérez',
        'dni' => '12345678',
        'area_id' => $area->id,
        'fecha_ingreso' => '2020-01-01',
    ]);

    $fecha = date('Y-m-d');

    $this->actingAs($admin);

    Livewire::test(InformeDiarioPage::class)
        ->set('fecha', $fecha)
        ->set('destinatario', 'Secretaría General')
        ->set('observacionesGenerales', 'Informe diario sin novedades mayores')
        ->set('detalles.0.tipo_novedad', 'tardanza')
        ->set('detalles.0.hora_llegada', '08:45')
        ->set('detalles.0.minutos_tardanza', 15)
        ->set('detalles.0.observaciones', 'Demora por transporte')
        ->call('guardarInforme');

    $informe = InformeDiario::whereDate('fecha', $fecha)->first();
    expect($informe)->not->toBeNull();
    expect($informe->destinatario)->toBe('Secretaría General');
    expect($informe->observaciones_generales)->toBe('Informe diario sin novedades mayores');

    $detalle = InformeDiarioDetalle::where('informe_diario_id', $informe->id)
        ->where('agente_id', $agente->id)
        ->first();

    expect($detalle)->not->toBeNull();
    expect($detalle->tipo_novedad)->toBe('tardanza');
    expect($detalle->hora_llegada)->toBe('08:45');
    expect($detalle->minutos_tardanza)->toBe(15);
    expect($detalle->observaciones)->toBe('Demora por transporte');
});

test('informe diario automatically loads approved licenses for specified date', function () {
    $admin = User::factory()->create();
    $admin->assignRole('administrador');

    $area = Area::create(['nombre' => 'Sistemas', 'codigo' => 'SIS']);
    $agente = Agente::create([
        'nombre' => 'María',
        'apellido' => 'Gómez',
        'dni' => '87654321',
        'area_id' => $area->id,
        'fecha_ingreso' => '2021-05-10',
    ]);

    $tipoSolicitud = TipoSolicitud::create([
        'nombre' => 'Licencia Anual Ordinaria',
        'codigo' => 'LAO',
        'dias_maximos_anio' => 30,
        'requiere_justificacion' => false,
    ]);

    $solicitud = Solicitud::create([
        'agente_id' => $agente->id,
        'tipo_solicitud_id' => $tipoSolicitud->id,
        'fecha_inicio' => '2026-08-15',
        'fecha_fin' => '2026-08-20',
        'dias_solicitados' => 6,
        'estado' => 'aprobado',
        'fecha_solicitud' => '2026-08-10',
    ]);

    $this->actingAs($admin);

    Livewire::test(InformeDiarioPage::class)
        ->set('fecha', '2026-08-15')
        ->assertSet('detalles.0.tipo_novedad', 'licencia')
        ->assertSet('detalles.0.solicitud_id', $solicitud->id)
        ->assertSet('detalles.0.tipo_solicitud_id', $tipoSolicitud->id);
});

test('informe diario page can be accessed by secretaria_general but cannot save changes', function () {
    $secretario = User::factory()->create();
    $secretario->assignRole('secretaria_general');

    $area = Area::create(['nombre' => 'Secretaría', 'codigo' => 'SEC']);
    Agente::create([
        'nombre' => 'Ana',
        'apellido' => 'Lopez',
        'dni' => '11223344',
        'area_id' => $area->id,
        'fecha_ingreso' => '2020-01-01',
    ]);

    $fecha = date('Y-m-d');

    $this->actingAs($secretario);

    expect(InformeDiarioPage::canAccess())->toBeTrue();

    Livewire::test(InformeDiarioPage::class)
        ->set('fecha', $fecha)
        ->set('observacionesGenerales', 'Intento de guardar por secretaria')
        ->call('guardarInforme');

    $informe = InformeDiario::whereDate('fecha', $fecha)->first();
    expect($informe)->toBeNull();
});
