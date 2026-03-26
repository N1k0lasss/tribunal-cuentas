<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class PerfilWidget extends Widget
{
    protected static ?int $sort = 1;

    protected static string $view = 'filament.widgets.perfil-widget';
    
    // Cambiamos el valor por defecto a string o sacamos la definición de tipo 
    // para ver si eso destraba el descubrimiento de paquetes.
    protected int | string | array $columnSpan = 'full'; 
}