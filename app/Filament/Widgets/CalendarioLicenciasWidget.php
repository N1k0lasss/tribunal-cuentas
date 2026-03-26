<?php

namespace App\Filament\Widgets;

use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class CalendarioLicenciasWidget extends FullCalendarWidget
{
    // Le decimos que se ponga debajo de tu perfil y ocupe todo el ancho
    protected int | string | array $columnSpan = 'full'; 
    protected static ?int $sort = 2;

    public function fetchEvents(array $fetchInfo): array
    {
        // Aquí en el futuro traeremos las licencias reales de la base de datos.
        // Por ahora le mandamos una lista vacía para que el calendario cargue limpio.
        return [];
    }
}