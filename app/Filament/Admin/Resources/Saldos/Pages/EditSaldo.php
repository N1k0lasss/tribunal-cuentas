<?php

namespace App\Filament\Admin\Resources\Saldos\Pages;

use App\Filament\Admin\Resources\Saldos\SaldoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSaldo extends EditRecord
{
    protected static string $resource = SaldoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
