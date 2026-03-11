<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ResolucionResource\Pages;
use App\Models\Resolucion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ResolucionResource extends Resource
{
    protected static ?string $model = Resolucion::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';

    protected static ?string $navigationLabel = 'Resoluciones';

    protected static ?string $navigationGroup = 'Licencias';

    protected static ?string $pluralModelLabel = 'Resoluciones';

    protected static ?string $modelLabel = 'Resolución';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('numero')
                    ->label('Número')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('fecha_emision')
                    ->label('Fecha de Emisión')
                    ->required(),
                Forms\Components\Textarea::make('detalle')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\Select::make('solicitud_id')
                    ->label('Solicitud')
                    ->relationship('solicitud', 'id')
                    ->searchable()
                    ->preload()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero')
                    ->label('Número')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_emision')
                    ->label('Fecha Emisión')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('detalle')
                    ->limit(50),
                Tables\Columns\TextColumn::make('solicitud_id')
                    ->label('Solicitud #')
                    ->sortable(),
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
            'index' => Pages\ListResolucions::route('/'),
            'create' => Pages\CreateResolucion::route('/create'),
            'edit' => Pages\EditResolucion::route('/{record}/edit'),
        ];
    }
}
