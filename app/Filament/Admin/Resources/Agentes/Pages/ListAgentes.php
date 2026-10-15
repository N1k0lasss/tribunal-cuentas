<?php

namespace App\Filament\Admin\Resources\Agentes\Pages;

use App\Filament\Admin\Resources\Agentes\AgenteResource;
use Filament\Resources\Pages\ListRecords;

class ListAgentes extends ListRecords
{
    protected static string $resource = AgenteResource::class;

    protected ?string $heading = 'Agentes';

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
