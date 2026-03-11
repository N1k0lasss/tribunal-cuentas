<?php

namespace App\Filament\Resources\Agentes\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Agentes\AgenteResource;
use Filament\Actions;
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
