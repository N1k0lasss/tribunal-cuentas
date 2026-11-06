<?php

use App\Filament\Admin\Resources\Saldos\SaldoResource;
use App\Models\Saldo;

beforeEach(function () {
    $this->actingAs(usuarioConRol('administrador'));
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

it('permite listar saldos a secretaría general y presidencia', function (string $rol) {
    $this->actingAs(usuarioConRol($rol));

    $this->get(SaldoResource::getUrl('index'))
        ->assertSuccessful();
})->with(['secretaria_general', 'presidencia']);

it('niega crear un saldo a secretaría general y presidencia', function (string $rol) {
    $this->actingAs(usuarioConRol($rol));

    $this->get(SaldoResource::getUrl('create'))
        ->assertForbidden();
})->with(['secretaria_general', 'presidencia']);

it('niega editar un saldo a secretaría general y presidencia', function (string $rol) {
    $this->actingAs(usuarioConRol($rol));

    $saldo = Saldo::factory()->create();

    $this->get(SaldoResource::getUrl('edit', ['record' => $saldo]))
        ->assertForbidden();
})->with(['secretaria_general', 'presidencia']);

it('niega el listado de saldos a un agente', function () {
    $this->actingAs(usuarioConRol('agente'));

    $this->get(SaldoResource::getUrl('index'))
        ->assertForbidden();
});
