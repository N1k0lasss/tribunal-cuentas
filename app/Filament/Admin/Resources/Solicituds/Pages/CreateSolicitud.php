<?php

namespace App\Filament\Admin\Resources\Solicituds\Pages;

use App\Filament\Admin\Resources\Solicituds\SolicitudResource;
use App\Models\Solicitud;
use App\Services\WorkflowAuditoriaService;
use Filament\Resources\Pages\CreateRecord;

class CreateSolicitud extends CreateRecord
{
    protected static string $resource = SolicitudResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (array_key_exists('es_prorroga', $data)) {
            $data['es_prorroga'] = (bool) $data['es_prorroga'];
        }

        return $data;
    }

    protected function handleRecordCreation(array $data): Solicitud
    {
        $solicitud = Solicitud::create($data);

        app(WorkflowAuditoriaService::class)->registrar(
            solicitud: $solicitud,
            evento: 'solicitud_creada',
            estadoAnterior: null,
            estadoNuevo: $solicitud->estado,
            detalle: 'Solicitud registrada por agente.',
        );

        return $solicitud;
    }
}
