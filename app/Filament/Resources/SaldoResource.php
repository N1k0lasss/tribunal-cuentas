<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaldoResource\Pages;
use App\Models\Saldo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SaldoResource extends Resource
{
    protected static ?string $model = Saldo::class;

    protected static ?string $navigationIcon = 'heroicon-o-calculator';

    protected static ?string $navigationLabel = 'Saldos';

    protected static ?string $navigationGroup = 'Gestión de Personal';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('agente_id')
                    ->label('Agente')
                    ->relationship('agente', 'apellido')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->apellido}, {$record->nombre}")
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('anio')
                    ->label('Año')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('tipo_saldo')
                    ->label('Tipo de Saldo')
                    ->maxLength(255)
                    ->required(),
                Forms\Components\TextInput::make('dias_otorgados')
                    ->label('Días Otorgados')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('dias_usados')
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
                Tables\Columns\TextColumn::make('agente.apellido')
                    ->label('Agente')
                    ->formatStateUsing(fn ($record) => "{$record->agente->apellido}, {$record->agente->nombre}")
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('anio')
                    ->label('Año')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipo_saldo')
                    ->label('Tipo')
                    ->sortable(),
                Tables\Columns\TextColumn::make('dias_otorgados')
                    ->label('Otorgados')
                    ->sortable(),
                Tables\Columns\TextColumn::make('dias_usados')
                    ->label('Usados')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListSaldos::route('/'),
            'create' => Pages\CreateSaldo::route('/create'),
            'edit' => Pages\EditSaldo::route('/{record}/edit'),
        ];
    }
}
