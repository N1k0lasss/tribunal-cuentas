<?php

namespace App\Filament\Resources\AprobacionResource\Pages;

use App\Filament\Resources\AprobacionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAprobacions extends ListRecords
{
    protected static string $resource = AprobacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
