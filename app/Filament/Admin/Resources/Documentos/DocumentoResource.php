<?php

namespace App\Filament\Admin\Resources\Documentos;

use App\Filament\Admin\Resources\Documentos\Pages\CreateDocumento;
use App\Filament\Admin\Resources\Documentos\Pages\EditDocumento;
use App\Filament\Admin\Resources\Documentos\Pages\ListDocumentos;
use App\Models\Documento;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DocumentoResource extends Resource
{
    protected static ?string $model = Documento::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-paper-clip';

    protected static ?string $navigationLabel = 'Documentos';

    protected static string|\UnitEnum|null $navigationGroup = 'Licencias';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nombre_archivo')
                    ->label('Nombre del Archivo')
                    ->required()
                    ->maxLength(255),
                TextInput::make('ruta')
                    ->label('Ruta')
                    ->maxLength(255),
                TextInput::make('tipo_documento')
                    ->label('Tipo de Documento')
                    ->maxLength(255),
                Textarea::make('descripcion')
                    ->label('Descripción')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Select::make('solicitud_id')
                    ->label('Solicitud')
                    ->relationship('solicitud', 'id')
                    ->searchable()
                    ->preload(),
                Select::make('resolucion_id')
                    ->label('Resolución')
                    ->relationship('resolucion', 'numero')
                    ->searchable()
                    ->preload(),
                Select::make('agente_id')
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
                TextColumn::make('nombre_archivo')
                    ->label('Archivo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tipo_documento')
                    ->label('Tipo')
                    ->sortable(),
                TextColumn::make('agente.apellido')
                    ->label('Agente')
                    ->sortable(),
                TextColumn::make('solicitud_id')
                    ->label('Solicitud #')
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
            'index' => ListDocumentos::route('/'),
            'create' => CreateDocumento::route('/create'),
            'edit' => EditDocumento::route('/{record}/edit'),
        ];
    }
}
