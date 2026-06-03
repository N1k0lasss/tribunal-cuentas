<?php

namespace App\Filament\Admin\Resources\Agentes\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AgenteInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextEntry::make('nombre'),
                TextEntry::make('apellido'),
                TextEntry::make('dni'),
                TextEntry::make('cargo')
                    ->placeholder('-'),
                TextEntry::make('situacion_revista')
                    ->placeholder('-'),
                TextEntry::make('area_id')
                    ->numeric(),
                TextEntry::make('fecha_ingreso')
                    ->date(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
