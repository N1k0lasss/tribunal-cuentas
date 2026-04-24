<?php

namespace App\Filament\Admin\Resources\Agentes\Schemas;

use App\Models\Agente;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AgenteInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('nombre'),
                TextEntry::make('apellido'),
                TextEntry::make('dni'),
                TextEntry::make('cargo')
                    ->placeholder('-'),
                TextEntry::make('tipo_contrato')
                    ->label('Tipo de contrato')
                    ->formatStateUsing(fn ($state) => Agente::TIPO_CONTRATO_LABELS[$state] ?? $state)
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
