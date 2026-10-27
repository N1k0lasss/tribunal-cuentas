<?php

namespace App\Filament\Admin\Resources\Aprobacions\Pages;

use App\Filament\Admin\Resources\Aprobacions\AprobacionResource;
use App\Filament\Admin\Resources\Aprobacions\Widgets\ResumenLicenciasWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAprobacions extends ListRecords
{
    protected static string $resource = AprobacionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ResumenLicenciasWidget::class,
        ];
    }
}
