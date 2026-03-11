<?php

namespace App\Filament\Resources\Saldos;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\Saldos\Pages\ListSaldos;
use App\Filament\Resources\Saldos\Pages\CreateSaldo;
use App\Filament\Resources\Saldos\Pages\EditSaldo;
use App\Filament\Resources\SaldoResource\Pages;
use App\Models\Saldo;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SaldoResource extends Resource
{
    protected static ?string $model = Saldo::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-calculator';

    protected static ?string $navigationLabel = 'Saldos';

    protected static string | \UnitEnum | null $navigationGroup = 'Gestión de Personal';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('agente_id')
                    ->label('Agente')
                    ->relationship('agente', 'apellido')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->apellido}, {$record->nombre}")
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('anio')
                    ->label('Año')
                    ->numeric()
                    ->required(),
                TextInput::make('tipo_saldo')
                    ->label('Tipo de Saldo')
                    ->maxLength(255)
                    ->required(),
                TextInput::make('dias_otorgados')
                    ->label('Días Otorgados')
                    ->numeric()
                    ->required(),
                TextInput::make('dias_usados')
                    ->label('Días Usados')
                    ->numeric()
                    ->default(0)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('agente.apellido')
                    ->label('Agente')
                    ->formatStateUsing(fn ($record) => "{$record->agente->apellido}, {$record->agente->nombre}")
                    ->searchable()
                    ->sortable(),
                TextColumn::make('anio')
                    ->label('Año')
                    ->sortable(),
                TextColumn::make('tipo_saldo')
                    ->label('Tipo')
                    ->sortable(),
                TextColumn::make('dias_otorgados')
                    ->label('Otorgados')
                    ->sortable(),
                TextColumn::make('dias_usados')
                    ->label('Usados')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
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
            'index' => ListSaldos::route('/'),
            'create' => CreateSaldo::route('/create'),
            'edit' => EditSaldo::route('/{record}/edit'),
        ];
    }
}
