<?php

namespace App\Filament\Resources\Solicituds\Pages;

use App\Filament\Resources\Solicituds\SolicitudResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSolicitud extends EditRecord
{
    protected static string $resource = SolicitudResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
