<?php

namespace App\Filament\Resources\Areas\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Areas\AreaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAreas extends ListRecords
{
    protected static string $resource = AreaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
