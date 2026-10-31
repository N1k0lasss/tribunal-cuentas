<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\CalendarWidget;
use App\Filament\Admin\Widgets\FeriadosListWidget;
use Filament\Pages\Page;

class FeriadosCalendario extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationLabel = 'Calendario de Feriados';

    protected static ?string $title = 'Calendario de Feriados';

    protected static string|\UnitEnum|null $navigationGroup = 'Calendarios';

    protected static ?int $navigationSort = 21;

    protected string $view = 'filament.admin.pages.feriados-calendario';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['agente', 'administrador', 'jefatura', 'secretaria_general', 'presidencia']) ?? false;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CalendarWidget::class,
            FeriadosListWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return [
            'default' => 1,
            'md' => 2,
            'xl' => 3,
        ];
    }
}
