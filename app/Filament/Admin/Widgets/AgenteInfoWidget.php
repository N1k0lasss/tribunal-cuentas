<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class AgenteInfoWidget extends Widget
{
    protected static ?int $sort = -2;

    protected string $view = 'filament.admin.widgets.agente-info-widget';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Auth::user()?->hasRole('agente') ?? false;
    }

    public function getAgenteProperty()
    {
        return Auth::user()?->agente;
    }
}
