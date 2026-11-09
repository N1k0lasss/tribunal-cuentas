<?php

namespace App\Filament\Admin\Pages;

use App\Jobs\EnviarLicenciaALegajos;
use App\Models\LegajoLicenciaEnvio;
use App\Models\User;
use App\Services\LegajoLicenciaEnvioService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class LegajoLicenciasEnvios extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.admin.pages.legajo-licencias-envios';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static ?string $navigationLabel = 'Envíos de licencias';

    protected static ?string $title = 'Envíos de licencias a Legajos';

    protected static string|\UnitEnum|null $navigationGroup = 'Configuración';

    protected static ?string $slug = 'envios-licencias-legajos';

    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        $usuario = Auth::user();

        return $usuario instanceof User && $usuario->hasRole('administrador');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(LegajoLicenciaEnvio::query()->with(['solicitud.agente', 'solicitud.tipoSolicitud']))
            ->columns([
                TextColumn::make('solicitud_id')
                    ->label('Solicitud')
                    ->sortable(),
                TextColumn::make('solicitud.agente.apellido')
                    ->label('Agente')
                    ->formatStateUsing(fn (LegajoLicenciaEnvio $record): string => $record->solicitud->agente->apellido.', '.$record->solicitud->agente->nombre),
                TextColumn::make('solicitud.tipoSolicitud.nombre')
                    ->label('Licencia')
                    ->wrap(),
                TextColumn::make('estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        LegajoLicenciaEnvio::ESTADO_PENDIENTE => 'Pendiente',
                        LegajoLicenciaEnvio::ESTADO_PROCESANDO => 'Procesando',
                        LegajoLicenciaEnvio::ESTADO_ENVIADO => 'Enviado',
                        LegajoLicenciaEnvio::ESTADO_ERROR => 'Error',
                        LegajoLicenciaEnvio::ESTADO_CONFLICTO => 'Conflicto (409)',
                        LegajoLicenciaEnvio::ESTADO_NO_APLICA => 'No aplica',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        LegajoLicenciaEnvio::ESTADO_PENDIENTE,
                        LegajoLicenciaEnvio::ESTADO_CONFLICTO => 'warning',
                        LegajoLicenciaEnvio::ESTADO_PROCESANDO => 'info',
                        LegajoLicenciaEnvio::ESTADO_ENVIADO => 'success',
                        LegajoLicenciaEnvio::ESTADO_ERROR => 'danger',
                        LegajoLicenciaEnvio::ESTADO_NO_APLICA => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('intentos')
                    ->label('Intentos')
                    ->alignCenter(),
                TextColumn::make('http_status')
                    ->label('HTTP')
                    ->placeholder('—')
                    ->alignCenter(),
                TextColumn::make('proximo_intento_at')
                    ->label('Próximo intento')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—'),
                TextColumn::make('ultimo_error')
                    ->label('Detalle')
                    ->wrap()
                    ->limit(180)
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        LegajoLicenciaEnvio::ESTADO_PENDIENTE => 'Pendiente',
                        LegajoLicenciaEnvio::ESTADO_PROCESANDO => 'Procesando',
                        LegajoLicenciaEnvio::ESTADO_ENVIADO => 'Enviado',
                        LegajoLicenciaEnvio::ESTADO_ERROR => 'Error',
                        LegajoLicenciaEnvio::ESTADO_CONFLICTO => 'Conflicto (409)',
                        LegajoLicenciaEnvio::ESTADO_NO_APLICA => 'No aplica',
                    ]),
            ])
            ->recordActions([
                Action::make('reintentar')
                    ->label('Reintentar')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Reintentar envío')
                    ->modalDescription(fn (LegajoLicenciaEnvio $record): string => match ($record->estado) {
                        LegajoLicenciaEnvio::ESTADO_CONFLICTO => 'Confirmá que ya resolviste la discrepancia con Legajos (409). Se volverá a enviar el PDF y se reiniciará el ciclo de reintentos.',
                        LegajoLicenciaEnvio::ESTADO_NO_APLICA => 'Se recalculará el tipo equivalente configurado actualmente en el régimen legal. Si sigue vacío, no se enviará.',
                        default => 'Se volverá a enviar el PDF firmado y se reiniciará el ciclo de reintentos.',
                    })
                    ->modalSubmitActionLabel('Reintentar ahora')
                    ->visible(fn (LegajoLicenciaEnvio $record): bool => in_array($record->estado, [
                        LegajoLicenciaEnvio::ESTADO_ERROR,
                        LegajoLicenciaEnvio::ESTADO_CONFLICTO,
                        LegajoLicenciaEnvio::ESTADO_NO_APLICA,
                    ], true))
                    ->action(function (LegajoLicenciaEnvio $record): void {
                        $estadoAnterior = $record->estado;
                        $errorAnterior = $record->ultimo_error;

                        // Un error puede deberse a datos incompletos: se
                        // recalcula el payload desde la solicitud actual antes
                        // de reencolar, para no reenviar los datos viejos.
                        $requiereRecalculo = in_array($estadoAnterior, [
                            LegajoLicenciaEnvio::ESTADO_NO_APLICA,
                            LegajoLicenciaEnvio::ESTADO_ERROR,
                        ], true);

                        if ($requiereRecalculo
                            && ! app(LegajoLicenciaEnvioService::class)->actualizarDesdeSolicitud($record)) {
                            Notification::make()
                                ->title('No se puede reencolar el envío')
                                ->body('El tipo de solicitud no tiene un equivalente configurado en Legajos.')
                                ->warning()
                                ->send();

                            return;
                        }

                        if (! $requiereRecalculo) {
                            $record->update([
                                'estado' => LegajoLicenciaEnvio::ESTADO_PENDIENTE,
                                'intentos' => 0,
                                'proximo_intento_at' => now(),
                                'iniciado_at' => null,
                                'http_status' => null,
                                'ultimo_error' => null,
                            ]);
                        }

                        try {
                            EnviarLicenciaALegajos::dispatch($record->id)->afterCommit();

                            Notification::make()
                                ->title('Envío encolado para reintento')
                                ->success()
                                ->send();
                        } catch (Throwable $exception) {
                            Log::warning('No se pudo encolar el reintento manual del envío de licencia.', [
                                'envio_id' => $record->id,
                                'error' => $exception->getMessage(),
                            ]);

                            Notification::make()
                                ->title('No se pudo encolar el envío')
                                ->body('Quedó pendiente; el planificador lo reintentará automáticamente.')
                                ->warning()
                                ->send();
                        }

                        Log::info('Reintento manual de envío de licencia solicitado.', [
                            'envio_id' => $record->id,
                            'estado_anterior' => $estadoAnterior,
                            'error_anterior' => $errorAnterior,
                            'user_id' => Auth::id(),
                        ]);
                    }),
                Action::make('marcar_enviado')
                    ->label('Marcar enviado')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Marcar el envío como enviado')
                    ->modalDescription('Usalo solo si confirmaste en Legajos que la licencia ya está registrada. No se volverá a enviar el PDF ni se reintentará automáticamente.')
                    ->modalSubmitActionLabel('Sí, ya está registrada')
                    ->visible(fn (LegajoLicenciaEnvio $record): bool => $record->estado === LegajoLicenciaEnvio::ESTADO_CONFLICTO)
                    ->action(function (LegajoLicenciaEnvio $record): void {
                        $statusAnterior = $record->http_status;
                        $errorAnterior = $record->ultimo_error;

                        $record->update([
                            'estado' => LegajoLicenciaEnvio::ESTADO_ENVIADO,
                            'enviado_at' => now(),
                            'proximo_intento_at' => null,
                            'iniciado_at' => null,
                            'http_status' => null,
                            'ultimo_error' => null,
                        ]);

                        Log::info('Envío de licencia marcado como enviado manualmente.', [
                            'envio_id' => $record->id,
                            'solicitud_id' => $record->solicitud_id,
                            'http_status_anterior' => $statusAnterior,
                            'error_anterior' => $errorAnterior,
                            'user_id' => Auth::id(),
                        ]);

                        Notification::make()
                            ->title('Envío marcado como enviado')
                            ->body('Se registró el cierre manual del envío. No se volvió a llamar a Legajos.')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('60s');
    }
}
