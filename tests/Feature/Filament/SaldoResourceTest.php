<?php

use App\Filament\Admin\Resources\Saldos\SaldoResource;
use App\Models\Agente;
use App\Models\Saldo;
use App\Models\Solicitud;
use App\Models\TipoSolicitud;
use App\Services\SaldoLicenciaService;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    loginAsRole('administrador');
});

it('can render Saldos list page', function () {
    $this->get(SaldoResource::getUrl('index'))
        ->assertSuccessful();
});

it('can render Saldos create page', function () {
    $this->get(SaldoResource::getUrl('create'))
        ->assertSuccessful();
});

it('can render Saldos edit page', function () {
    $saldo = Saldo::factory()->create();

    $this->get(SaldoResource::getUrl('edit', ['record' => $saldo]))
        ->assertSuccessful();
});

it('can open Saldos create page without runtime type errors', function () {
    $this->get(SaldoResource::getUrl('create'))
        ->assertOk()
        ->assertDontSee('TypeError');
});

it('throws validation exception when trying to consume more saldo than available', function () {
    $agente = Agente::factory()->create();

    $tipo = TipoSolicitud::factory()->create([
        'nombre' => 'Licencia Anual Ordinaria',
        'unidad_medida' => 'dias',
        'tipo_dias' => 'corridos',
    ]);

    $solicitud = Solicitud::factory()->create([
        'agente_id' => $agente->id,
        'tipo_solicitud_id' => $tipo->id,
        'dias_solicitados' => 7,
        'estado' => Solicitud::ESTADO_EN_REVISION,
    ]);

    Saldo::factory()->create([
        'agente_id' => $agente->id,
        'anio' => now()->year,
        'tipo_saldo' => $tipo->nombre,
        'dias_otorgados' => 3,
        'dias_usados' => 0,
    ]);

    expect(fn () => app(SaldoLicenciaService::class)->consumirSaldoPorAprobacion($solicitud))
        ->toThrow(ValidationException::class);
});
