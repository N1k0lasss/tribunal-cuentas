<?php

namespace App\Filament\Admin\Resources\Agentes;

use App\Filament\Admin\Resources\Agentes\Pages\CreateAgente;
use App\Filament\Admin\Resources\Agentes\Pages\EditAgente;
use App\Filament\Admin\Resources\Agentes\Pages\ListAgentes;
use App\Filament\Admin\Resources\Agentes\Pages\ViewAgente;
use App\Filament\Admin\Resources\Agentes\Schemas\AgenteForm;
use App\Filament\Admin\Resources\Agentes\Schemas\AgenteInfolist;
use App\Filament\Admin\Resources\Agentes\Tables\AgentesTable;
use App\Models\Agente;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AgenteResource extends Resource
{
    protected static ?string $model = Agente::class;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'apellido';

    public static function form(Schema $schema): Schema
    {
        return AgenteForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AgenteInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AgentesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAgentes::route('/'),
            'create' => CreateAgente::route('/create'),
            'view' => ViewAgente::route('/{record}'),
            'edit' => EditAgente::route('/{record}/edit'),
        ];
    }
}
