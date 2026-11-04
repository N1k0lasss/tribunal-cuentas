<?php

namespace App\Filament\Admin\Resources\Documentos;

use App\Filament\Admin\Resources\Documentos\Pages\CreateDocumento;
use App\Filament\Admin\Resources\Documentos\Pages\EditDocumento;
use App\Filament\Admin\Resources\Documentos\Pages\ListDocumentos;
use App\Models\Documento;
use App\Models\Solicitud;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use UnitEnum;

class DocumentoResource extends Resource
{
    protected static ?string $model = Documento::class;

    protected static ?string $navigationLabel = 'Documentos';

    protected static UnitEnum|string|null $navigationGroup = 'Flujo de licencias';

    protected static ?int $navigationSort = 4;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getNavigationIcon(): string|BackedEnum|Htmlable|null
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
                    ->maxLength(100),
                FileUpload::make('ruta')
                    ->label('Archivo')
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                    ->disk('local')
                    ->directory('documentos')
                    ->maxSize(10240)
                    ->downloadable()
                    ->openable()
                    ->required(),
                TextInput::make('tipo_documento')
                    ->label('Tipo de Documento')
                    ->required()
                    ->maxLength(50)
                    ->rules([
                        Rule::notIn([
                            Documento::TIPO_RESOLUCION_FIRMADA,
                            Documento::TIPO_SOLICITUD_FIRMADA,
                        ]),
                    ])
                    ->validationMessages([
                        'not_in' => 'Ese tipo de documento se genera automáticamente y no puede crearse manualmente.',
                    ])
                    ->helperText('Los tipos de sistema (resolución firmada y solicitud firmada) se generan automáticamente.'),
                Textarea::make('descripcion')
                    ->label('Descripción')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Select::make('solicitud_id')
                    ->label('Solicitud')
                    ->relationship('solicitud', 'id')
                    ->getOptionLabelFromRecordUsing(fn (Solicitud $record): string => '#'.$record->id
                        .' · '.($record->tipoSolicitud?->nombre ?? 'Sin tipo')
                        .' · '.($record->agente ? "{$record->agente->apellido}, {$record->agente->nombre}" : 'Sin agente')
                        .' · '.$record->fecha_solicitud?->format('d/m/Y'))
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
                    ->url(fn (Documento $record): ?string => $record->archivoExiste()
                        ? route('documento.ver', $record)
                        : null)
                    ->openUrlInNewTab()
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
                EditAction::make()
                    ->visible(fn (Documento $record): bool => ! $record->esSistema()),
                DeleteAction::make()
                    ->visible(fn (Documento $record): bool => ! $record->esSistema())
                    ->authorizeIndividualRecords('delete'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->authorizeIndividualRecords('delete'),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['agente', 'solicitud.tipoSolicitud']);
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
