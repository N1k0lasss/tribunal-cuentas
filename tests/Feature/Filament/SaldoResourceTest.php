<?php

use App\Filament\Resources\Saldos\SaldoResource;
use App\Models\Saldo;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
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
