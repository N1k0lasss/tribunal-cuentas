<?php

namespace App\Filament\Resources\AprobacionResource\Pages;

use App\Filament\Resources\AprobacionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAprobacion extends EditRecord
{
    protected static string $resource = AprobacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
