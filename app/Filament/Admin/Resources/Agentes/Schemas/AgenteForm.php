<?php

namespace App\Filament\Admin\Resources\Agentes\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AgenteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('nombre')
                    ->required(),
                TextInput::make('apellido')
                    ->required(),
                TextInput::make('dni')
                    ->required(),
                TextInput::make('cargo'),
                TextInput::make('situacion_revista'),
                Select::make('area_id')
                    ->relationship('area', 'nombre')
                    ->searchable()
                    ->preload()
                    ->required(),
                DatePicker::make('fecha_ingreso')
                    ->required(),
            ]);
    }
}
