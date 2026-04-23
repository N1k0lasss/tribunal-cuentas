<?php

use App\Models\TipoSolicitud;
use App\Models\User;

it('prevents agente from creating, updating and deleting tipo solicitud', function () {
    /** @var User $agente */
    $agente = loginAsRole('agente');

    $tipoSolicitud = TipoSolicitud::factory()->create();

    expect($agente->can('create', TipoSolicitud::class))->toBeFalse();
    expect($agente->can('update', $tipoSolicitud))->toBeFalse();
    expect($agente->can('delete', $tipoSolicitud))->toBeFalse();
});
