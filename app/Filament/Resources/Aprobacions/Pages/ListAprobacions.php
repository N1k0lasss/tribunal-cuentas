<?php

namespace App\Filament\Resources\Aprobacions\Pages;

use App\Filament\Resources\Aprobacions\AprobacionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAprobacions extends ListRecords
{
    protected static string $resource = AprobacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
