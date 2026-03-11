<?php

namespace App\Filament\Resources\TipoSolicituds\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\TipoSolicituds\TipoSolicitudResource;
use Filament\Actions;
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
