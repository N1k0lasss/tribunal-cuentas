<?php

namespace App\Filament\Admin\Resources\Feriados\Pages;

use App\Filament\Admin\Resources\Feriados\FeriadosResource;
// use Filament\Actions\CreateAction;
use App\Filament\Admin\Widgets\CalendarWidget;
use Filament\Resources\Pages\ListRecords;

class ListFeriados extends ListRecords
{
    protected static string $resource = FeriadosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CalendarWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }
}
