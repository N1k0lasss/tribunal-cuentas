<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Feriados;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class ProximaFechaWidget extends Widget
{
    protected static ?int $sort = 1;

    protected string $view = 'filament.admin.widgets.proxima-fecha-widget';

    protected int | string | array $columnSpan = 'full';

    public function getNextHoliday()
    {
        $today = now()->startOfDay();

        $nonRecurrent = Feriados::where('recurrente', false)
            ->whereDate('fecha_inicio', '>=', $today)
            ->orderBy('fecha_inicio')
            ->first();

        $recurrent = Feriados::where('recurrente', true)->get()
            ->map(function (Feriados $f) use ($today) {
                $fechaProxima = $f->fecha_inicio->copy()->year($today->year);
                if ($fechaProxima->isBefore($today)) {
                    $fechaProxima->addYear();
                }
                $f->computed_proxima_fecha = $fechaProxima;
                return $f;
            })
            ->filter(fn ($f) => $f->computed_proxima_fecha->gte($today))
            ->sortBy('computed_proxima_fecha')
            ->first();

        if ($nonRecurrent && $recurrent) {
            return $nonRecurrent->fecha_inicio->lte($recurrent->computed_proxima_fecha)
                ? $nonRecurrent
                : $recurrent;
        }

        return $nonRecurrent ?? $recurrent;
    }

    public static function canView(): bool
    {
        return Auth::user()?->hasRole('agente') ?? false;
    }
}
