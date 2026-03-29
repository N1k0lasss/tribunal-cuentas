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

    public function config(): array
    {
        return [
            'displayEventTime' => false,
        ];
    }


    public function fetchEvents(array $fetchInfo): array
{   
    $start = \Illuminate\Support\Carbon::parse($fetchInfo['start']);
    $end = \Illuminate\Support\Carbon::parse($fetchInfo['end']);

    return Feriados::query() // Iniciamos la consulta
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
                'allDay' => (bool) $feriado->todo_el_dia,
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