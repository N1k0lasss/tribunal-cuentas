<?php

use App\Filament\Resources\Areas\AreaResource;
use App\Models\Area;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('can render Areas list page', function () {
    $this->get(AreaResource::getUrl('index'))
         ->assertSuccessful();
});

it('can render Areas create page', function () {
    $this->get(AreaResource::getUrl('create'))
         ->assertSuccessful();
});

it('can render Areas edit page', function () {
    $area = Area::factory()->create();

    $this->get(AreaResource::getUrl('edit', ['record' => $area]))
         ->assertSuccessful();
});
