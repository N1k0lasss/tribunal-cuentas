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
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
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

    // ── Infolist (Divulgación Progresiva — modal) ─────────────

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                // ── Sección 1: Datos del Agente ───────────────────
                Section::make('Datos del Agente')
                    ->icon('heroicon-o-user-circle')
                    ->collapsible()
                    ->components([
                        Grid::make(4)
                            ->components([
                                TextEntry::make('agente.apellido')
                                    ->label('Apellido')
                                    ->weight('bold')
                                    ->formatStateUsing(fn ($record) => $record->agente?->apellido ?? '—'),

                                TextEntry::make('agente.nombre')
                                    ->label('Nombre')
                                    ->formatStateUsing(fn ($record) => $record->agente?->nombre ?? '—'),

                                TextEntry::make('agente.id')
                                    ->label('Nº de Legajo')
                                    ->badge()
                                    ->color('gray'),

                                TextEntry::make('agente.area.nombre')
                                    ->label('Área')
                                    ->icon('heroicon-m-building-office'),
                            ]),
                        Grid::make(2)
                            ->components([
                                TextEntry::make('agente.cargo')
                                    ->label('Cargo')
                                    ->icon('heroicon-m-briefcase')
                                    ->default('Sin cargo asignado'),

                                TextEntry::make('agente.fecha_ingreso')
                                    ->label('Fecha de Ingreso')
                                    ->date('d/m/Y')
                                    ->icon('heroicon-m-calendar'),
                            ]),
                    ]),

                // ── Sección 2: Detalle de la Solicitud ────────────
                Section::make('Detalle de la Solicitud')
                    ->icon('heroicon-o-document-text')
                    ->collapsible()
                    ->components([
                        Grid::make(3)
                            ->components([
                                TextEntry::make('tipoSolicitud.nombre')
                                    ->label('Régimen Legal')
                                    ->weight('bold')
                                    ->color('primary'),

                                TextEntry::make('tipoSolicitud.categoria')
                                    ->label('Categoría')
                                    ->badge()
                                    ->color('info'),

                                TextEntry::make('tipoSolicitud.articulo_legal')
                                    ->label('Artículo Legal')
                                    ->icon('heroicon-m-scale')
                                    ->default('—'),
                            ]),
                        Grid::make(4)
                            ->components([
                                TextEntry::make('fecha_solicitud')
                                    ->label('Fecha de Solicitud')
                                    ->date('d/m/Y')
                                    ->icon('heroicon-m-calendar'),

                                TextEntry::make('fecha_inicio')
                                    ->label('Fecha Inicio')
                                    ->date('d/m/Y')
                                    ->icon('heroicon-m-calendar-days'),

                                TextEntry::make('fecha_fin')
                                    ->label('Fecha Fin')
                                    ->date('d/m/Y')
                                    ->icon('heroicon-m-calendar-days')
                                    ->default('—'),

                                TextEntry::make('dias_solicitados')
                                    ->label('Días Solicitados')
                                    ->badge()
                                    ->color('gray')
                                    ->suffix(' días')
                                    ->default('—'),
                            ]),
                        Grid::make(3)
                            ->components([
                                TextEntry::make('hora_inicio')
                                    ->label('Hora de Salida')
                                    ->icon('heroicon-m-clock')
                                    ->default('—')
                                    ->visible(fn ($record) => $record->hora_inicio !== null),

                                TextEntry::make('hora_fin')
                                    ->label('Hora de Regreso')
                                    ->icon('heroicon-m-clock')
                                    ->default('—')
                                    ->visible(fn ($record) => $record->hora_fin !== null),

                                TextEntry::make('estado')
                                    ->label('Estado')
                                    ->badge()
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'pendiente' => 'Pendiente',
                                        'en_revision' => 'En Revisión',
                                        'aprobado' => 'Aprobado',
                                        'rechazado' => 'Rechazado',
                                        default => $state,
                                    })
                                    ->color(fn (string $state): string => match ($state) {
                                        'pendiente' => 'warning',
                                        'en_revision' => 'info',
                                        'aprobado' => 'success',
                                        'rechazado' => 'danger',
                                        default => 'gray',
                                    })
                                    ->icon(fn (string $state): string => match ($state) {
                                        'pendiente' => 'heroicon-m-clock',
                                        'en_revision' => 'heroicon-m-arrow-path',
                                        'aprobado' => 'heroicon-m-check-badge',
                                        'rechazado' => 'heroicon-m-x-circle',
                                        default => 'heroicon-m-question-mark-circle',
                                    }),
                            ]),
                        TextEntry::make('motivo')
                            ->label('Motivo / Observaciones')
                            ->icon('heroicon-m-chat-bubble-left-ellipsis')
                            ->default('Sin observaciones')
                            ->columnSpanFull(),
                    ]),

                // ── Sección 3: Documentos Adjuntos ────────────────
                Section::make('Documentos Adjuntos')
                    ->icon('heroicon-o-paper-clip')
                    ->collapsible()
                    ->collapsed()
                    ->components([
                        RepeatableEntry::make('documentos')
                            ->label('')
                            ->columns(3)
                            ->components([
                                TextEntry::make('nombre_archivo')
                                    ->label('Archivo')
                                    ->icon('heroicon-m-document')
                                    ->weight('medium'),

                                TextEntry::make('tipo_documento')
                                    ->label('Tipo')
                                    ->badge()
                                    ->color('gray'),

                                TextEntry::make('descripcion')
                                    ->label('Descripción')
                                    ->default('—'),
                            ])
                            ->placeholder('No hay documentos adjuntos.'),
                    ]),

                // ── Sección 4: Historial de Aprobaciones ──────────
                Section::make('Historial de Aprobaciones')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->collapsible()
                    ->collapsed()
                    ->components([
                        RepeatableEntry::make('aprobaciones')
                            ->label('')
                            ->columns(4)
                            ->components([
                                TextEntry::make('etapa')
                                    ->label('Etapa')
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'jefatura' => 'Jefatura',
                                        'secretaria' => 'Secretaría General',
                                        'direccion' => 'Presidencia',
                                        default => $state,
                                    })
                                    ->weight('bold'),

                                TextEntry::make('estado')
                                    ->label('Resolución')
                                    ->badge()
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'aprobado' => 'Aprobado',
                                        'rechazado' => 'Rechazado',
                                        'pendiente' => 'Pendiente',
                                        default => $state,
                                    })
                                    ->color(fn (string $state): string => match ($state) {
                                        'aprobado' => 'success',
                                        'rechazado' => 'danger',
                                        'pendiente' => 'warning',
                                        default => 'gray',
                                    }),

                                TextEntry::make('observacion')
                                    ->label('Observación')
                                    ->default('—'),

                                TextEntry::make('fecha')
                                    ->label('Fecha')
                                    ->date('d/m/Y')
                                    ->icon('heroicon-m-calendar'),
                            ])
                            ->placeholder('Sin registros de aprobación.'),
                    ]),
            ]);
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
            ->defaultSort('fecha_solicitud', 'desc')
            ->columns([
                TextColumn::make('agente.apellido')
                    ->label('Agente')
                    ->formatStateUsing(fn ($record) => $record->agente ? "{$record->agente->apellido}, {$record->agente->nombre}" : '-')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-m-user'),
                TextColumn::make('tipoSolicitud.nombre')
                    ->label('Tipo')
                    ->sortable()
                    ->description(fn ($record) => $record->tipoSolicitud?->categoria ? mb_substr($record->tipoSolicitud->categoria, 0, 30) : null)
                    ->color('primary')
                    ->weight('medium'),
                TextColumn::make('fecha_solicitud')
                    ->label('Fecha Solicitud')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-m-calendar'),
                TextColumn::make('agente.id')
                    ->label('Legajo')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->toggleable(),
                TextColumn::make('fecha_inicio')
                    ->label('Desde')
                    ->date('d/m/Y')
                    ->sortable()
                    ->icon('heroicon-m-calendar-days'),
                TextColumn::make('fecha_fin')
                    ->label('Hasta')
                    ->date('d/m/Y')
                    ->sortable()
                    ->icon('heroicon-m-calendar-days'),
                TextColumn::make('dias_solicitados')
                    ->label('Días')
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('motivo')
                    ->label('Motivo')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->tooltip(fn ($record) => $record->motivo),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pendiente' => 'Pendiente',
                        'en_revision' => 'En Revisión',
                        'aprobado' => 'Aprobado',
                        'rechazado' => 'Rechazado',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pendiente' => 'warning',
                        'en_revision' => 'info',
                        'aprobado' => 'success',
                        'rechazado' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'pendiente' => 'heroicon-m-clock',
                        'en_revision' => 'heroicon-m-arrow-path',
                        'aprobado' => 'heroicon-m-check-badge',
                        'rechazado' => 'heroicon-m-x-circle',
                        default => 'heroicon-m-question-mark-circle',
                    }),
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
                Filter::make('fecha_solicitud')
                    ->form([
                        DatePicker::make('desde')
                            ->label('Desde'),
                        DatePicker::make('hasta')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['desde'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha_solicitud', '>=', $date),
                            )
                            ->when(
                                $data['hasta'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha_solicitud', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['desde'] ?? null) {
                            $indicators[] = 'Desde: ' . \Carbon\Carbon::parse($data['desde'])->format('d/m/Y');
                        }

                        if ($data['hasta'] ?? null) {
                            $indicators[] = 'Hasta: ' . \Carbon\Carbon::parse($data['hasta'])->format('d/m/Y');
                        }

                        return $indicators;
                    }),
            ])
            ->actions([
                ViewAction::make(),
            ->recordActions([
                ViewAction::make()
                    ->iconButton()
                    ->tooltip('Ver Detalles')
                    ->modalWidth('4xl')
                    ->slideOver(),
                EditAction::make()
                    ->iconButton()
                    ->tooltip('Editar Solicitud')
                    ->visible(fn (Solicitud $record) => $record->puedeSerEditada()),

                // ── Action: Aprobar ────────────────────────────────
                Action::make('aprobar')
                    ->label('Aprobar')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->button()
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
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->button()
                    ->outlined()
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
