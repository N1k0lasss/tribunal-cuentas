<?php

namespace App\Filament\Resources\Aprobacions\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Aprobacions\AprobacionResource;
use Filament\Actions;
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
