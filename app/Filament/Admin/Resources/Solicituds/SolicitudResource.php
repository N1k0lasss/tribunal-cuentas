<?php

namespace App\Filament\Admin\Resources\Solicituds;

use App\Filament\Admin\Resources\Solicituds\Pages\CreateSolicitud;
use App\Filament\Admin\Resources\Solicituds\Pages\EditSolicitud;
use App\Filament\Admin\Resources\Solicituds\Pages\ListSolicituds;
use App\Models\Aprobacion;
use App\Models\Solicitud;
use App\Models\TipoSolicitud;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;
use BackedEnum;
use Illuminate\Contracts\Support\Htmlable;

class SolicitudResource extends Resource
{
    protected static ?string $model = Solicitud::class;

    protected static ?string $navigationLabel = 'Solicitudes';

    protected static UnitEnum|string|null $navigationGroup = 'Licencias';

    protected static ?string $pluralModelLabel = 'Solicitudes';

    protected static ?string $modelLabel = 'Solicitud';

    public static function getNavigationIcon(): string | BackedEnum | Htmlable | null
    {
        return 'heroicon-o-document-text';
    }

    // ── Formulario ─────────────────────────────────────────────

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('agente_id')
                    ->label('Agente')
                    ->relationship('agente', 'apellido')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->apellido}, {$record->nombre}")
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('categoria')
                    ->label('Categoría')
                    ->options([
                        'Receso' => 'Receso',
                        'Salud y Maternidad' => 'Salud y Maternidad',
                        'Extraordinaria' => 'Licencia Extraordinaria',
                        'Justificación' => 'Justificación',
                        'Franquicia' => 'Franquicia',
                        'Falta/Tardanza' => 'Falta/Tardanza',
                    ])
                    ->live()
                    ->afterStateUpdated(fn ($set) => $set('tipo_solicitud_id', null))
                    ->disabled(fn (?Solicitud $record) => $record?->estaEnFlujo()),

                Select::make('tipo_solicitud_id')
                    ->label('Régimen Legal Específico')
                    ->options(fn (Get $get) => TipoSolicitud::query()->where('categoria', '=', $get('categoria', false), 'and')->pluck('nombre', 'id'))
                    ->live()
                    ->required(),

                DatePicker::make('fecha_solicitud')
                    ->label('Fecha de Solicitud')
                    ->default(now())
                    ->required()
                    ->disabled(fn (?Solicitud $record) => $record?->estaEnFlujo()),

                // Fechas (Licencias por Días)
                DatePicker::make('fecha_inicio')
                    ->label('Fecha Inicio / Fecha de la Franquicia')
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($set, $get, $state) {
                        $set('dias_solicitados', SolicitudResource::calcularDias($get('tipo_solicitud_id'), $state, $get('fecha_fin')));
                    }),

                DatePicker::make('fecha_fin')
                    ->label('Fecha Fin')
                    ->required(fn (Get $get) => TipoSolicitud::find($get('tipo_solicitud_id', false), ['*'])?->unidad_medida === 'dias')
                    ->visible(fn (Get $get) => TipoSolicitud::find($get('tipo_solicitud_id', false), ['*'])?->unidad_medida === 'dias' || ! TipoSolicitud::find($get('tipo_solicitud_id', false), ['*']))
                    ->live()
                    ->afterStateUpdated(function ($set, $get, $state) {
                        $set('dias_solicitados', SolicitudResource::calcularDias($get('tipo_solicitud_id'), $get('fecha_inicio'), $state));
                    }),

                // Horas (Franquicias)
                TimePicker::make('hora_inicio')
                    ->label('Hora de Salida')
                    ->required(fn (Get $get) => TipoSolicitud::find($get('tipo_solicitud_id', false), ['*'])?->unidad_medida === 'horas')
                    ->visible(fn (Get $get) => TipoSolicitud::find($get('tipo_solicitud_id', false), ['*'])?->unidad_medida === 'horas'),

                TimePicker::make('hora_fin')
                    ->label('Hora de Regreso')
                    ->required(fn (Get $get) => TipoSolicitud::find($get('tipo_solicitud_id', false), ['*'])?->unidad_medida === 'horas')
                    ->visible(fn (Get $get) => TipoSolicitud::find($get('tipo_solicitud_id', false), ['*'])?->unidad_medida === 'horas')
                    ->disabled(fn (?Solicitud $record) => $record?->estaEnFlujo()),

                TextInput::make('dias_solicitados')
                    ->label('Días Solicitados')
                    ->numeric()
                    ->readOnly()
                    ->helperText('Se calcula automáticamente según el régimen legal.')
                    ->visible(fn (Get $get) => TipoSolicitud::find($get('tipo_solicitud_id', false), ['*'])?->unidad_medida === 'dias' || ! TipoSolicitud::find($get('tipo_solicitud_id', false), ['*'])),

                TextInput::make('motivo')
                    ->label('Motivo / Observaciones')
                    ->maxLength(100)
                    ->disabled(fn (?Solicitud $record) => $record?->estaEnFlujo()),

                // Notice de Obligatoriedad de adjunto
                Placeholder::make('requiere_adjunto_info')
                    ->label('Atención')
                    ->content('Este tipo de solicitud requiere subir documentación respaldatoria obligatoriamente.')
                    ->visible(fn (Get $get) => TipoSolicitud::find($get('tipo_solicitud_id', false), ['*'])?->requiere_adjunto === true),

                Select::make('estado')
                    ->label('Estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'en_revision' => 'En Revisión',
                        'aprobado' => 'Aprobado',
                        'rechazado' => 'Rechazado',
                    ])
                    ->default('pendiente')
                    ->required()
                    ->visible(fn () => ! Auth::user()?->hasRole('agente'))
                    ->disabled(fn (?Solicitud $record) => $record?->estaEnFlujo()),
            ]);
    }

    // ── Cálculo de días ────────────────────────────────────────

    public static function calcularDias($tipoId, $fechaInicio, $fechaFin)
    {
        if (! $tipoId || ! $fechaInicio || ! $fechaFin) {
            return null;
        }

        $tipo = TipoSolicitud::find($tipoId, ['*']);

        if (! $tipo || $tipo->unidad_medida !== 'dias') {
            return null;
        }

        $inicio = Carbon::parse($fechaInicio);
        $fin = Carbon::parse($fechaFin);

        if ($fin->lessThan($inicio)) {
            return 0;
        }

        if ($tipo->tipo_dias === 'habiles') {
            return $inicio->diffInDaysFiltered(function (Carbon $date) {
                return ! $date->isWeekend();
            }, $fin) + 1;
        }

        // Días corridos
        return $inicio->diffInDays($fin) + 1;
    }

    // ── Tabla ──────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        $etapaLabels = [
            Solicitud::ETAPA_JEFATURA => 'Jefatura',
            Solicitud::ETAPA_SECRETARIA => 'Secretaría General',
            Solicitud::ETAPA_DIRECCION => 'Presidencia',
        ];

        return $table
            ->columns([
                TextColumn::make('agente.apellido')
                    ->label('Agente')
                    ->formatStateUsing(fn ($record) => $record->agente ? "{$record->agente->apellido}, {$record->agente->nombre}" : '-')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tipoSolicitud.nombre')
                    ->label('Tipo')
                    ->sortable(),
                TextColumn::make('fecha_solicitud')
                    ->label('Fecha Solicitud')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('fecha_inicio')
                    ->label('Desde')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('fecha_fin')
                    ->label('Hasta')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('dias_solicitados')
                    ->label('Días')
                    ->sortable(),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->colors([
                        'warning' => Solicitud::ESTADO_PENDIENTE,
                        'info' => Solicitud::ESTADO_EN_REVISION,
                        'success' => Solicitud::ESTADO_APROBADO,
                        'danger' => Solicitud::ESTADO_RECHAZADO,
                    ]),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'en_revision' => 'En Revisión',
                        'aprobado' => 'Aprobado',
                        'rechazado' => 'Rechazado',
                    ]),
                SelectFilter::make('tipo_solicitud_id')
                    ->label('Régimen Legal')
                    ->relationship('tipoSolicitud', 'nombre'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn (Solicitud $record) => $record->puedeSerEditada()),

                // ── Action: Aprobar ────────────────────────────────
                Action::make('aprobar')
                    ->label('Aprobar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Aprobar Solicitud')
                    ->modalDescription(fn (Solicitud $record) => 'Está a punto de aprobar esta solicitud en la etapa de '.($etapaLabels[$record->etapaActual()] ?? 'desconocida').'.')
                    ->form([
                        Textarea::make('observacion')
                            ->label('Observación (opcional)')
                            ->maxLength(500),
                    ])
                    ->action(function (Solicitud $record, array $data) {
                        static::procesarAprobacion($record, 'aprobado', $data['observacion'] ?? null);

                        Notification::make()
                            ->success()
                            ->title('Solicitud aprobada')
                            ->body('La solicitud fue aprobada correctamente en esta etapa.')
                            ->send();
                    })
                    ->visible(fn (Solicitud $record) => Auth::user()?->can('aprobar', $record)),

                // ── Action: Rechazar ───────────────────────────────
                Action::make('rechazar')
                    ->label('Rechazar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Rechazar Solicitud')
                    ->modalDescription('Esta acción rechazará la solicitud y detendrá el flujo de aprobación.')
                    ->form([
                        Textarea::make('observacion')
                            ->label('Motivo del rechazo')
                            ->required()
                            ->maxLength(500),
                    ])
                    ->action(function (Solicitud $record, array $data) {
                        static::procesarAprobacion($record, 'rechazado', $data['observacion']);

                        Notification::make()
                            ->danger()
                            ->title('Solicitud rechazada')
                            ->body('La solicitud fue rechazada.')
                            ->send();
                    })
                    ->visible(fn (Solicitud $record) => Auth::user()?->can('rechazar', $record)),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    // ── Lógica de Aprobación / Rechazo ─────────────────────────

    protected static function procesarAprobacion(Solicitud $record, string $estado, ?string $observacion): void
    {
        $etapa = $record->etapaActual();
        $user = Auth::user();

        // Crear registro de aprobación
        Aprobacion::create([
            'solicitud_id' => $record->id,
            'aprobado_por' => $user->agente?->id ?? $user->id,
            'etapa' => $etapa,
            'estado' => $estado,
            'observacion' => $observacion,
            'fecha' => now(),
        ]);

        if ($estado === 'rechazado') {
            // Rechazar cierra el flujo completamente
            $record->update(['estado' => Solicitud::ESTADO_RECHAZADO]);

            return;
        }

        // Si aprobado, verificar si es la última etapa
        $siguienteEtapa = $record->siguienteEtapa();

        if ($siguienteEtapa === null) {
            // Última etapa aprobada → solicitud aprobada
            $record->update(['estado' => Solicitud::ESTADO_APROBADO]);
        } else {
            // Aún quedan etapas → mantener en revisión
            $record->update(['estado' => Solicitud::ESTADO_EN_REVISION]);
        }

        // Refrescar relaciones para que la vista refleje el cambio
        $record->load('aprobaciones');
    }

    // ── Eloquent Query Scope ───────────────────────────────────

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();

        return parent::getEloquentQuery()
            ->with(['agente', 'tipoSolicitud'])
            ->when($user->hasRole('agente'), function (Builder $query) use ($user) {
                // El agente solo ve sus propias solicitudes
                return $query->where('agente_id', $user->agente?->id);
            })
            ->when($user->hasRole('jefatura'), function (Builder $query) {
                // Jefatura ve solicitudes pendientes en su etapa + las que ya procesó
                return $query->where(function (Builder $q) {
                    $q->where('estado', Solicitud::ESTADO_EN_REVISION)
                        ->orWhere('estado', Solicitud::ESTADO_PENDIENTE);
                });
            })
            ->when($user->hasRole('secretaria_general'), function (Builder $query) {
                // Secretaría General ve solicitudes que ya pasaron por jefatura
                return $query->where(function (Builder $q) {
                    $q->where('estado', Solicitud::ESTADO_EN_REVISION)
                        ->orWhere('estado', Solicitud::ESTADO_APROBADO);
                });
            })
            ->when($user->hasRole('presidencia'), function (Builder $query) {
                // Presidencia ve solicitudes que ya pasaron por secretaría
                return $query->where(function (Builder $q) {
                    $q->where('estado', Solicitud::ESTADO_EN_REVISION)
                        ->orWhere('estado', Solicitud::ESTADO_APROBADO);
                });
            });
        // Administrador: no se aplica filtro (ve todo)
    }

    // ── Relaciones y Páginas ───────────────────────────────────

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSolicituds::route('/'),
            'create' => CreateSolicitud::route('/create'),
            'edit' => EditSolicitud::route('/{record}/edit'),
        ];
    }
}
