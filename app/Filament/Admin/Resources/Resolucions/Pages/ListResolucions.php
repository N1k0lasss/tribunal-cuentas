<?php

namespace App\Filament\Admin\Resources\Resolucions\Pages;

use App\Filament\Admin\Resources\Resolucions\ResolucionResource;
use App\Models\Documento;
use App\Models\Resolucion;
use App\Services\ResolucionDiariaService;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListResolucions extends ListRecords
{
    protected static string $resource = ResolucionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generar_resolucion_dia')
                ->label('Generar resolución del día')
                ->icon('heroicon-o-document-plus')
                ->color('primary')
                ->visible(fn (): bool => Gate::allows('create', Resolucion::class))
                ->authorize('create', Resolucion::class)
                ->disabled(fn (): bool => $this->resolucionDelDia()?->tienePdfFirmado() ?? false)
                ->tooltip(fn (): ?string => $this->resolucionDelDia()?->tienePdfFirmado()
                    ? 'La resolución del día ya tiene un PDF firmado y no puede regenerarse.'
                    : null)
                ->form([
                    TextInput::make('numero')
                        ->label('Número de resolución')
                        ->required()
                        ->maxLength(50),
                    Textarea::make('detalle')
                        ->label('Detalle (opcional)')
                        ->maxLength(65535)
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->action(function (array $data): ?StreamedResponse {
                    Gate::authorize('create', Resolucion::class);

                    $hoy = ResolucionDiariaService::hoy();
                    $solicitudes = app(ResolucionDiariaService::class)->solicitudesDelDia($hoy);

                    if ($solicitudes->isEmpty()) {
                        Notification::make()
                            ->warning()
                            ->title('Sin licencias aprobadas hoy')
                            ->body('No hay licencias con aprobación final registrada en la fecha de hoy.')
                            ->send();

                        return null;
                    }

                    $resolucion = DB::transaction(function () use ($data, $hoy, $solicitudes): Resolucion|false {
                        $resolucion = Resolucion::query()
                            ->diarias()
                            ->whereDate('fecha_emision', $hoy)
                            ->lockForUpdate()
                            ->first();

                        if ($resolucion?->tienePdfFirmado()) {
                            return false;
                        }

                        if ($resolucion === null) {
                            $resolucion = new Resolucion;
                            $resolucion->solicitud_id = null;
                        }

                        $resolucion->numero = $data['numero'];
                        $resolucion->fecha_emision = $hoy;
                        $resolucion->detalle = $data['detalle'] ?? null;
                        $resolucion->save();

                        $resolucion->solicitudes()->sync($solicitudes->pluck('id')->all());

                        return $resolucion;
                    });

                    if ($resolucion === false) {
                        Notification::make()
                            ->danger()
                            ->title('Resolución ya firmada')
                            ->body('No se puede regenerar la resolución del día porque ya tiene un PDF firmado.')
                            ->send();

                        return null;
                    }

                    $this->resetTable();

                    Notification::make()
                        ->success()
                        ->title('Resolución del día generada')
                        ->body('Se generó la resolución con '.$solicitudes->count().' licencia(s).')
                        ->send();

                    return app(ResolucionDiariaService::class)->descargar($resolucion);
                }),

            Action::make('subir_resolucion_firmada_dia')
                ->label('Subir resolución firmada')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->visible(fn (): bool => Gate::allows('subirPdfFirmadoDelDia', Resolucion::class)
                    || Gate::allows('reemplazarPdfFirmadoDelDia', Resolucion::class))
                ->authorize(fn (): bool => $this->puedeSubirOReemplazarPdfFirmadoDelDia())
                ->disabled(fn (): bool => $this->resolucionDelDia() === null)
                ->tooltip(function (): ?string {
                    $resolucion = $this->resolucionDelDia();

                    if ($resolucion === null) {
                        return 'Primero generá la resolución del día.';
                    }

                    if ($resolucion->tienePdfFirmado()
                        && ! Gate::allows('reemplazarPdfFirmadoDelDia', Resolucion::class)) {
                        return 'Ya existe un PDF firmado. Solo un administrador puede reemplazarlo.';
                    }

                    return null;
                })
                ->requiresConfirmation(fn (): bool => $this->resolucionDelDia()?->tienePdfFirmado() ?? false)
                ->modalHeading(fn (): string => $this->resolucionDelDia()?->tienePdfFirmado()
                    ? 'Reemplazar PDF firmado'
                    : 'Subir PDF firmado')
                ->modalDescription(fn (): ?string => $this->resolucionDelDia()?->tienePdfFirmado()
                    ? 'Ya existe un PDF firmado. Al reemplazarlo, el archivo anterior se eliminará y la operación quedará registrada en la auditoría.'
                    : null)
                ->modalSubmitActionLabel(fn (): string => $this->resolucionDelDia()?->tienePdfFirmado()
                    ? 'Reemplazar PDF'
                    : 'Subir PDF')
                ->form([
                    FileUpload::make('archivo')
                        ->label('PDF firmado del día')
                        ->acceptedFileTypes(['application/pdf'])
                        ->disk('local')
                        ->directory('documentos/firmados')
                        ->maxSize(10240)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    abort_unless($this->puedeSubirOReemplazarPdfFirmadoDelDia(), 403);

                    $hoy = ResolucionDiariaService::hoy();
                    $path = is_array($data['archivo']) ? ($data['archivo'][0] ?? null) : $data['archivo'];

                    if (! $path) {
                        return;
                    }

                    $oldPath = null;
                    $reemplazo = false;

                    try {
                        DB::transaction(function () use ($hoy, $path, &$oldPath, &$reemplazo): void {
                            $resolucion = Resolucion::query()
                                ->diarias()
                                ->whereDate('fecha_emision', $hoy)
                                ->lockForUpdate()
                                ->first();

                            if ($resolucion === null) {
                                throw new \DomainException('Primero generá la resolución del día antes de subir el PDF firmado.');
                            }

                            $documento = $resolucion->documentos()
                                ->where('tipo_documento', Documento::TIPO_RESOLUCION_FIRMADA)
                                ->lockForUpdate()
                                ->first();

                            if ($documento !== null
                                && ! Gate::allows('reemplazarPdfFirmadoDelDia', Resolucion::class)) {
                                throw new \DomainException('Ya existe un PDF firmado. Solo un administrador puede reemplazarlo.');
                            }

                            $reemplazo = $documento !== null;

                            if ($documento !== null) {
                                $oldPath = $documento->ruta;

                                Documento::conPermisoDocumentoSistema(function () use ($documento, $path, $hoy): void {
                                    $documento->update([
                                        'agente_id' => Auth::user()?->agente?->id,
                                        'nombre_archivo' => 'Resolución del día '.$hoy->format('d/m/Y').' firmada',
                                        'ruta' => $path,
                                        'descripcion' => 'Resolución del día firmada digitalmente.',
                                    ]);
                                });

                                activity()
                                    ->performedOn($resolucion)
                                    ->withProperties([
                                        'documento_id' => $documento->id,
                                        'ruta_anterior' => $oldPath,
                                        'ruta_nueva' => $path,
                                    ])
                                    ->log('PDF firmado reemplazado por administrador');

                                return;
                            }

                            Documento::conPermisoDocumentoSistema(function () use ($resolucion, $path, $hoy): void {
                                $resolucion->documentos()->create([
                                    'tipo_documento' => Documento::TIPO_RESOLUCION_FIRMADA,
                                    'solicitud_id' => null,
                                    'agente_id' => Auth::user()?->agente?->id,
                                    'nombre_archivo' => 'Resolución del día '.$hoy->format('d/m/Y').' firmada',
                                    'ruta' => $path,
                                    'descripcion' => 'Resolución del día firmada digitalmente.',
                                ]);
                            });
                        });
                    } catch (\DomainException $exception) {
                        Storage::disk('local')->delete($path);

                        Notification::make()
                            ->danger()
                            ->title('No se pudo subir el PDF firmado')
                            ->body($exception->getMessage())
                            ->send();

                        return;
                    } catch (\Throwable $exception) {
                        Storage::disk('local')->delete($path);

                        throw $exception;
                    }

                    if ($oldPath !== null
                        && $oldPath !== $path
                        && Documento::rutaValidaPath($oldPath)) {
                        Storage::disk('local')->delete($oldPath);
                    }

                    $this->resetTable();

                    Notification::make()
                        ->success()
                        ->title($reemplazo ? 'PDF firmado reemplazado' : 'PDF firmado subido')
                        ->body($reemplazo
                            ? 'El PDF firmado anterior fue reemplazado correctamente.'
                            : 'La resolución del día firmada se guardó correctamente.')
                        ->send();
                }),
        ];
    }

    private function puedeSubirOReemplazarPdfFirmadoDelDia(): bool
    {
        return Gate::allows('subirPdfFirmadoDelDia', Resolucion::class)
            || Gate::allows('reemplazarPdfFirmadoDelDia', Resolucion::class);
    }

    private function resolucionDelDia(): ?Resolucion
    {
        return Resolucion::query()
            ->diarias()
            ->whereDate('fecha_emision', ResolucionDiariaService::hoy())
            ->with('pdfFirmado')
            ->first();
    }
}
