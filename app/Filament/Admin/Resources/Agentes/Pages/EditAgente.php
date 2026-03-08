<?php

namespace App\Filament\Admin\Resources\Agentes\Pages;

use App\Filament\Admin\Resources\Agentes\AgenteResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAgente extends EditRecord
{
    protected static string $resource = AgenteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
