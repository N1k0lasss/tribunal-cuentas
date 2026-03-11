<?php

namespace App\Filament\Resources\ResolucionResource\Pages;

use App\Filament\Resources\ResolucionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditResolucion extends EditRecord
{
    protected static string $resource = ResolucionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
