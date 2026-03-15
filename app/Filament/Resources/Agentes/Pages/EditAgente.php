<?php

namespace App\Filament\Resources\Agentes\Pages;

use App\Filament\Resources\Agentes\AgenteResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAgente extends EditRecord
{
    protected static string $resource = AgenteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
