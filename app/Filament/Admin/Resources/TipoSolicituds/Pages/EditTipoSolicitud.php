<?php

namespace App\Filament\Admin\Resources\TipoSolicituds\Pages;

use App\Filament\Admin\Resources\TipoSolicituds\TipoSolicitudResource;
use Filament\Actions\DeleteAction;
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
