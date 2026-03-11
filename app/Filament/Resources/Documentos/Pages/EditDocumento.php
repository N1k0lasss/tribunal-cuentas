<?php

namespace App\Filament\Resources\Documentos\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\Documentos\DocumentoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDocumento extends EditRecord
{
    protected static string $resource = DocumentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
