<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\Widget;

class FeriadosListWidget extends Widget
{
    protected string $view = 'filament.widgets.feriados-list-widget';

    protected int | string | array $columnSpan = 1;

    public function getFeriadosProperty()
    {
        return \App\Models\Feriados::orderBy('fecha_inicio', 'asc')->get()->map(function($feriado) {
            return [
                'id' => $feriado->id,
                'nombre' => $feriado->nombre,
                'fecha_inicio' => $feriado->fecha_inicio,
                'fecha_fin' => $feriado->fecha_fin,
                'tipo' => $feriado->tipo,
                'recurrente' => $feriado->recurrente,
            ];
        });
    }
}
