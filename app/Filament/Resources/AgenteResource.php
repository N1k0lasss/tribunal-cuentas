<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AgenteResource\Pages;
use App\Models\Agente;
use App\Models\Area;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AgenteResource extends Resource
{
    protected static ?string $model = Agente::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Agentes';

    protected static ?string $navigationGroup = 'Gestión de Personal';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('apellido')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('dni')
                    ->required()
                    ->maxLength(20),
                Forms\Components\TextInput::make('cargo')
                    ->maxLength(255),
                Forms\Components\TextInput::make('tipo_contrato')
                    ->maxLength(255),
                Forms\Components\Select::make('area_id')
                    ->label('Área')
                    ->relationship('area', 'nombre')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\DatePicker::make('fecha_ingreso')
                    ->label('Fecha de Ingreso'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('apellido')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('dni')
                    ->searchable(),
                Tables\Columns\TextColumn::make('cargo')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipo_contrato')
                    ->label('Tipo Contrato'),
                Tables\Columns\TextColumn::make('area.nombre')
                    ->label('Área')
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_ingreso')
                    ->label('Fecha Ingreso')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListAgentes::route('/'),
            'create' => Pages\CreateAgente::route('/create'),
            'edit' => Pages\EditAgente::route('/{record}/edit'),
        ];
    }
}
