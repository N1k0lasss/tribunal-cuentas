<?php

namespace App\Filament\Actions;

use App\Models\Aprobacion;
use App\Models\Solicitud;
use App\Services\DelegacionService;
use App\Services\FirmaDigitalService;
use App\Services\WorkflowSolicitudesService;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FirmarAprobarAction
{
    protected ?Closure $afterFirmar = null;

    public static function make(): static
    {
        return new static;
    }

    public function afterFirmar(Closure $callback): static
    {
        $this->afterFirmar = $callback;

        return $this;
    }

    public function getAction(): Action
    {
        return Action::make('firmarAprobar')
            ->label('Firmar y Aprobar')
            ->icon('heroicon-m-check-badge')
            ->color('success')
            ->button()
            ->requiresConfirmation()
            ->modalHeading('Firma Digital - Aprobar Solicitud')
            ->modalDescription(fn (Solicitud $record) => $this->getModalDescription($record))
            ->form($this->getForm())
            ->action(function (Solicitud $record, array $data) {
                $this->procesarFirmaYAprueba($record, $data);
            })
            ->visible(fn (Solicitud $record) => $this->canFirmar($record));
    }

    protected function getModalDescription(Solicitud $record): string
    {
        $etapa = $record->etapaActual();
        $etapaLabel = match ($etapa) {
            'jefatura' => 'Jefatura',
            'secretaria' => 'Secretaría General',
            'direccion' => 'Presidencia',
            default => 'desconocida',
        };

        return "Está a punto de aprobar y firmar esta solicitud en la etapa de {$etapaLabel}. La firma digital quedará registrada con su identidad.";
    }

    protected function getForm(): array
    {
        return [
            Placeholder::make('info_firma')
                ->label('Información de Firma')
                ->content(function (): string {
                    $user = Auth::user();
                    $agente = $user->agente;

                    return "Firmante: {$agente?->apellido}, {$agente?->nombre} ({$agente?->cargo})\n"
                         .'Fecha: '.now()->format('d/m/Y H:i:s')."\n"
                         .'IP: '.request()->ip();
                })
                ->columnSpanFull(),

            Placeholder::make('declaracion')
                ->label('Declaración')
                ->content('Al hacer click en "Confirmar Firma", usted está firmando digitalmente esta solicitud. '
                    .'Esta acción tiene validez legal y no puede ser revocada.')
                ->columnSpanFull(),

            TextInput::make('pin_firma')
                ->label('PIN de Firma')
                ->password()
                ->required()
                ->helperText('Ingrese su PIN de firma digital (4-6 dígitos)')
                ->minLength(4)
                ->maxLength(6)
                ->regex('/^[0-9]+$/')
                ->validationAttribute('PIN de firma'),

            Textarea::make('observacion')
                ->label('Observación (opcional)')
                ->maxLength(500)
                ->rows(2),

            Hidden::make('firma_base64')
                ->default(''),
        ];
    }

    protected function canFirmar(Solicitud $record): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        $etapa = $record->etapaActual();

        if (! $etapa) {
            return false;
        }

        return app(DelegacionService::class)->puedeActuarEnEtapa($user, $record, $etapa);
    }

    protected function procesarFirmaYAprueba(Solicitud $record, array $data): void
    {
        $firmaService = app(FirmaDigitalService::class);

        DB::transaction(function () use ($record, $data, $firmaService) {
            $etapa = $record->etapaActual();
            $workflowService = app(WorkflowSolicitudesService::class);
            $user = Auth::user();

            if ($user === null) {
                return;
            }

            /** @var Aprobacion $aprobacion */
            $aprobacion = $workflowService->resolver($record, $user, 'aprobado', $data['observacion'] ?? null);

            $firmaService->crearFirma(
                solicitud: $record,
                aprobacion: $aprobacion,
                etapa: $etapa,
                firmaBase64: $data['firma_base64'] ?? null,
                pin: $data['pin_firma'] ?? null,
            );

            $record->load(['aprobaciones', 'firmasDigitales']);
        });

        if ($this->afterFirmar) {
            ($this->afterFirmar)($record);
        }

        Notification::make()
            ->success()
            ->title('Solicitud firmada y aprobada')
            ->body('La firma digital ha sido registrada correctamente. '
                .'IP: '.request()->ip().' | Fecha: '.now()->format('d/m/Y H:i:s'))
            ->send();
    }
}
