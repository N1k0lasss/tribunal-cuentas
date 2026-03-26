<?php

namespace App\Filament\Admin\Resources\Solicituds;

use App\Filament\Admin\Resources\Solicituds\Pages\CreateSolicitud;
use App\Filament\Admin\Resources\Solicituds\Pages\EditSolicitud;
use App\Filament\Admin\Resources\Solicituds\Pages\ListSolicituds;
use App\Models\Solicitud;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class SolicitudResource extends Resource
{
    protected static ?string $model = Solicitud::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Solicitudes';

    protected static ?string $navigationGroup = 'Licencias';

    protected static ?string $pluralModelLabel = 'Solicitudes';

    protected static ?string $modelLabel = 'Solicitud';

    public static function form(Form $form): Form
    {
        return $form
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
                    ->afterStateUpdated(fn ($set) => $set('tipo_solicitud_id', null)),

                Select::make('tipo_solicitud_id')
                    ->label('Régimen Legal Específico')
                    ->options(fn ($get) => \App\Models\TipoSolicitud::where('categoria', $get('categoria'))->pluck('nombre', 'id'))
                    ->live()
                    ->required(),

                DatePicker::make('fecha_solicitud')
                    ->label('Fecha de Solicitud')
                    ->default(now())
                    ->required(),

                // Fechas (Licencias por Días)
                DatePicker::make('fecha_inicio')
                    ->label('Fecha Inicio / Fecha de la Franquicia')
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($set, $get, $state) {
                        $set('dias_solicitados', \App\Filament\Admin\Resources\Solicituds\SolicitudResource::calcularDias($get('tipo_solicitud_id'), $state, $get('fecha_fin')));
                    }),

                DatePicker::make('fecha_fin')
                    ->label('Fecha Fin')
                    ->required(fn ($get) => \App\Models\TipoSolicitud::find($get('tipo_solicitud_id'))?->unidad_medida === 'dias')
                    ->visible(fn ($get) => \App\Models\TipoSolicitud::find($get('tipo_solicitud_id'))?->unidad_medida === 'dias' || !\App\Models\TipoSolicitud::find($get('tipo_solicitud_id')))
                    ->live()
                    ->afterStateUpdated(function ($set, $get, $state) {
                        $set('dias_solicitados', \App\Filament\Admin\Resources\Solicituds\SolicitudResource::calcularDias($get('tipo_solicitud_id'), $get('fecha_inicio'), $state));
                    }),

                // Horas (Franquicias)
                TimePicker::make('hora_inicio')
                    ->label('Hora de Salida')
                    ->required(fn ($get) => \App\Models\TipoSolicitud::find($get('tipo_solicitud_id'))?->unidad_medida === 'horas')
                    ->visible(fn ($get) => \App\Models\TipoSolicitud::find($get('tipo_solicitud_id'))?->unidad_medida === 'horas'),

                TimePicker::make('hora_fin')
                    ->label('Hora de Regreso')
                    ->required(fn ($get) => \App\Models\TipoSolicitud::find($get('tipo_solicitud_id'))?->unidad_medida === 'horas')
                    ->visible(fn ($get) => \App\Models\TipoSolicitud::find($get('tipo_solicitud_id'))?->unidad_medida === 'horas'),

                TextInput::make('dias_solicitados')
                    ->label('Días Solicitados')
                    ->numeric()
                    ->readOnly()
                    ->helperText('Se calcula automáticamente según el régimen legal.')
                    ->visible(fn ($get) => \App\Models\TipoSolicitud::find($get('tipo_solicitud_id'))?->unidad_medida === 'dias' || !\App\Models\TipoSolicitud::find($get('tipo_solicitud_id'))),

                TextInput::make('motivo')
                    ->label('Motivo / Observaciones')
                    ->maxLength(100),

                // Notice de Obligatoriedad de adjunto
                Placeholder::make('requiere_adjunto_info')
                    ->label('Atención')
                    ->content('Este tipo de solicitud requiere subir documentación respaldatoria obligatoriamente.')
                    ->visible(fn ($get) => \App\Models\TipoSolicitud::find($get('tipo_solicitud_id'))?->requiere_adjunto === true),

                Select::make('estado')
                    ->label('Estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'en_revision' => 'En Revisión (Reconocimiento Médico)',
                        'aprobado' => 'Aprobado',
                        'rechazado' => 'Rechazado',
                    ])
                    ->default('pendiente')
                    ->required(),
            ]);
    }

    public static function calcularDias($tipoId, $fechaInicio, $fechaFin)
    {
        if (!$tipoId || !$fechaInicio || !$fechaFin) {
            return null;
        }

        $tipo = \App\Models\TipoSolicitud::find($tipoId);

        if (!$tipo || $tipo->unidad_medida !== 'dias') {
            return null;
        }

        $inicio = \Carbon\Carbon::parse($fechaInicio);
        $fin = \Carbon\Carbon::parse($fechaFin);

        if ($fin->lessThan($inicio)) {
            return 0; // Fechas invalidas
        }

        if ($tipo->tipo_dias === 'habiles') {
            return $inicio->diffInDaysFiltered(function (\Carbon\Carbon $date) {
                return !$date->isWeekend(); // Se puede mejorar para incluir feriados luego
            }, $fin) + 1; // +1 porque diffInDaysFiltered no cuenta el primer día si es el mismo
        }

        // Días corridos
        return $inicio->diffInDays($fin) + 1;
    }

    public static function table(Table $table): Table
    {
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
                        'warning' => 'pendiente',
                        'success' => 'aprobado',
                        'danger' => 'rechazado',
                    ]),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'aprobado' => 'Aprobado',
                        'rechazado' => 'Rechazado',
                    ]),
                SelectFilter::make('tipo_solicitud_id')
                    ->label('Régimen Legal')
                    ->relationship('tipoSolicitud', 'nombre'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $usuarioEsAdministrador = Auth::user()->hasRole('administrador');

        return parent::getEloquentQuery()
            ->when(!$usuarioEsAdministrador, function ($query) {
                return $query->where('agente_id', Auth::id());
            });
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
            'index' => ListSolicituds::route('/'),
            'create' => CreateSolicitud::route('/create'),
            'edit' => EditSolicitud::route('/{record}/edit'),
        ];
    }
}
