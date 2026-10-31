<?php

use App\Filament\Admin\Resources\Aprobacions\AprobacionResource;
use App\Models\Aprobacion;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    /** @var Authenticatable $user */
    $user = User::factory()->create();
    actingAs($user);
});

it('can render Aprobacions list page', function () {
    get(AprobacionResource::getUrl('index'))
        ->assertSuccessful();
});

it('cannot access Aprobacions create page', function () {
    get(AprobacionResource::getUrl('index').'/create')
        ->assertNotFound();
});

it('cannot access Aprobacions edit page for unowned record', function () {
    $aprobacion = Aprobacion::factory()->create();

    get(AprobacionResource::getUrl('edit', ['record' => $aprobacion]))
        ->assertNotFound();
});
