<?php

namespace App\Filament\Admin\Pages;

use App\Models\Agente;
use App\Models\Solicitud;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Pages\Page;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

class HistorialSolicitudes extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.admin.pages.historial-solicitudes';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'Historial';

    protected static string|\UnitEnum|null $navigationGroup = 'Licencias';

    protected static ?string $title = 'Historial de Solicitudes';

    protected static ?string $slug = 'historial-solicitudes';

    protected static ?int $navigationSort = 10;

    public function table(Table $table): Table
    {
        $user = Auth::user();
        $esAgente = $user?->hasRole('agente');

        return $table
            ->query(
                Solicitud::query()
                    ->with(['agente', 'tipoSolicitud', 'aprobaciones.aprobador'])
                    ->when($esAgente, function (Builder $query) use ($user) {
                        $query->where('agente_id', $user->agente?->id);
                    })
            )
            ->columns([
                TextColumn::make('agente.apellido')
                    ->label('Agente')
                    ->formatStateUsing(fn ($record) => "{$record->agente->nombre} {$record->agente->apellido}")
                    ->description(fn ($record) => $record->agente->legajo ?? null)
                    ->searchable(['agentes.apellido', 'agentes.nombre'])
                    ->sortable()
                    ->weight(FontWeight::SemiBold)
                    ->icon('heroicon-o-user')
                    ->visible(fn () => ! $esAgente),
                TextColumn::make('tipoSolicitud.nombre')
                    ->label('Tipo de Licencia')
                    ->sortable()
                    ->wrap()
                    ->icon('heroicon-o-document-text')
                    ->color('gray'),
                TextColumn::make('fecha_solicitud')
                    ->label('Fecha Solicitud')
                    ->date('d/m/Y')
                    ->sortable()
                    ->description(fn ($record) => $record->fecha_solicitud?->diffForHumans() ?? null)
                    ->color('gray'),
                TextColumn::make('fecha_inicio')
                    ->label('Desde')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color('gray'),
                TextColumn::make('fecha_fin')
                    ->label('Hasta')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color('gray'),
                TextColumn::make('dias_solicitados')
                    ->label('Días')
                    ->sortable()
                    ->alignCenter()
                    ->weight(FontWeight::Bold)
                    ->placeholder('—'),
                TextColumn::make('estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'pendiente' => 'Pendiente',
                        'en_revision' => 'En Revisión',
                        'aprobado' => 'Aprobado',
                        'rechazado' => 'Rechazado',
                        default => ucfirst($state),
                    })
                    ->colors([
                        'warning' => Solicitud::ESTADO_PENDIENTE,
                        'info' => Solicitud::ESTADO_EN_REVISION,
                        'success' => Solicitud::ESTADO_APROBADO,
                        'danger' => Solicitud::ESTADO_RECHAZADO,
                    ])
                    ->icon(fn (string $state) => match ($state) {
                        'pendiente' => 'heroicon-o-clock',
                        'en_revision' => 'heroicon-o-magnifying-glass',
                        'aprobado' => 'heroicon-o-check-circle',
                        'rechazado' => 'heroicon-o-x-circle',
                        default => null,
                    }),
            ])
            ->filters([
                SelectFilter::make('anio')
                    ->label('Año')
                    ->options(function () {
                        $primerAnio = Solicitud::min('fecha_solicitud');
                        $primerAnio = $primerAnio ? (int) date('Y', strtotime($primerAnio)) : (int) date('Y');
                        $anioActual = (int) date('Y');

                        $anios = [];
                        for ($y = $anioActual; $y >= $primerAnio; $y--) {
                            $anios[(string) $y] = (string) $y;
                        }

                        return $anios;
                    })
                    ->default((string) date('Y'))
                    ->query(function (Builder $query, array $data) {
                        if (! empty($data['value'])) {
                            $query->whereYear('fecha_solicitud', $data['value']);
                        }
                    }),
                SelectFilter::make('estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'en_revision' => 'En Revisión',
                        'aprobado' => 'Aprobado',
                        'rechazado' => 'Rechazado',
                    ]),
                SelectFilter::make('agente_id')
                    ->label('Agente')
                    ->options(function () {
                        return Agente::query()
                            ->orderBy('apellido')
                            ->get()
                            ->mapWithKeys(fn (Agente $agente) => [
                                $agente->id => "{$agente->apellido}, {$agente->nombre}",
                            ]);
                    })
                    ->searchable()
                    ->visible(fn () => ! $esAgente),
            ])
            ->defaultSort('fecha_solicitud', 'desc')
            ->recordAction('ver_detalle')
            ->recordActions([
                Action::make('ver_detalle')
                    ->label('')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->tooltip('Ver detalle de la solicitud')
                    ->modalHeading(fn (Solicitud $record) => "Solicitud #{$record->id}")
                    ->modalDescription(fn (Solicitud $record) => "Enviada el {$record->fecha_solicitud?->format('d/m/Y')} por {$record->agente->nombre} {$record->agente->apellido}")
                    ->modalIcon('heroicon-o-document-magnifying-glass')
                    ->modalWidth('3xl')
                    ->modalSubmitAction(false)
                    ->infolist([
                        Section::make('Datos de la Solicitud')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('agente.apellido')
                                            ->label('Agente')
                                            ->icon('heroicon-o-user')
                                            ->formatStateUsing(fn ($record) => "{$record->agente->nombre} {$record->agente->apellido}"),
                                        TextEntry::make('tipoSolicitud.nombre')
                                            ->label('Tipo de Licencia')
                                            ->icon('heroicon-o-document-text'),
                                        TextEntry::make('estado')
                                            ->badge()
                                            ->formatStateUsing(fn (string $state) => match ($state) {
                                                'pendiente' => 'Pendiente',
                                                'en_revision' => 'En Revisión',
                                                'aprobado' => 'Aprobado',
                                                'rechazado' => 'Rechazado',
                                                default => ucfirst($state),
                                            })
                                            ->colors([
                                                'warning' => 'pendiente',
                                                'success' => 'aprobado',
                                                'danger' => 'rechazado',
                                                'info' => 'en_revision',
                                            ]),
                                    ]),
                                Grid::make(4)
                                    ->schema([
                                        TextEntry::make('fecha_solicitud')
                                            ->label('Fecha de Solicitud')
                                            ->icon('heroicon-o-calendar')
                                            ->date('d/m/Y'),
                                        TextEntry::make('fecha_inicio')
                                            ->label('Desde')
                                            ->icon('heroicon-o-arrow-right-circle')
                                            ->date('d/m/Y'),
                                        TextEntry::make('fecha_fin')
                                            ->label('Hasta')
                                            ->icon('heroicon-o-arrow-left-circle')
                                            ->date('d/m/Y'),
                                        TextEntry::make('dias_solicitados')
                                            ->label('Días Solicitados')
                                            ->icon('heroicon-o-hashtag'),
                                    ]),
                                TextEntry::make('motivo')
                                    ->label('Motivo / Observaciones')
                                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                                    ->placeholder('Sin motivo registrado')
                                    ->columnSpanFull(),
                            ]),
                        Section::make('Flujo de Aprobación')
                            ->icon('heroicon-o-check-badge')
                            ->schema([
                                RepeatableEntry::make('aprobaciones')
                                    ->label('')
                                    ->schema([
                                        Grid::make(4)
                                            ->schema([
                                                TextEntry::make('etapa')
                                                    ->label('Etapa')
                                                    ->icon('heroicon-o-flag')
                                                    ->formatStateUsing(fn (string $state) => match ($state) {
                                                        'jefatura' => 'Jefatura',
                                                        'secretaria' => 'Secretaría General',
                                                        'direccion' => 'Presidencia',
                                                        default => $state,
                                                    }),
                                                TextEntry::make('estado')
                                                    ->label('Estado')
                                                    ->badge()
                                                    ->formatStateUsing(fn (string $state) => match ($state) {
                                                        'pendiente' => 'Pendiente',
                                                        'aprobado' => 'Aprobado',
                                                        'rechazado' => 'Rechazado',
                                                        default => ucfirst($state),
                                                    })
                                                    ->colors([
                                                        'warning' => 'pendiente',
                                                        'success' => 'aprobado',
                                                        'danger' => 'rechazado',
                                                    ]),
                                                TextEntry::make('aprobador.apellido')
                                                    ->label('Responsable')
                                                    ->icon('heroicon-o-user-circle')
                                                    ->formatStateUsing(fn ($record) => $record->aprobador
                                                        ? "{$record->aprobador->nombre} {$record->aprobador->apellido}"
                                                        : '—'),
                                                TextEntry::make('fecha')
                                                    ->label('Fecha')
                                                    ->icon('heroicon-o-calendar-days')
                                                    ->date('d/m/Y'),
                                            ]),
                                        TextEntry::make('observacion')
                                            ->label('Observación')
                                            ->icon('heroicon-o-chat-bubble-bottom-center-text')
                                            ->placeholder('Sin observación')
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->visible(fn ($record) => $record->aprobaciones->isNotEmpty()),
                    ]),
            ])
            ->striped()
            ->paginated([10, 25, 50])
            ->emptyStateHeading('Sin solicitudes registradas')
            ->emptyStateDescription('Tu historial de licencias está vacío o no hay registros que coincidan con los filtros de búsqueda actuales.')
            ->emptyStateIcon('heroicon-o-folder-open');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportar_pdf')
                ->label('Exportar PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->action(function () {
                    $user = Auth::user();
                    $esAgente = $user?->hasRole('agente');

                    // Reproducir la query de la tabla con los filtros activos
                    $query = Solicitud::query()
                        ->with(['agente', 'tipoSolicitud', 'aprobaciones.aprobador'])
                        ->when($esAgente, function (Builder $query) use ($user) {
                            $query->where('agente_id', $user->agente?->id);
                        });

                    // Aplicar filtros activos de la tabla
                    $filterState = $this->tableFilters;

                    if (! empty($filterState['anio']['value'])) {
                        $query->whereYear('fecha_solicitud', $filterState['anio']['value']);
                    }

                    if (! empty($filterState['estado']['value'])) {
                        $query->where('estado', $filterState['estado']['value']);
                    }

                    if (! $esAgente && ! empty($filterState['agente_id']['value'])) {
                        $query->where('agente_id', $filterState['agente_id']['value']);
                    }

                    $solicitudes = $query->orderBy('fecha_solicitud', 'desc')->get();

                    $anioFiltro = $filterState['anio']['value'] ?? date('Y');
                    $agenteFiltro = null;
                    if (! $esAgente && ! empty($filterState['agente_id']['value'])) {
                        $agenteFiltro = Agente::find($filterState['agente_id']['value']);
                    } elseif ($esAgente) {
                        $agenteFiltro = $user->agente;
                    }

                    $pdf = Pdf::loadView('filament.admin.pages.historial-pdf', [
                        'solicitudes' => $solicitudes,
                        'anio' => $anioFiltro,
                        'agente' => $agenteFiltro,
                        'estadoFiltro' => $filterState['estado']['value'] ?? null,
                    ]);

                    $nombreArchivo = 'historial_solicitudes_' . $anioFiltro;
                    if ($agenteFiltro) {
                        $nombreArchivo .= '_' . str_replace(' ', '_', $agenteFiltro->apellido);
                    }
                    $nombreArchivo .= '.pdf';

                    return response()->streamDownload(
                        fn () => print($pdf->output()),
                        $nombreArchivo
                    );
                }),
        ];
    }
}
