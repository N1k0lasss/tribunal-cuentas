<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Feriados;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class ProximaFechaWidget extends Widget
{
    protected string $view = 'filament.admin.widgets.proxima-fecha-widget';

    protected int | string | array $columnSpan = 'full';

    public function getNextHoliday()
    {
        $today = now()->startOfDay();
        
        return Feriados::all()
            ->map(function (Feriados $f) use ($today) {
                $fechaProxima = $f->fecha_inicio->copy();
                
                if ($f->recurrente) {
                    $fechaProxima->year($today->year);
                    
                    if ($fechaProxima->isBefore($today)) {
                        $fechaProxima->addYear();
                    }
                }
                
                $f->computed_proxima_fecha = $fechaProxima;
                return $f;
            })
            ->filter(fn($f) => $f->computed_proxima_fecha->isAfter($today) || $f->computed_proxima_fecha->isSameDay($today))
            ->sortBy('computed_proxima_fecha')
            ->first();
    }

    public static function canView(): bool
    {
        return Auth::user()?->hasRole('agente') ?? false;
    }
}
