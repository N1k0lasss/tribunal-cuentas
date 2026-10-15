<?php

namespace App\Filament\Admin\Resources\Feriados\Pages;

use App\Filament\Admin\Resources\Feriados\FeriadosResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFeriados extends EditRecord
{
    protected static string $resource = FeriadosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
