<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Feriados;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use Saade\FilamentFullCalendar\Actions\ViewAction;
//use Saade\FilamentFullCalendar\Actions\EditAction;
//use Saade\FilamentFullCalendar\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Model;
use Filament\Schemas\Components\Grid;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;


class CalendarWidget extends FullCalendarWidget
{   
    #[On('calendar--refresh')]
    public function refreshCalendar(): void
    {
        // Usar el helper del paquete para forzar la recarga de registros
        Log::info('CalendarWidget: recibido evento calendar--refresh, refrescando registros');
        $this->refreshRecords();
    }
    // Indicamos el modelo con el que trabaja el calendario
    public Model|string|null $model = Feriados::class;

    protected int | string | array $columnSpan = 2;

    public function config(): array
    {
        return [
            'displayEventTime' => false,
            'headerToolbar' => [
                'left' => 'prev,next today',
                'center' => 'title',
                'right' => 'dayGridMonth,timeGridWeek,timeGridDay'
            ],
        ];
    }


    public function fetchEvents(array $fetchInfo): array
{   
    $start = \Illuminate\Support\Carbon::parse($fetchInfo['start']);
    $end = \Illuminate\Support\Carbon::parse($fetchInfo['end']);

    $feriados = Feriados::query() // Iniciamos la consulta
        ->where(function ($query) use ($start, $end) {
            // Buscamos los que están en este mes O los que son recurrentes
            $query->whereBetween('fecha_inicio', [$start, $end])
                  ->orWhere('recurrente', true);
        })
        ->get() 
        ->map(function (Feriados $feriado) use ($start) {
            
            $inicio = $feriado->fecha_inicio;
            $fin = $feriado->fecha_fin;
            // esto es para que cuando el feriado es recurrente
            if ($feriado->recurrente) {
                $inicio = $feriado->fecha_inicio->copy()->year($start->year);
                $fin = $feriado->fecha_fin->copy()->year($start->year);
            }

            return [
                'id' =>  $feriado->id,
                'title' => $feriado->nombre,
                'start' => $inicio,
                'end' => $fin,
                // Agregamos campos extra para el widget de lista
                'nombre' => $feriado->nombre,
                'tipo' => $feriado->tipo,
                'fecha_inicio' => $inicio,
                'recurrente' => $feriado->recurrente,
                
                'allDay' => (bool) $feriado->todo_el_dia,
                'display' => 'block',
                'backgroundColor' => match ($feriado->tipo) {
                    'Nacional' => '#3b82f6',
                    'Provincial' => '#f59e0b',
                    'Local' => '#10b981',
                    default => '#6366f1',
                },
                'borderColor' => 'transparent',
                'classNames' => ['cursor-pointer', 'hover:opacity-80'],   
            ];
        })
        ->toArray();

    // Emitir evento para sincronizar el widget de lista (usamos el centro de la vista para asegurar el mes correcto)
    $mesVisible = $start->copy()->addDays(15);
    // Extraemos solo los feriados que caen exactamente en el mes visible para la barra lateral
    $feriadosDelMes = array_filter($feriados, function($f) use ($mesVisible) {
        $fecha = \Illuminate\Support\Carbon::parse($f['fecha_inicio']);
        return $fecha->month === $mesVisible->month && $fecha->year === $mesVisible->year;
    });

    $this->dispatch('calendar-state-update', month: $mesVisible->month, year: $mesVisible->year, eventos: array_values($feriadosDelMes));

    return $feriados;
}


    
    protected function headerActions(): array
    {
        return [
            // "Crear Feriado"
        ];
    }

    protected function modalActions(): array
    {
        return [     
            //
        ];
    }


    public function viewAction(): ViewAction
    {
        return ViewAction::make()
            ->label('Detalles del Feriado')
            ->modalWidth('md')
            ->schema([
                \Filament\Infolists\Components\TextEntry::make('nombre')
                    ->label('Nombre del Feriado')
                    ->weight('bold'),
                
                Grid::make(2)
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('fecha_inicio')
                            ->label('Inicio')
                            ->dateTime('d/m/Y H:i'),
                        \Filament\Infolists\Components\TextEntry::make('fecha_fin')
                            ->label('Fin')
                            ->dateTime('d/m/Y H:i'),
                    ]),

                \Filament\Infolists\Components\TextEntry::make('tipo')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Nacional' => 'info',
                        'Provincial' => 'warning',
                        'Local' => 'success',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'Nacional' => 'heroicon-m-flag',
                        'Provincial' => 'heroicon-m-map',
                        'Local' => 'heroicon-m-map-pin',
                        default => 'heroicon-m-question-mark-circle',
                    }),

                \Filament\Infolists\Components\IconEntry::make('recurrente')
                    ->boolean(),

                \Filament\Infolists\Components\TextEntry::make('trasladable')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state ? 'Inamovible' : 'Trasladable')
                    ->color(fn ($state): string => $state ? 'gray' : 'warning')
                    ->icon(fn ($state): string => $state ? 'heroicon-m-lock-closed' : 'heroicon-m-arrows-right-left'),
            ]);
    }


}