<?php

use App\Filament\Admin\Resources\Saldos\SaldoResource;
use App\Models\Saldo;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('administrador', 'web');

    $user = User::factory()->create();
    $user->assignRole('administrador');

    $this->actingAs($user);
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
