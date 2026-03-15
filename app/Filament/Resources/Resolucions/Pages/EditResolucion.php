<?php

namespace App\Filament\Resources\Resolucions\Pages;

use App\Filament\Resources\Resolucions\ResolucionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditResolucion extends EditRecord
{
    protected static string $resource = ResolucionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
