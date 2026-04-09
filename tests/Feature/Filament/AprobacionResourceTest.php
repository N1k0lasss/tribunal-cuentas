<?php

use App\Filament\Admin\Resources\Aprobacions\AprobacionResource;
use App\Models\Aprobacion;
use App\Models\User;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
    $user = User::factory()->create();
    actingAs($user);
});

it('can render Aprobacions list page', function () {
    get(AprobacionResource::getUrl('index'))
        ->assertSuccessful();
});

it('can render Aprobacions create page', function () {
    get(AprobacionResource::getUrl('create'))
        ->assertSuccessful();
});

it('can render Aprobacions edit page', function () {
    $aprobacion = Aprobacion::factory()->create();

    get(AprobacionResource::getUrl('edit', ['record' => $aprobacion]))
        ->assertForbidden();
});
