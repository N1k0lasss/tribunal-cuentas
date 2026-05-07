<?php

namespace App\Filament\Admin\Resources\Aprobacions;

use App\Filament\Admin\Resources\Aprobacions\Pages\CreateAprobacion;
use App\Filament\Admin\Resources\Aprobacions\Pages\EditAprobacion;
use App\Filament\Admin\Resources\Aprobacions\Pages\ListAprobacions;
use App\Models\Aprobacion;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Filters\Filter;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

class AprobacionResource extends Resource
{
    protected static ?string $model = Aprobacion::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-check-badge';

    protected static ?string $navigationLabel = 'Aprobaciones';

    protected static string|\UnitEnum|null $navigationGroup = 'Licencias';

    protected static ?string $pluralModelLabel = 'Aprobaciones';

    protected static ?string $modelLabel = 'Aprobación';

    public static function canAccess(): bool
    {
        return ! Auth::user()?->hasRole('agente');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('solicitud_id')
                    ->label('Solicitud')
                    ->relationship('solicitud', 'id')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('aprobado_por')
                    ->label('Aprobado por')
                    ->relationship('aprobador', 'apellido')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->apellido}, {$record->nombre}")
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('etapa')
                    ->maxLength(255),
                Select::make('estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'aprobado' => 'Aprobado',
                        'rechazado' => 'Rechazado',
                    ])
                    ->default('pendiente')
                    ->required(),
                Textarea::make('observacion')
                    ->label('Observación')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                DatePicker::make('fecha')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->groups([
                Group::make('solicitud_id')
                    ->label('Solicitud')
                    ->getTitleFromRecordUsing(function ($record) {
                        $sol = $record->solicitud;
                        $count = $sol->aprobaciones->count() ?? 0;
                        return "Solicitud #{$record->solicitud_id} — {$sol->agente->apellido}, {$sol->agente->nombre} ({$sol->tipoSolicitud->nombre}) — {$count} aprob.";
                    })
                    ->collapsible(),
            ])
            ->defaultGroup('solicitud_id')
            ->columns([
                TextColumn::make('solicitud_id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('solicitud.agente.apellido')
                    ->label('Agente Solicitante')
                    ->formatStateUsing(fn ($record) => "{$record->solicitud->agente->apellido}, {$record->solicitud->agente->nombre}")
                    ->searchable()
                    ->sortable(),
                TextColumn::make('solicitud.tipoSolicitud.nombre')
                    ->label('Licencia')
                    ->description(fn ($record) => $record->solicitud->tipoSolicitud->categoria)
                    ->color('warning')
                    ->weight('semibold')
                    ->wrap()
                    ->sortable(),
                TextColumn::make('periodo')
                    ->label('Período Licencia')
                    ->formatStateUsing(fn ($record) => $record->solicitud->fecha_inicio->format('d/m/Y') . ' al ' . $record->solicitud->fecha_fin->format('d/m/Y'))
                    ->icon('heroicon-m-calendar-days')
                    ->color('gray'),
                TextColumn::make('aprobador.apellido')
                    ->label('Resuelto por')
                    ->formatStateUsing(fn ($record) => $record->aprobador ? "{$record->aprobador->apellido}, {$record->aprobador->nombre}" : '-')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('etapa')
                    ->label('Etapa')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'jefatura' => 'Jefatura',
                        'secretaria' => 'Secretaría Gral.',
                        'direccion' => 'Presidencia',
                        default => $state,
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'jefatura' => 'heroicon-m-building-office',
                        'secretaria' => 'heroicon-m-document-text',
                        'direccion' => 'heroicon-m-shield-check',
                        default => 'heroicon-m-question-mark-circle',
                    })
                    ->color('info')
                    ->weight('bold')
                    ->sortable(),
                TextColumn::make('estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'aprobado' => 'Aprobado',
                        'rechazado' => 'Rechazado',
                        'pendiente' => 'Pendiente',
                        default => $state,
                    })
                    ->colors([
                        'warning' => 'pendiente',
                        'success' => 'aprobado',
                        'danger' => 'rechazado',
                    ]),
                TextColumn::make('fecha')
                    ->label('Fecha Resolución')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'aprobado' => 'Aprobado',
                        'rechazado' => 'Rechazado',
                    ]),
                SelectFilter::make('agente')
                    ->relationship('solicitud.agente', 'apellido')
                    ->label('Agente Solicitante')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('etapa')
                    ->label('Etapa')
                    ->options([
                        'jefatura' => 'Jefatura',
                        'secretaria' => 'Secretaría Gral.',
                        'direccion' => 'Presidencia',
                    ]),
                Filter::make('fecha_aprobacion')
                    ->form([
                        DatePicker::make('desde')->label('Resolución desde'),
                        DatePicker::make('hasta')->label('Resolución hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['desde'], fn ($q, $date) => $q->whereDate('fecha', '>=', $date))
                            ->when($data['hasta'], fn ($q, $date) => $q->whereDate('fecha', '<=', $date));
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('aprobar')
                    ->label('Aprobar')
                    ->icon('heroicon-m-check-circle')
                    ->iconButton()
                    ->tooltip('Aprobar')
                    ->action(function (Aprobacion $record): void {
                        $record->update(['estado' => 'aprobado']);

                        Notification::make()
                            ->success()
                            ->title('Aprobación actualizada')
                            ->body('La aprobación se marcó como aprobada.')
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->hidden(fn ($record) => $record->estado === 'aprobado')
                    ->color('success'),
                Action::make('rechazar')
                    ->label('Rechazar')
                    ->icon('heroicon-m-x-circle')
                    ->iconButton()
                    ->tooltip('Rechazar')
                    ->action(function (Aprobacion $record): void {
                        $record->update(['estado' => 'rechazado']);

                        Notification::make()
                            ->danger()
                            ->title('Aprobación actualizada')
                            ->body('La aprobación se marcó como rechazada.')
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->hidden(fn ($record) => $record->estado === 'rechazado')
                    ->color('danger'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('aprobar_seleccion')
                        ->label('Aprobar seleccionadas')
                        ->icon('heroicon-m-check-badge')
                        ->action(function (Collection $records): void {
                            $updated = Aprobacion::whereIn('id', $records->pluck('id'))->update(['estado' => 'aprobado']);

                            Notification::make()
                                ->success()
                                ->title('Acción masiva completada')
                                ->body("Se aprobaron {$updated} registro(s).")
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->color('success'),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['aprobador', 'solicitud', 'solicitud.agente', 'solicitud.tipoSolicitud', 'solicitud.aprobaciones']);

        $tienePermisosDeAdmin = Auth::user()?->hasAnyRole(['administrador', 'jefatura', 'secretaria_general', 'presidencia']) ?? false;

        if (! $tienePermisosDeAdmin) {
            $query->whereHas('solicitud', function (Builder $query): void {
                $query->where('agente_id', Auth::user()->agente?->id);
            });
        }

        return $query;
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
            'index' => ListAprobacions::route('/'),
            'create' => CreateAprobacion::route('/create'),
            'edit' => EditAprobacion::route('/{record}/edit'),
        ];
    }
}
