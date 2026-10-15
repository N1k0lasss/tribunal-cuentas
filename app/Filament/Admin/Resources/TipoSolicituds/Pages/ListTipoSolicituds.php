<?php

namespace App\Filament\Admin\Resources\TipoSolicituds\Pages;

use App\Filament\Admin\Resources\TipoSolicituds\TipoSolicitudResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTipoSolicituds extends ListRecords
{
    protected static string $resource = TipoSolicitudResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
