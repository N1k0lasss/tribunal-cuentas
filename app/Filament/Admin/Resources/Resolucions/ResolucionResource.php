<?php

namespace App\Filament\Admin\Resources\Resolucions;

use App\Filament\Admin\Resources\Resolucions\Pages\EditResolucion;
use App\Filament\Admin\Resources\Resolucions\Pages\ListResolucions;
use App\Models\Resolucion;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
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
                    ->maxLength(50),
                DatePicker::make('fecha_emision')
                    ->label('Fecha de Emisión')
                    ->helperText('La fecha de emisión no se puede modificar.')
                    ->required()
                    ->disabled()
                    ->dehydrated(),
                Textarea::make('detalle')
                    ->label('Detalle')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    /**
     * El listado muestra únicamente las resoluciones del día (las que no
     * pertenecen a una única solicitud).
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->diarias()
            ->with('pdfFirmado')
            ->withCount('solicitudes');
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
                TextColumn::make('solicitudes_count')
                    ->label('Licencias')
                    ->sortable(),
                TextColumn::make('firmado')
                    ->label('PDF firmado')
                    ->badge()
                    ->state(fn (Resolucion $record): string => $record->pdfFirmado ? 'Firmada' : 'Pendiente')
                    ->color(fn (string $state): string => $state === 'Firmada' ? 'success' : 'warning'),
            ])
            ->defaultSort('fecha_emision', 'desc')
            ->filters([
                //
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn (Resolucion $record): bool => $record->pdfFirmado === null),
                Action::make('descargar_pdf_resolucion')
                    ->label('Descargar PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (Resolucion $record) => route('resolucion.pdf', $record))
                    ->openUrlInNewTab(),
                Action::make('descargar_pdf_firmado')
                    ->label('Ver PDF firmado')
                    ->icon('heroicon-o-document-check')
                    ->url(fn (Resolucion $record): ?string => $record->pdfFirmado
                        ? route('documento.ver', $record->pdfFirmado)
                        : null)
                    ->openUrlInNewTab()
                    ->visible(fn (Resolucion $record): bool => $record->pdfFirmado !== null),
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
            'edit' => EditResolucion::route('/{record}/edit'),
        ];
    }
}
