<?php

namespace App\Filament\Admin\Resources\Resolucions;

use App\Filament\Admin\Resources\Resolucions\Pages\CreateResolucion;
use App\Filament\Admin\Resources\Resolucions\Pages\EditResolucion;
use App\Filament\Admin\Resources\Resolucions\Pages\ListResolucions;
use App\Models\Documento;
use App\Models\Resolucion;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use UnitEnum;

class ResolucionResource extends Resource
{
    protected static ?string $model = Resolucion::class;

    protected static ?string $navigationLabel = 'Resoluciones';

    protected static UnitEnum|string|null $navigationGroup = 'Flujo de licencias';

    protected static ?int $navigationSort = 5;

    protected static ?string $pluralModelLabel = 'Resoluciones';

    protected static ?string $modelLabel = 'Resolución';

    public static function getNavigationIcon(): string|BackedEnum|Htmlable|null
    {
        return 'heroicon-o-document-check';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('numero')
                    ->label('Número')
                    ->required()
                    ->maxLength(255),
                DatePicker::make('fecha_emision')
                    ->label('Fecha de Emisión')
                    ->required(),
                Textarea::make('detalle')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Select::make('solicitud_id')
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
                TextColumn::make('numero')
                    ->label('Número')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('fecha_emision')
                    ->label('Fecha Emisión')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('detalle')
                    ->label('Detalle')
                    ->limit(50),
                TextColumn::make('solicitud_id')
                    ->label('Solicitud #')
                    ->sortable(),
            ])
            ->groups([
                Group::make('numero')
                    ->label('Número de Resolución')
                    ->collapsible(),
            ])
            ->defaultGroup('numero')
            ->filters([
                //
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('descargar_pdf_resolucion')
                    ->label('Descargar PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (Resolucion $record) => route('solicitud.pdf', [
                        'solicitud' => $record->solicitud_id,
                        'numero' => $record->numero,
                    ]))
                    ->openUrlInNewTab()
                    ->visible(fn (Resolucion $record) => filled($record->solicitud_id)),
                Action::make('subir_pdf_firmado')
                    ->label('Subir PDF firmado')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->authorize('subirPdfFirmado')
                    ->form([
                        FileUpload::make('archivo')
                            ->label('PDF firmado')
                            ->acceptedFileTypes(['application/pdf'])
                            ->disk('local')
                            ->directory('documentos/firmados')
                            ->maxSize(10240)
                            ->required(),
                    ])
                    ->action(function (Resolucion $record, array $data): void {
                        $path = is_array($data['archivo']) ? ($data['archivo'][0] ?? null) : $data['archivo'];

                        if (! $path) {
                            return;
                        }

                        $documentoAnterior = $record->documentos()
                            ->where('tipo_documento', Documento::TIPO_RESOLUCION_FIRMADA)
                            ->first();

                        $record->documentos()->updateOrCreate(
                            ['tipo_documento' => Documento::TIPO_RESOLUCION_FIRMADA],
                            [
                                'solicitud_id' => $record->solicitud_id,
                                'agente_id' => Auth::user()?->agente?->id,
                                'nombre_archivo' => "Resolución {$record->numero} firmada",
                                'ruta' => $path,
                                'descripcion' => 'Resolución firmada digitalmente.',
                            ],
                        );

                        if ($documentoAnterior && $documentoAnterior->ruta !== $path) {
                            Storage::disk('local')->delete($documentoAnterior->ruta);
                        }

                        Notification::make()
                            ->success()
                            ->title('PDF firmado subido')
                            ->body('La resolución firmada se guardó correctamente.')
                            ->send();
                    }),
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
            'index' => ListResolucions::route('/'),
            'create' => CreateResolucion::route('/create'),
            'edit' => EditResolucion::route('/{record}/edit'),
        ];
    }
}
