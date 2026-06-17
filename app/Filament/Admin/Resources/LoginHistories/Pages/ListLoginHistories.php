<?php

namespace App\Filament\Admin\Resources\LoginHistories\Pages;

use App\Filament\Admin\Resources\LoginHistories\LoginHistoryResource;
use Filament\Resources\Pages\ListRecords;

class ListLoginHistories extends ListRecords
{
    protected static string $resource = LoginHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
