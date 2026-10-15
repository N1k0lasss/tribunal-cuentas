<?php

namespace App\Filament\Admin\Resources\Aprobacions\Pages;

use App\Filament\Admin\Resources\Aprobacions\AprobacionResource;
use Filament\Actions\DeleteAction;
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
