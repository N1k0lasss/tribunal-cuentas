<?php

namespace App\Filament\Resources\TipoSolicitudResource\Pages;

use App\Filament\Resources\TipoSolicitudResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTipoSolicituds extends ListRecords
{
    protected static string $resource = TipoSolicitudResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
