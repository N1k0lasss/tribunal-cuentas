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
use Filament\Schemas\Schema;
use Filament\Infolists\Infolist;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Support\Htmlable;

class AgenteResource extends Resource
{
    protected static ?string $model = Agente::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $recordTitleAttribute = 'apellido';

    public static function canAccess(): bool
    {
        return ! Auth::user()?->hasRole('agente');
    }

    public static function form(Form $form): Form
    {
        return AgenteForm::configure($form);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return AgenteInfolist::configure($infolist);
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
