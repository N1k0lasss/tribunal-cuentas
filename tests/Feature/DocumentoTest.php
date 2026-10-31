<?php

use App\Models\Agente;
use App\Models\Documento;
use App\Models\Solicitud;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    foreach (['administrador', 'agente', 'jefatura', 'secretaria_general', 'presidencia'] as $role) {
        Role::findOrCreate($role, 'web');
    }

    Storage::fake('local');
});

function usuarioDescargaDocumento(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

it('sirve un documento con cabeceras seguras', function () {
    $user = usuarioDescargaDocumento('secretaria_general');
    $documento = Documento::factory()->withFile()->create();

    $response = $this->actingAs($user)->get(route('documento.ver', $documento));

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/pdf')
        ->assertHeader('X-Content-Type-Options', 'nosniff');

    expect($response->headers->get('Cache-Control'))
        ->toContain('private')
        ->toContain('no-store')
        ->toContain('max-age=0');
});

it('impide que un agente vea documentos de otro agente', function () {
    $user = usuarioDescargaDocumento('agente');
    Agente::factory()->create(['user_id' => $user->id]);

    $otroAgente = Agente::factory()->create();
    $solicitud = Solicitud::factory()->create(['agente_id' => $otroAgente->id]);
    $documento = Documento::factory()->withFile()->create(['solicitud_id' => $solicitud->id]);

    $this->actingAs($user)
        ->get(route('documento.ver', $documento))
        ->assertForbidden();
});

it('rechaza rutas fuera del directorio de documentos', function () {
    $user = usuarioDescargaDocumento('administrador');
    $documento = Documento::factory()->create(['ruta' => 'documentos/../secreto.pdf']);

    Storage::disk('local')->put('secreto.pdf', 'contenido');

    $this->actingAs($user)
        ->get(route('documento.ver', $documento))
        ->assertNotFound();
});

it('rechaza archivos inexistentes', function () {
    $user = usuarioDescargaDocumento('administrador');
    $documento = Documento::factory()->create(['ruta' => 'documentos/no-existe.pdf']);

    $this->actingAs($user)
        ->get(route('documento.ver', $documento))
        ->assertNotFound();
});

it('rechaza archivos con mime no permitido', function () {
    $user = usuarioDescargaDocumento('administrador');
    $documento = Documento::factory()->withFile('<svg xmlns="http://www.w3.org/2000/svg"></svg>', 'svg')->create();

    $this->actingAs($user)
        ->get(route('documento.ver', $documento))
        ->assertNotFound();
});

it('borra el archivo al eliminar el documento', function () {
    $documento = Documento::factory()->withFile()->create();
    $ruta = $documento->ruta;

    $documento->delete();

    Storage::disk('local')->assertMissing($ruta);
});

it('borra el archivo anterior al cambiar la ruta', function () {
    $documento = Documento::factory()->withFile()->create();
    $rutaAnterior = $documento->ruta;
    $rutaNueva = 'documentos/'.Str::uuid().'.pdf';

    Storage::disk('local')->put($rutaNueva, "%PDF-1.4\n");

    $documento->update(['ruta' => $rutaNueva]);

    Storage::disk('local')->assertMissing($rutaAnterior);
    Storage::disk('local')->assertExists($rutaNueva);
});

it('bloquea eliminar una solicitud con documentos', function () {
    $solicitud = Solicitud::factory()->create();
    Documento::factory()->withFile()->create(['solicitud_id' => $solicitud->id]);

    expect(fn () => $solicitud->delete())->toThrow(DomainException::class);
});

it('bloquea crear documentos de sistema fuera del flujo autorizado', function () {
    expect(fn () => Documento::factory()->create([
        'tipo_documento' => Documento::TIPO_RESOLUCION_FIRMADA,
        'solicitud_id' => null,
    ]))->toThrow(DomainException::class);
});
