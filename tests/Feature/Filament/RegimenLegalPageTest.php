<?php

use App\Filament\Admin\Pages\RegimenLegal;

it('allows agente to open regimen legal page', function () {
    loginAsRole('agente');

    $this->get(RegimenLegal::getUrl())
        ->assertSuccessful();
});

it('forbids admin from opening regimen legal page', function () {
    loginAsRole('administrador');

    $this->get(RegimenLegal::getUrl())
        ->assertForbidden();
});
