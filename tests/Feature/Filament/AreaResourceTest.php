<?php

use App\Filament\Admin\Resources\Areas\AreaResource;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('can render Areas list page', function () {
    $this->get(AreaResource::getUrl('index'))
        ->assertSuccessful();
});
