<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class LicenciasLeyendaWidget extends Widget
{
    protected string $view = 'filament.widgets.licencias-leyenda-widget';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 12;

    public static function canView(): bool
    {
        return Auth::user()?->hasAnyRole(['administrador', 'jefatura', 'secretaria_general', 'presidencia']) ?? false;
    }
}
