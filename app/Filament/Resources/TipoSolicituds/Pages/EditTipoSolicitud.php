<?php

namespace App\Filament\Resources\TipoSolicituds\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\TipoSolicituds\TipoSolicitudResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTipoSolicitud extends EditRecord
{
    protected static string $resource = TipoSolicitudResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
