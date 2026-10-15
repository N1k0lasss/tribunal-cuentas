<?php

namespace App\Filament\Admin\Resources\Agentes\Pages;

use App\Filament\Admin\Resources\Agentes\AgenteResource;
use Filament\Resources\Pages\ViewRecord;

class ViewAgente extends ViewRecord
{
    protected static string $resource = AgenteResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
