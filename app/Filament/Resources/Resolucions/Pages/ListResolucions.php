<?php

namespace App\Filament\Resources\Resolucions\Pages;

use App\Filament\Resources\Resolucions\ResolucionResource;
use Filament\Actions\CreateAction;
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
