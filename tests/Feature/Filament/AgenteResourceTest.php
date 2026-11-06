<?php

use App\Filament\Admin\Resources\Agentes\AgenteResource;
use App\Models\Agente;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('administrador', 'web');

    $user = User::factory()->create();
    $user->assignRole('administrador');

    $this->actingAs($user);
});

it('can render Agentes list page', function () {
    $this->get(AgenteResource::getUrl('index'))
        ->assertSuccessful();
});

it('can render Agentes view page', function () {
    $agente = Agente::factory()->create();

    $this->get(AgenteResource::getUrl('view', ['record' => $agente]))
        ->assertSuccessful();
});
