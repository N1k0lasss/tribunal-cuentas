<?php

namespace App\Filament\Admin\Resources\Feriados;

// use App\Filament\Admin\Resources\Feriados\Pages\CreateFeriados;
// use App\Filament\Admin\Resources\Feriados\Pages\EditFeriados;
use App\Filament\Admin\Resources\Feriados\Pages\ListFeriados;
use App\Filament\Admin\Resources\Feriados\Schemas\FeriadosForm;
// use App\Filament\Admin\Resources\Feriados\Tables\FeriadosTable;
use App\Models\Feriados;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Log;

// use Filament\Support\Icons\Heroicon;

class FeriadosResource extends Resource
{
    protected static ?string $model = Feriados::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    // protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $navigationLabel = 'Calendario';

    protected static ?string $slug = 'gestion-calendario';

    protected static string|\UnitEnum|null $navigationGroup = 'Configuración';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return FeriadosForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fecha_inicio')
                    ->label('Horarios')
                    ->dateTime('d/M/Y - H:i')
                    ->weight('bold')
                    ->color('primary')
                    ->icon('heroicon-m-calendar-days')
                    ->description(fn ($record): string => 'Fin: '.$record->fecha_fin->format('d/M/Y - H:i')),
                TextColumn::make('nombre')
                    ->label('Nombre del Feriado')
                    ->size('lg')
                    ->weight('bold')
                    ->icon(fn ($record) => $record->recurrente ? 'heroicon-m-arrow-path' : null) // Aparece un icono si el feriado es recurrente
                    ->iconPosition('after')
                    ->searchable(),
                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->color(
                        fn ($record) => match ($record->tipo) {
                            'Nacional' => 'info',
                            'Provincial' => 'warning',
                            'Local' => 'success',
                            default => 'primary',
                        }
                    )
                    ->icon(fn ($record) => match ($record->tipo) {
                        'Nacional' => 'heroicon-m-flag',
                        'Regional' => 'heroicon-m-map',
                        'Local' => 'heroicon-m-map-pin',
                        default => 'heroicon-m-flag',
                    }),
                TextColumn::make('trasladable')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Inamovible' : 'Trasladable')
                    ->color(fn (bool $state): string => $state ? 'gray' : 'warning')
                    // icono de si es trasladable o no
                    ->icon(fn (bool $state): string => $state ? 'heroicon-m-lock-closed' : 'heroicon-m-arrows-right-left'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Nuevo Feriado')
                    ->icon('heroicon-o-plus')
                    ->button()
                    ->after(function ($livewire, $record = null) {
                        // Refrescar calendario
                        $livewire->dispatch('calendar--refresh');
                        Log::info('FeriadosResource: dispatched calendar--refresh (CreateAction)');

                        // Si existe el registro creado, navegar el calendario a su fecha de inicio
                        if ($record && isset($record->fecha_inicio)) {
                            $date = $record->fecha_inicio->format('Y-m-d');
                            $livewire->js("window.dispatchEvent(new CustomEvent('filament-fullcalendar--goto', { detail: { date: '$date' } }))");
                            Log::info("FeriadosResource: requested calendar goto {$date} (CreateAction)");
                        }
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->iconButton()
                    ->after(function ($livewire, $record = null) {
                        // Emitir refresh y navegar a la fecha del registro editado
                        $livewire->dispatch('calendar--refresh');
                        if ($record && isset($record->fecha_inicio)) {
                            $date = $record->fecha_inicio->format('Y-m-d');
                            $livewire->js("window.dispatchEvent(new CustomEvent('filament-fullcalendar--goto', { detail: { date: '$date' } }))");
                            Log::info("FeriadosResource: requested calendar goto {$date} (EditAction)");
                        }
                        Log::info('FeriadosResource: dispatched calendar--refresh (EditAction)');
                    }),
                DeleteAction::make()
                    ->iconButton()
                    ->after(function ($livewire, $record = null) {
                        // Refrescar calendario
                        $livewire->dispatch('calendar--refresh');
                        Log::info('FeriadosResource: dispatched calendar--refresh (DeleteAction)');

                        // Si se eliminó un registro, navegar al inicio del registro eliminado (si tiene fecha)
                        if ($record && isset($record->fecha_inicio)) {
                            $date = $record->fecha_inicio->format('Y-m-d');
                            $livewire->js("window.dispatchEvent(new CustomEvent('filament-fullcalendar--goto', { detail: { date: '$date' } }))");
                            Log::info("FeriadosResource: requested calendar goto {$date} (DeleteAction)");
                        }
                    }),
            ])
            ->actionsColumnLabel('Acciones');
        // ->paginated(false);
        // FeriadosTable::configure($table);
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
            'index' => ListFeriados::route('/'),
            // 'create' => CreateFeriados::route('/create'),
            // 'edit' => EditFeriados::route('/{record}/edit'),
        ];
    }
}
