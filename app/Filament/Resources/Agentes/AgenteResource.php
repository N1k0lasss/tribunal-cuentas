<?php

namespace App\Filament\Resources\Agentes;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\Agentes\Pages\ListAgentes;
use App\Filament\Resources\Agentes\Pages\CreateAgente;
use App\Filament\Resources\Agentes\Pages\EditAgente;
use App\Filament\Resources\AgenteResource\Pages;
use App\Models\Agente;
use App\Models\Area;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AgenteResource extends Resource
{
    protected static ?string $model = Agente::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Agentes';

    protected static string | \UnitEnum | null $navigationGroup = 'Gestión de Personal';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nombre')
                    ->required()
                    ->maxLength(255),
                TextInput::make('apellido')
                    ->required()
                    ->maxLength(255),
                TextInput::make('dni')
                    ->required()
                    ->maxLength(20),
                TextInput::make('cargo')
                    ->maxLength(255),
                TextInput::make('tipo_contrato')
                    ->maxLength(255),
                Select::make('area_id')
                    ->label('Área')
                    ->relationship('area', 'nombre')
                    ->searchable()
                    ->preload()
                    ->required(),
                DatePicker::make('fecha_ingreso')
                    ->label('Fecha de Ingreso'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('apellido')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('dni')
                    ->searchable(),
                TextColumn::make('cargo')
                    ->sortable(),
                TextColumn::make('tipo_contrato')
                    ->label('Tipo Contrato'),
                TextColumn::make('area.nombre')
                    ->label('Área')
                    ->sortable(),
                TextColumn::make('fecha_ingreso')
                    ->label('Fecha Ingreso')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
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
            'edit' => EditAgente::route('/{record}/edit'),
        ];
    }
}
