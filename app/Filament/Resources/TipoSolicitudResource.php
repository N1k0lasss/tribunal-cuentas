<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TipoSolicitudResource\Pages;
use App\Models\TipoSolicitud;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TipoSolicitudResource extends Resource
{
    protected static ?string $model = TipoSolicitud::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Tipos de Solicitud';

    protected static ?string $navigationGroup = 'Configuración';

    protected static ?string $pluralModelLabel = 'Tipos de Solicitud';

    protected static ?string $modelLabel = 'Tipo de Solicitud';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('categoria')
                    ->label('Categoría')
                    ->maxLength(255),
                Forms\Components\TextInput::make('articulo_legal')
                    ->label('Artículo Legal')
                    ->maxLength(255),
                Forms\Components\TextInput::make('duracion_max_dias')
                    ->label('Duración Máxima (días)')
                    ->numeric(),
                Forms\Components\Toggle::make('requiere_adjunto')
                    ->label('Requiere Adjunto'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('categoria')
                    ->label('Categoría')
                    ->sortable(),
                Tables\Columns\TextColumn::make('articulo_legal')
                    ->label('Artículo Legal'),
                Tables\Columns\TextColumn::make('duracion_max_dias')
                    ->label('Duración Máx. (días)')
                    ->sortable(),
                Tables\Columns\IconColumn::make('requiere_adjunto')
                    ->label('Req. Adjunto')
                    ->boolean(),
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
            'index' => Pages\ListTipoSolicituds::route('/'),
            'create' => Pages\CreateTipoSolicitud::route('/create'),
            'edit' => Pages\EditTipoSolicitud::route('/{record}/edit'),
        ];
    }
}
