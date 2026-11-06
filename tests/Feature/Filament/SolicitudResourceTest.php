<?php

use App\Filament\Admin\Resources\Solicituds\Pages\ListSolicituds;
use App\Filament\Admin\Resources\Solicituds\SolicitudResource;
use App\Models\Agente;
use App\Models\Documento;
use App\Models\Resolucion;
use App\Models\Solicitud;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;

function resolucionDiariaPara(Solicitud $solicitud, string $numero = 'RES-DIA-001'): Resolucion
{
    $resolucion = Resolucion::factory()->daily()->create(['numero' => $numero]);

    $resolucion->solicitudes()->attach($solicitud);

    return $resolucion;
}

function abrirDetalleSolicitud(Solicitud $solicitud)
{
    return Livewire::test(ListSolicituds::class)
        ->mountAction(TestAction::make('view')->table($solicitud));
}

it('can render Solicituds list page', function () {
    $this->actingAs(usuarioConRol('agente'))
        ->get(SolicitudResource::getUrl('index'))
        ->assertSuccessful();
});

it('can render Solicituds create page', function () {
    $this->actingAs(usuarioConRol('agente'))
        ->get(SolicitudResource::getUrl('create'))
        ->assertSuccessful();
});

it('can render Solicituds edit page', function () {
    $user = usuarioConRol('agente');

    // Crear un agente vinculado al user
    $agente = Agente::factory()->create(['user_id' => $user->id]);

    // Crear una solicitud pendiente del agente
    $solicitud = Solicitud::factory()->create([
        'agente_id' => $agente->id,
        'estado' => 'pendiente',
    ]);

    $this->actingAs($user)
        ->get(SolicitudResource::getUrl('edit', ['record' => $solicitud]))
        ->assertSuccessful();
});

it('blocks edit page for solicitud en flujo', function () {
    $user = usuarioConRol('agente');

    $agente = Agente::factory()->create(['user_id' => $user->id]);

    $solicitud = Solicitud::factory()->create([
        'agente_id' => $agente->id,
        'estado' => 'en_revision',
    ]);

    $this->actingAs($user)
        ->get(SolicitudResource::getUrl('edit', ['record' => $solicitud]))
        ->assertForbidden();
});

it('blocks create page for non-agente role', function () {
    $this->actingAs(usuarioConRol('jefatura'))
        ->get(SolicitudResource::getUrl('create'))
        ->assertForbidden();
});

it('muestra la resolución del día en el detalle de la solicitud', function () {
    $this->actingAs(usuarioConRol('administrador'));

    $solicitud = Solicitud::factory()->create([
        'estado' => Solicitud::ESTADO_APROBADO,
    ]);

    resolucionDiariaPara($solicitud, 'RES-DIA-001');

    abrirDetalleSolicitud($solicitud)
        ->assertMountedActionModalSee('Resolución del día')
        ->assertMountedActionModalSee('RES-DIA-001')
        ->assertMountedActionModalSee('Pendiente');
});

it('no muestra la resolución del día si la solicitud no tiene una', function () {
    $this->actingAs(usuarioConRol('administrador'));

    $solicitud = Solicitud::factory()->create([
        'estado' => Solicitud::ESTADO_APROBADO,
    ]);

    abrirDetalleSolicitud($solicitud)
        ->assertMountedActionModalSee('Detalle de la Solicitud')
        ->assertMountedActionModalDontSee('Resolución del día');
});

it('no muestra la resolución del día a un agente', function () {
    $user = usuarioConRol('agente');
    $agente = Agente::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    $solicitud = Solicitud::factory()->create([
        'agente_id' => $agente->id,
        'estado' => Solicitud::ESTADO_PENDIENTE,
    ]);

    resolucionDiariaPara($solicitud, 'RES-DIA-AGENTE');

    abrirDetalleSolicitud($solicitud)
        ->assertMountedActionModalSee('Detalle de la Solicitud')
        ->assertMountedActionModalDontSee('RES-DIA-AGENTE')
        ->assertMountedActionModalDontSee('Resolución del día');
});

it('muestra el enlace al PDF firmado de la resolución del día', function () {
    $this->actingAs(usuarioConRol('administrador'));

    $solicitud = Solicitud::factory()->create([
        'estado' => Solicitud::ESTADO_APROBADO,
    ]);

    $resolucion = resolucionDiariaPara($solicitud, 'RES-DIA-FIRMADA');

    Documento::conPermisoDocumentoSistema(fn () => Documento::factory()->create([
        'resolucion_id' => $resolucion->id,
        'solicitud_id' => null,
        'tipo_documento' => Documento::TIPO_RESOLUCION_FIRMADA,
        'ruta' => 'documentos/resolucion-firmada.pdf',
    ]));

    abrirDetalleSolicitud($solicitud)
        ->assertMountedActionModalSee('RES-DIA-FIRMADA')
        ->assertMountedActionModalSee('Ver PDF firmado');
});
