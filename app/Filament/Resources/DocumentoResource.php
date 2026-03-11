<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentoResource\Pages;
use App\Models\Documento;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DocumentoResource extends Resource
{
    protected static ?string $model = Documento::class;

    protected static ?string $navigationIcon = 'heroicon-o-paper-clip';

    protected static ?string $navigationLabel = 'Documentos';

    protected static ?string $navigationGroup = 'Licencias';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre_archivo')
                    ->label('Nombre del Archivo')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('ruta')
                    ->label('Ruta')
                    ->maxLength(255),
                Forms\Components\TextInput::make('tipo_documento')
                    ->label('Tipo de Documento')
                    ->maxLength(255),
                Forms\Components\Textarea::make('descripcion')
                    ->label('Descripción')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\Select::make('solicitud_id')
                    ->label('Solicitud')
                    ->relationship('solicitud', 'id')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('resolucion_id')
                    ->label('Resolución')
                    ->relationship('resolucion', 'numero')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('agente_id')
                    ->label('Agente')
                    ->relationship('agente', 'apellido')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->apellido}, {$record->nombre}")
                    ->searchable()
                    ->preload(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre_archivo')
                    ->label('Archivo')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipo_documento')
                    ->label('Tipo')
                    ->sortable(),
                Tables\Columns\TextColumn::make('agente.apellido')
                    ->label('Agente')
                    ->sortable(),
                Tables\Columns\TextColumn::make('solicitud_id')
                    ->label('Solicitud #')
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
            'index' => Pages\ListDocumentos::route('/'),
            'create' => Pages\CreateDocumento::route('/create'),
            'edit' => Pages\EditDocumento::route('/{record}/edit'),
        ];
    }
}
