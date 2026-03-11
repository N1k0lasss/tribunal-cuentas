<?php

namespace App\Filament\Resources\Areas\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\Areas\AreaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditArea extends EditRecord
{
    protected static string $resource = AreaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
