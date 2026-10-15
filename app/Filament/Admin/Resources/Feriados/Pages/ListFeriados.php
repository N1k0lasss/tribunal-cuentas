<?php

namespace App\Filament\Admin\Resources\Feriados\Pages;

use App\Filament\Admin\Resources\Feriados\FeriadosResource;
//use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFeriados extends ListRecords
{
    protected static string $resource = FeriadosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //CreateAction::make(),
        ];
    }
    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Admin\Widgets\CalendarWidget::class,
            \App\Filament\Admin\Widgets\FeriadosListWidget::class,
        ];
    }
    public function getHeaderWidgetsColumns(): int | array
    {
        return 3;
    }

}
