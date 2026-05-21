<?php

namespace App\Filament\Admin\Widgets;

use App\Filament\Admin\Resources\Solicituds\SolicitudResource;
use App\Models\Solicitud;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Actions\Action;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class SolicitudesPendientesWidget extends BaseWidget
{
    protected static ?int $sort = 2; // Debajo de las métricas principales
    
    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Acción Rápida: Solicitudes Pendientes';

    public static function canView(): bool
    {
        return Auth::user()?->hasAnyRole(['administrador', 'jefatura', 'secretaria_general', 'presidencia']) ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $user = Auth::user();
                $query = Solicitud::query()
                    ->whereIn('estado', ['pendiente', 'en_revision'])
                    ->with(['agente.area', 'tipoSolicitud', 'aprobaciones']);

                if ($user->hasRole('administrador')) {
                    return $query;
                }

                if ($user->hasRole('jefatura')) {
                    // Jefatura: solicitudes que no tienen ninguna aprobación todavía
                    return $query->whereDoesntHave('aprobaciones');
                }

                if ($user->hasRole('secretaria_general')) {
                    // Secretaría: tiene aprobación de jefatura pero no de secretaría
                    return $query->whereHas('aprobaciones', fn($q) => $q->where('etapa', 'jefatura')->where('estado', 'aprobado'))
                                 ->whereDoesntHave('aprobaciones', fn($q) => $q->where('etapa', 'secretaria'));
                }

                if ($user->hasRole('presidencia')) {
                    // Presidencia: tiene aprobación de secretaría pero no de presidencia (dirección)
                    return $query->whereHas('aprobaciones', fn($q) => $q->where('etapa', 'secretaria')->where('estado', 'aprobado'))
                                 ->whereDoesntHave('aprobaciones', fn($q) => $q->where('etapa', 'direccion'));
                }

                return $query;
            })
            ->columns([
                Tables\Columns\TextColumn::make('agente.apellido')
                    ->label('Agente')
                    ->formatStateUsing(fn ($record) => "{$record->agente->nombre} {$record->agente->apellido}")
                    ->description(fn ($record) => $record->agente->legajo ? "Legajo: {$record->agente->legajo}" : null)
                    ->searchable(['nombre', 'apellido']),
                
                Tables\Columns\TextColumn::make('agente.area.nombre')
                    ->label('Área')
                    ->sortable()
                    ->color('gray'),
                    
                Tables\Columns\TextColumn::make('tipoSolicitud.nombre')
                    ->label('Licencia')
                    ->badge()
                    ->color('gray'),
                
                Tables\Columns\TextColumn::make('fecha_inicio')
                    ->label('Desde')
                    ->date('d/m/Y')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('fecha_fin')
                    ->label('Hasta')
                    ->date('d/m/Y')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('dias_solicitados')
                    ->label('Días')
                    ->alignCenter()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('etapa_actual')
                    ->label('Etapa Actual')
                    ->state(fn ($record) => match ($record->etapaActual()) {
                        'jefatura' => 'Jefatura',
                        'secretaria' => 'Secretaría Gral.',
                        'direccion' => 'Presidencia',
                        default => 'Finalizado',
                    })
                    ->badge()
                    ->color('info'),
            ])
            ->actions([
                Action::make('aprobar')
                    ->label('Aprobar')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Aprobar Solicitud')
                    ->modalDescription('¿Está seguro de que desea aprobar esta licencia?')
                    ->form([
                        Textarea::make('observacion')
                            ->label('Observación (opcional)')
                            ->maxLength(500),
                    ])
                    ->action(function (Solicitud $record, array $data) {
                        SolicitudResource::procesarAprobacion($record, 'aprobado', $data['observacion'] ?? null);

                        Notification::make()
                            ->success()
                            ->title('Solicitud aprobada')
                            ->body("La solicitud de {$record->agente->nombre} fue procesada.")
                            ->send();
                    })
                    ->visible(fn (Solicitud $record) => Auth::user()?->can('aprobar', $record)),
                    
                Action::make('rechazar')
                    ->label('Rechazar')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Rechazar Solicitud')
                    ->modalDescription('¿Por qué motivo rechaza esta licencia?')
                    ->form([
                        Textarea::make('observacion')
                            ->label('Motivo del rechazo')
                            ->required()
                            ->maxLength(500),
                    ])
                    ->action(function (Solicitud $record, array $data) {
                        SolicitudResource::procesarAprobacion($record, 'rechazado', $data['observacion']);

                        Notification::make()
                            ->danger()
                            ->title('Solicitud rechazada')
                            ->send();
                    })
                    ->visible(fn (Solicitud $record) => Auth::user()?->can('rechazar', $record)),
            ])
            ->emptyStateHeading('Sin pendientes')
            ->emptyStateDescription('No hay solicitudes esperando revisión en este momento.')
            ->emptyStateIcon('heroicon-o-check-badge');
    }
}
