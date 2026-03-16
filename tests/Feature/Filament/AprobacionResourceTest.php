<?php

use App\Filament\Admin\Resources\Aprobacions\AprobacionResource;
use App\Models\Aprobacion;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('can render Aprobacions list page', function () {
    $this->get(AprobacionResource::getUrl('index'))
        ->assertSuccessful();
});

it('can render Aprobacions create page', function () {
    $this->get(AprobacionResource::getUrl('create'))
        ->assertSuccessful();
});

it('can render Aprobacions edit page', function () {
    $aprobacion = Aprobacion::factory()->create();

    $this->get(AprobacionResource::getUrl('edit', ['record' => $aprobacion]))
        ->assertSuccessful();
});
