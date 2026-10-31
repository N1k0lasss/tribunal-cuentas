<?php

use App\Filament\Admin\Resources\Documentos\DocumentoResource;
use App\Filament\Admin\Resources\Documentos\Pages\ListDocumentos;
use App\Models\Documento;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    foreach (['administrador', 'secretaria_general', 'jefatura', 'presidencia'] as $role) {
        Role::findOrCreate($role, 'web');
    }

    $this->actingAs(usuarioDocumento('secretaria_general'));
});

function usuarioDocumento(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

it('can render Documentos list page', function () {
    $this->get(DocumentoResource::getUrl('index'))
        ->assertSuccessful();
});

it('can render Documentos create page', function () {
    $this->get(DocumentoResource::getUrl('create'))
        ->assertSuccessful();
});

it('can render Documentos edit page for non-system documents', function () {
    $documento = Documento::factory()->create();

    $this->get(DocumentoResource::getUrl('edit', ['record' => $documento]))
        ->assertSuccessful();
});

it('niega editar documentos de sistema incluso al administrador', function () {
    $documento = Documento::conPermisoDocumentoSistema(fn () => Documento::factory()->create([
        'tipo_documento' => Documento::TIPO_RESOLUCION_FIRMADA,
        'solicitud_id' => null,
    ]));

    $this->actingAs(usuarioDocumento('administrador'));

    expect(Gate::denies('update', $documento))->toBeTrue()
        ->and(Gate::denies('delete', $documento))->toBeTrue();
});

it('oculta la edición de documentos de sistema en el listado', function () {
    $documento = Documento::conPermisoDocumentoSistema(fn () => Documento::factory()->create([
        'tipo_documento' => Documento::TIPO_RESOLUCION_FIRMADA,
        'solicitud_id' => null,
    ]));

    $this->actingAs(usuarioDocumento('administrador'));

    Livewire::test(ListDocumentos::class)
        ->assertTableActionHidden('edit', $documento)
        ->assertTableActionHidden('delete', $documento);
});
