<?php

namespace App\Filament\Admin\Resources\Agentes\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;

class AgenteInfolist
{
    public static function configure(Infolist $schema): Infolist
    {
        return $schema
            ->components([
                TextEntry::make('nombre'),
                TextEntry::make('apellido'),
                TextEntry::make('dni'),
                TextEntry::make('cargo')
                    ->placeholder('-'),
                TextEntry::make('tipo_contrato')
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
