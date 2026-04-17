<?php

namespace App\Filament\Admin\Resources\Feriados\Schemas;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class FeriadosForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('fecha_inicio')
                    ->label('Fecha de Inicio')
                    ->displayFormat('d/m/Y')
                    ->required()
                    ->visible(fn ($get) => $get('todo_el_dia')),

                DateTimePicker::make('fecha_inicio')
                    ->label('Fecha de Inicio')
                    ->displayFormat('d/m/Y H:i')
                    ->required()
                    ->hidden(fn ($get) => $get('todo_el_dia')),

                DatePicker::make('fecha_fin')
                    ->label('Fecha de Finalizacion')
                    ->displayFormat('d/m/Y')
                    ->required()
                    ->visible(fn ($get) => $get('todo_el_dia')),

                DateTimePicker::make('fecha_fin')
                    ->label('Fecha de Finalizacion')
                    ->displayFormat('d/m/Y H:i')
                    ->required()
                    ->hidden(fn ($get) => $get('todo_el_dia')),

                TextInput::make('nombre')
                    ->label('Nombre del Feriado')
                    ->placeholder('Ej: Dia de la Independencia')
                    ->required(),

                Select::make('tipo')
                    ->label('Tipo de Feriado')
                    ->options([
                        'Nacional' => 'Nacional',
                        'Provincial' => 'Provincial',
                        'Local' => 'Local',
                    ])
                    ->required(),

                Checkbox::make('recurrente')
                    ->label('Recurrente')
                    ->default(false),

                Checkbox::make('trasladable')
                    ->label('Inamovible')
                    ->default(false),
                Toggle::make('todo_el_dia')
                    ->label('¿Todo el día?')
                    ->default(true)
                    ->live(),
            ]);
    }
}
