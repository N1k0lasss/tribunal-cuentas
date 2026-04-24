<?php

namespace App\Filament\Admin\Resources\Resolucions;

use App\Filament\Admin\Resources\Resolucions\Pages\CreateResolucion;
use App\Filament\Admin\Resources\Resolucions\Pages\EditResolucion;
use App\Filament\Admin\Resources\Resolucions\Pages\ListResolucions;
use App\Models\Resolucion;
use App\Models\Solicitud;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ResolucionResource extends Resource
{
    protected static ?string $model = Resolucion::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-check';

    protected static ?string $navigationLabel = 'Resoluciones';

    protected static string|\UnitEnum|null $navigationGroup = 'Licencias';

    protected static ?string $pluralModelLabel = 'Resoluciones';

    protected static ?string $modelLabel = 'Resolución';

    public static function canAccess(): bool
    {
        return ! Auth::user()?->hasRole('agente');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos de la Resolución')
                    ->description('Cargá la identificación legal y su referencia GDE.')
                    ->icon('heroicon-o-document-check')
                    ->columns(2)
                    ->schema([
                        TextInput::make('numero')
                            ->label('Número')
                            ->placeholder('Ej: 2026-001565')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),
                        DatePicker::make('fecha_emision')
                            ->label('Fecha de Emisión')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->required(),
                        TextInput::make('gde_expediente')
                            ->label('GDE Expediente')
                            ->placeholder('EX-2026-00012345-GDESDE-TCSE')
                            ->helperText('Formato sugerido: EX-AAAA-NNNNNNNN-ORIGEN-ORGANISMO')
                            ->required()
                            ->maxLength(80),
                        TextInput::make('gde_documento')
                            ->label('GDE Documento')
                            ->placeholder('IF-2026-00054321-GDESDE-TCSE')
                            ->helperText('Formato sugerido: IF-AAAA-NNNNNNNN-ORIGEN-ORGANISMO')
                            ->required()
                            ->maxLength(80),
                        Select::make('solicitud_id')
                            ->label('Solicitud')
                            ->relationship('solicitud', 'id')
                            ->getOptionLabelFromRecordUsing(
                                fn (Solicitud $record) => '#'.$record->id.' - '.($record->agente?->apellido ?? 'Sin agente').', '.($record->agente?->nombre ?? '')
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpanFull(),
                        Textarea::make('detalle')
                            ->label('Detalle')
                            ->placeholder('Observaciones y fundamentos de la resolución...')
                            ->rows(4)
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ]),
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
                TextColumn::make('gde_expediente')
                    ->label('GDE Expediente')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('gde_documento')
                    ->label('GDE Documento')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('detalle')
                    ->limit(50),
                TextColumn::make('solicitud_id')
                    ->label('Solicitud #')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
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
            'index' => ListResolucions::route('/'),
            'create' => CreateResolucion::route('/create'),
            'edit' => EditResolucion::route('/{record}/edit'),
        ];
    }
}
