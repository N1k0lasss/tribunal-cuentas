<?php

namespace App\Filament\Admin\Resources\Solicituds\Pages;

use App\Filament\Admin\Resources\Solicituds\SolicitudResource;
use App\Models\Solicitud;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSolicitud extends EditRecord
{
    protected static string $resource = SolicitudResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn () => $this->record->puedeSerEditada()),
        ];
    }

    /**
     * Bloquear el acceso a edición si la solicitud ya está en flujo.
     */
    protected function authorizeAccess(): void
    {
        parent::authorizeAccess();

        /** @var Solicitud $record */
        $record = $this->getRecord();

        if (! $record->puedeSerEditada()) {
            abort(403, 'Esta solicitud ya está en proceso de aprobación y no puede ser editada.');
        }
    }
}
