<?php

namespace App\Filament\Resources\Resolucions\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Resolucions\ResolucionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListResolucions extends ListRecords
{
    protected static string $resource = ResolucionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
