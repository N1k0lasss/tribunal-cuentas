<?php

namespace App\Filament\Admin\Resources\Solicituds;

use App\Filament\Admin\Resources\Solicituds\Pages\CreateSolicitud;
use App\Filament\Admin\Resources\Solicituds\Pages\EditSolicitud;
use App\Filament\Admin\Resources\Solicituds\Pages\ListSolicituds;
use App\Models\Solicitud;
use App\Models\TipoSolicitud;
use App\Services\WorkflowSolicitudesService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\HtmlString;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class SolicitudResource extends Resource
{
    protected static ?string $model = Solicitud::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Solicitudes';

    protected static string|\UnitEnum|null $navigationGroup = 'Licencias';

    protected static ?string $pluralModelLabel = 'Solicitudes';

    protected static ?string $modelLabel = 'Solicitud';

    // ── Formulario ─────────────────────────────────────────────

    public static function form(Schema $schema): Schema
    {
        $user     = Auth::user();
        $esAgente = $user?->hasRole('agente');

        return $schema->components([

            // ═══════════════════════════════════════════════════════════
            // SECCIÓN 1 — Identificación de la Solicitud
            // Los campos que definen QUÉ se solicita. Van primero.
            // ═══════════════════════════════════════════════════════════
            Section::make('Identificación')
                ->description('Seleccioná el agente y el tipo de licencia que se tramitará.')
                ->icon('heroicon-o-identification')
                ->columns(3)
                ->schema([

                    Select::make('agente_id')
                        ->label('Agente')
                        ->relationship('agente', 'apellido')
                        ->getOptionLabelFromRecordUsing(
                            fn ($record) => "{$record->apellido}, {$record->nombre}"
                        )
                        ->searchable()
                        ->preload()
                        ->required()
                        ->default(fn () => $esAgente ? $user->agente?->id : null)
                        ->disabled(fn () => $esAgente)
                        ->dehydrated()
                        ->columnSpan(1),

                    Select::make('categoria')
                        ->label('Categoría')
                        ->options([
                            'Receso'             => 'Receso',
                            'Salud y Maternidad' => 'Salud y Maternidad',
                            'Extraordinaria'     => 'Licencia Extraordinaria',
                            'Justificación'      => 'Justificación',
                            'Franquicia'         => 'Franquicia',
                            'Falta/Tardanza'     => 'Falta/Tardanza',
                        ])
                        ->live()
                        ->afterStateUpdated(fn ($set) => $set('tipo_solicitud_id', null))
                        ->disabled(fn (?Solicitud $record) => $record?->estaEnFlujo())
                        ->dehydrated(false) // Campo de UI auxiliar; no persiste en DB
                        ->columnSpan(1),

                    Select::make('tipo_solicitud_id')
                        ->label('Tipo de Licencia')
                        ->helperText('El régimen legal determina el cálculo de días y los requisitos.')
                        ->options(
                            fn ($get) => TipoSolicitud::where('categoria', $get('categoria'))
                                ->pluck('nombre', 'id')
                        )
                        ->live()
                        ->required()
                        ->disabled(fn (?Solicitud $record) => $record?->estaEnFlujo())
                        ->columnSpan(1),
                ]),

            // ═══════════════════════════════════════════════════════════
            // SECCIÓN 2 — Aviso de Documentación Obligatoria (Poka-Yoke)
            // Aparece ANTES de las fechas para que el operador lo lea
            // ANTES de continuar, no después de completar todo el form.
            // ═══════════════════════════════════════════════════════════
            Section::make('')
                ->visible(
                    fn ($get) => TipoSolicitud::find($get('tipo_solicitud_id'))?->requiere_adjunto === true
                )
                ->schema([
                    Placeholder::make('requiere_adjunto_info')
                        ->label('')
                        ->content(
                            new HtmlString(
                                '<div class="flex items-start gap-3 rounded-lg border border-amber-400 bg-amber-50 p-4 dark:border-amber-500 dark:bg-amber-950">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-5 w-5 shrink-0 text-amber-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                                    </svg>
                                    <div>
                                        <p class="font-semibold text-amber-800 dark:text-amber-200">Documentación obligatoria</p>
                                        <p class="mt-1 text-sm text-amber-700 dark:text-amber-300">
                                            Este tipo de solicitud <strong>requiere adjuntar documentación respaldatoria</strong> antes de ser enviada.
                                            Sin el adjunto, la solicitud no podrá avanzar en el flujo de aprobación.
                                        </p>
                                    </div>
                                </div>'
                            )
                        ),
                ]),

            // ═══════════════════════════════════════════════════════════
            // SECCIÓN 3 — Período y Duración
            // Layout de 2 columnas para pares lógicos de campos.
            // ═══════════════════════════════════════════════════════════
            Section::make('Período Solicitado')
                ->description('Ingresá las fechas y/o horas del período de licencia.')
                ->icon('heroicon-o-calendar-days')
                ->columns(2)
                ->schema([

                    DatePicker::make('fecha_inicio')
                        ->label(
                            fn ($get) => TipoSolicitud::find($get('tipo_solicitud_id'))?->unidad_medida === 'horas'
                                ? 'Fecha de la Franquicia'
                                : 'Fecha de Inicio'
                        )
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($set, $get, $state) {
                            $set('dias_solicitados', static::calcularDias(
                                $get('tipo_solicitud_id'), $state, $get('fecha_fin')
                            ));
                        })
                        ->disabled(fn (?Solicitud $record) => $record?->estaEnFlujo()),

                    DatePicker::make('fecha_fin')
                        ->label('Fecha de Fin')
                        ->required(
                            fn ($get) => TipoSolicitud::find($get('tipo_solicitud_id'))?->unidad_medida === 'dias'
                        )
                        ->visible(
                            fn ($get) => TipoSolicitud::find($get('tipo_solicitud_id'))?->unidad_medida === 'dias'
                                || ! TipoSolicitud::find($get('tipo_solicitud_id'))
                        )
                        ->minDate(fn ($get) => $get('fecha_inicio')) // Poka-Yoke: impide fecha_fin < fecha_inicio
                        ->live()
                        ->afterStateUpdated(function ($set, $get, $state) {
                            $set('dias_solicitados', static::calcularDias(
                                $get('tipo_solicitud_id'), $get('fecha_inicio'), $state
                            ));
                        })
                        ->disabled(fn (?Solicitud $record) => $record?->estaEnFlujo()),

                    // Franquicias: par de horas en 2 columnas
                    TimePicker::make('hora_inicio')
                        ->label('Hora de Salida')
                        ->required(
                            fn ($get) => TipoSolicitud::find($get('tipo_solicitud_id'))?->unidad_medida === 'horas'
                        )
                        ->visible(
                            fn ($get) => TipoSolicitud::find($get('tipo_solicitud_id'))?->unidad_medida === 'horas'
                        )
                        ->disabled(fn (?Solicitud $record) => $record?->estaEnFlujo()),

                    TimePicker::make('hora_fin')
                        ->label('Hora de Regreso')
                        ->required(
                            fn ($get) => TipoSolicitud::find($get('tipo_solicitud_id'))?->unidad_medida === 'horas'
                        )
                        ->visible(
                            fn ($get) => TipoSolicitud::find($get('tipo_solicitud_id'))?->unidad_medida === 'horas'
                        )
                        ->disabled(fn (?Solicitud $record) => $record?->estaEnFlujo()),

                    // Campo calculado — diferenciado visualmente del resto
                    TextInput::make('dias_solicitados')
                        ->label('Días Solicitados')
                        ->numeric()
                        ->readOnly()
                        ->suffix('días')
                        ->helperText('Calculado automáticamente según el régimen legal seleccionado.')
                        ->extraInputAttributes(['class' => 'bg-gray-100 dark:bg-white/5 cursor-not-allowed'])
                        ->visible(
                            fn ($get) => TipoSolicitud::find($get('tipo_solicitud_id'))?->unidad_medida === 'dias'
                                || ! TipoSolicitud::find($get('tipo_solicitud_id'))
                        ),
                ]),

            // ═══════════════════════════════════════════════════════════
            // SECCIÓN 4 — Datos Administrativos
            // Estado, prórroga y observaciones. Sección de menor urgencia.
            // ═══════════════════════════════════════════════════════════
            Section::make('Datos Administrativos')
                ->description('Estado del trámite, prórroga y observaciones.')
                ->icon('heroicon-o-clipboard-document-list')
                ->columns(2)
                ->schema([

                    DatePicker::make('fecha_solicitud')
                        ->label('Fecha de Solicitud')
                        ->default(now())
                        ->required()
                        ->disabled(fn (?Solicitud $record) => $record?->estaEnFlujo()),

                    // El estado se oculta para agentes; siempre pendiente al crear
                    Select::make('estado')
                        ->label('Estado')
                        ->options([
                            'pendiente'   => 'Pendiente',
                            'en_revision' => 'En Revisión',
                            'aprobado'    => 'Aprobado',
                            'rechazado'   => 'Rechazado',
                        ])
                        ->default('pendiente')
                        ->required()
                        ->visible(fn () => ! $esAgente)
                        // Poka-Yoke: bloquear cambio manual si el flujo ya comenzó
                        ->disabled(fn (?Solicitud $record) => $record?->estaEnFlujo()),

                    // Toggle booleano (reemplaza Select 0/1 que era confuso)
                    Toggle::make('es_prorroga')
                        ->label('¿Es prórroga de una solicitud anterior?')
                        ->default(false)
                        ->live()
                        ->dehydrated() // Necesario para que el cast :boolean funcione
                        ->disabled(fn (?Solicitud $record) => $record?->estaEnFlujo())
                        ->columnSpanFull(),

                    Select::make('solicitud_origen_id')
                        ->label('Solicitud Origen (prórroga)')
                        ->helperText('Seleccioná la solicitud original que se está prorrogando.')
                        ->relationship('solicitudOrigen', 'id')
                        ->getOptionLabelFromRecordUsing(
                            fn (Solicitud $record) => "#{$record->id} — {$record->fecha_inicio?->format('d/m/Y')} al {$record->fecha_fin?->format('d/m/Y')}"
                        )
                        ->required(fn ($get) => (bool) $get('es_prorroga'))
                        ->visible(fn ($get)  => (bool) $get('es_prorroga'))
                        ->searchable()
                        ->preload()
                        ->disabled(fn (?Solicitud $record) => $record?->estaEnFlujo())
                        ->columnSpanFull(),

                    Textarea::make('motivo')
                        ->label('Motivo / Observaciones')
                        ->helperText('Opcional. Máximo 100 caracteres.')
                        ->maxLength(100)
                        ->rows(3)
                        ->disabled(fn (?Solicitud $record) => $record?->estaEnFlujo())
                        ->columnSpanFull(),
                ]),
        ]);
    }

    // ── Cálculo de días ────────────────────────────────────────

    public static function calcularDias($tipoId, $fechaInicio, $fechaFin)
    {
        if (! $tipoId || ! $fechaInicio || ! $fechaFin) {
            return null;
        }

        $tipo = TipoSolicitud::find($tipoId);

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
                    ->formatStateUsing(fn ($record) => "{$record->agente->apellido}, {$record->agente->nombre}")
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
                            $indicators[] = 'Desde: '.Carbon::parse($data['desde'])->format('d/m/Y');
                        }

                        if ($data['hasta'] ?? null) {
                            $indicators[] = 'Hasta: '.Carbon::parse($data['hasta'])->format('d/m/Y');
                        }

                        return $indicators;
                    }),
            ])
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
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    // ── Lógica de Aprobación / Rechazo ─────────────────────────

    protected static function procesarAprobacion(Solicitud $record, string $estado, ?string $observacion): void
    {
        $user = Auth::user();

        if ($user === null) {
            return;
        }

        /** @var WorkflowSolicitudesService $workflow */
        $workflow = app(WorkflowSolicitudesService::class);
        $workflow->resolver($record, $user, $estado, $observacion);
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
