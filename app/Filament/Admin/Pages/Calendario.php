<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;

class Calendario extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected string $view = 'filament.admin.pages.calendario';

    public static function canAccess(): bool
    {
        // Permitir acceso a agentes, administradores, jefatura, secretaria y presidencia
        return auth()->user()?->hasRole(['agente', 'administrador', 'jefatura', 'secretaria_general', 'presidencia']) ?? false;
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
