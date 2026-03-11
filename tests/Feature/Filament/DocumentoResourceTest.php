<?php

use App\Filament\Resources\Documentos\DocumentoResource;
use App\Models\Documento;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('can render Documentos list page', function () {
    $this->get(DocumentoResource::getUrl('index'))
         ->assertSuccessful();
});

it('can render Documentos create page', function () {
    $this->get(DocumentoResource::getUrl('create'))
         ->assertSuccessful();
});

it('can render Documentos edit page', function () {
    $documento = Documento::factory()->create();

    $this->get(DocumentoResource::getUrl('edit', ['record' => $documento]))
         ->assertSuccessful();
});
