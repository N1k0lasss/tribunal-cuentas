<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class PerfilWidget extends Widget
{
    protected static ?int $sort = 1;

    protected string $view = 'filament.widgets.perfil-widget';

    protected int | string | array $columnSpan = 'full';
}
