<?php

use App\Filament\Admin\Resources\Resolucions\Pages\ListResolucions;
use App\Filament\Admin\Resources\Resolucions\ResolucionResource;
use App\Models\Documento;
use App\Models\Resolucion;
use App\Models\User;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    foreach (['administrador', 'agente', 'jefatura', 'secretaria_general', 'presidencia', 'personal'] as $role) {
        Role::findOrCreate($role, 'web');
    }

    $this->actingAs(usuarioResolucion('administrador'));
});

function usuarioResolucion(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

it('can render Resolucions list page', function () {
    $this->get(ResolucionResource::getUrl('index'))
        ->assertSuccessful();
});

it('can render Resolucions create page', function () {
    $this->get(ResolucionResource::getUrl('create'))
        ->assertSuccessful();
});

it('can render Resolucions edit page', function () {
    $resolucion = Resolucion::factory()->create();

    $this->get(ResolucionResource::getUrl('edit', ['record' => $resolucion]))
        ->assertSuccessful();
});

it('permite subir el PDF firmado a los roles habilitados', function (string $role) {
    $resolucion = Resolucion::factory()->create();

    $this->actingAs(usuarioResolucion($role));

    expect(Gate::allows('subirPdfFirmado', $resolucion))->toBeTrue();
})->with(['administrador', 'jefatura', 'secretaria_general', 'presidencia']);

it('niega subir el PDF firmado a agente y personal', function (string $role) {
    $resolucion = Resolucion::factory()->create();

    $this->actingAs(usuarioResolucion($role));

    expect(Gate::denies('subirPdfFirmado', $resolucion))->toBeTrue();
})->with(['agente', 'personal']);

it('muestra la acción de subida en la tabla para los roles habilitados', function (string $role) {
    $resolucion = Resolucion::factory()->create();

    $this->actingAs(usuarioResolucion($role));

    Livewire::test(ListResolucions::class)
        ->assertTableActionVisible('subir_pdf_firmado', $resolucion);
})->with(['administrador', 'jefatura', 'secretaria_general', 'presidencia']);

it('guarda el PDF firmado vinculado a la resolución y a la solicitud', function () {
    Storage::fake('local');

    $resolucion = Resolucion::factory()->create();

    Livewire::test(ListResolucions::class)
        ->mountAction(TestAction::make('subir_pdf_firmado')->table($resolucion))
        ->setActionData([
            'archivo' => UploadedFile::fake()->create('resolucion-firmada.pdf', 100, 'application/pdf'),
        ])
        ->callMountedAction();

    $documento = Documento::query()
        ->where('resolucion_id', $resolucion->id)
        ->where('tipo_documento', Documento::TIPO_RESOLUCION_FIRMADA)
        ->first();

    expect($documento)->not->toBeNull()
        ->and($documento->solicitud_id)->toBe($resolucion->solicitud_id)
        ->and($documento->ruta)->not->toBeEmpty();

    Storage::disk('local')->assertExists($documento->ruta);
});

it('reemplaza el PDF firmado anterior y borra el archivo viejo', function () {
    Storage::fake('local');

    $resolucion = Resolucion::factory()->create();

    Livewire::test(ListResolucions::class)
        ->mountAction(TestAction::make('subir_pdf_firmado')->table($resolucion))
        ->setActionData([
            'archivo' => UploadedFile::fake()->create('resolucion-firmada.pdf', 100, 'application/pdf'),
        ])
        ->callMountedAction();

    $anterior = Documento::query()
        ->where('resolucion_id', $resolucion->id)
        ->where('tipo_documento', Documento::TIPO_RESOLUCION_FIRMADA)
        ->firstOrFail();

    Storage::disk('local')->assertExists($anterior->ruta);

    Livewire::test(ListResolucions::class)
        ->mountAction(TestAction::make('subir_pdf_firmado')->table($resolucion))
        ->setActionData([
            'archivo' => UploadedFile::fake()->create('resolucion-firmada-nueva.pdf', 100, 'application/pdf'),
        ])
        ->callMountedAction();

    $documentos = Documento::query()
        ->where('resolucion_id', $resolucion->id)
        ->where('tipo_documento', Documento::TIPO_RESOLUCION_FIRMADA)
        ->get();

    expect($documentos)->toHaveCount(1);

    $actual = $documentos->first();

    expect($actual->ruta)->not->toBe($anterior->ruta);

    Storage::disk('local')->assertMissing($anterior->ruta);
    Storage::disk('local')->assertExists($actual->ruta);
});

it('prohíbe borrar resoluciones incluso al administrador', function () {
    $resolucion = Resolucion::factory()->create();

    expect(Gate::denies('delete', $resolucion))->toBeTrue()
        ->and($resolucion->delete())->toBeFalse();

    expect(Resolucion::find($resolucion->id))->not->toBeNull();
});

it('no muestra el borrado masivo en el listado de resoluciones', function () {
    Livewire::test(ListResolucions::class)
        ->assertTableBulkActionDoesNotExist(DeleteBulkAction::class);
});
