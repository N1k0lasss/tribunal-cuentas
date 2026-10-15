<?php

namespace App\Filament\Admin\Resources\Saldos\Pages;

use App\Filament\Admin\Resources\Saldos\SaldoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSaldos extends ListRecords
{
    protected static string $resource = SaldoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
