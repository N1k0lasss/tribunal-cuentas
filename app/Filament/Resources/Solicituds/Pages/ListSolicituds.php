<?php

namespace App\Filament\Resources\Solicituds\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Solicituds\SolicitudResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSolicituds extends ListRecords
{
    protected static string $resource = SolicitudResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
