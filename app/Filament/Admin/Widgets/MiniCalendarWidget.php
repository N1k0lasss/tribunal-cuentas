<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class MiniCalendarWidget extends Widget
{
    protected string $view = 'filament.admin.widgets.mini-calendar-widget';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Auth::user()?->hasRole('agente') ?? false;
    }
}
