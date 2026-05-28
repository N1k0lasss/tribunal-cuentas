<?php

namespace App\Filament\Admin\Resources\Documentos;

use App\Filament\Admin\Resources\Documentos\Pages\CreateDocumento;
use App\Filament\Admin\Resources\Documentos\Pages\EditDocumento;
use App\Filament\Admin\Resources\Documentos\Pages\ListDocumentos;
use App\Models\Documento;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use UnitEnum;
use BackedEnum;
use Illuminate\Contracts\Support\Htmlable;

class DocumentoResource extends Resource
{
    protected static ?string $model = Documento::class;

    protected static ?string $navigationLabel = 'Documentos';

    protected static UnitEnum|string|null $navigationGroup = 'Flujo de licencias';

    protected static ?int $navigationSort = 4;

    public static function getNavigationIcon(): string | BackedEnum | Htmlable | null
    {
        return 'heroicon-o-paper-clip';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('nombre_archivo')
                    ->label('Nombre del Archivo')
                    ->required()
                    ->maxLength(255),
                FileUpload::make('ruta')
                    ->label('Archivo')
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'])
                    ->directory('documentos')
                    ->maxSize(10240) // 10MB
                    ->downloadable()
                    ->openable()
                    ->required(),
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
                Hidden::make('agente_id')
                    ->default(auth()->user()->agente?->id),
                Placeholder::make('agente_nombre')
                    ->label('Agente')
                    ->content(auth()->user()->agente ? auth()->user()->agente->nombre . ' ' . auth()->user()->agente->apellido : 'No asignado'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('ruta')
                    ->label('Miniatura')
                    ->disk('public')
                    ->circular()
                    ->toggleable(),
                TextColumn::make('nombre_archivo')
                    ->label('Archivo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tipo_documento')
                    ->label('Tipo')
                    ->sortable(),
                TextColumn::make('agente.apellido')
                    ->label('Agente')
                    ->formatStateUsing(fn ($record) => $record->agente ? "{$record->agente->apellido}, {$record->agente->nombre}" : '-')
                    ->sortable(),
                TextColumn::make('solicitud_id')
                    ->label('Solicitud #')
                    ->sortable(),
                TextColumn::make('solicitud.fecha_solicitud')
                    ->label('Fecha de Solicitud')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('solicitud.tipoSolicitud.nombre')
                    ->label('Tipo de Licencia')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
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
