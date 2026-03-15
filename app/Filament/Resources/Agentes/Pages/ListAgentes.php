<?php

namespace App\Filament\Resources\Agentes\Pages;

use App\Filament\Resources\Agentes\AgenteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAgentes extends ListRecords
{
    protected static string $resource = AgenteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
