<?php

namespace App\Filament\Resources\Aprobacions\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\Aprobacions\AprobacionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAprobacion extends EditRecord
{
    protected static string $resource = AprobacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
