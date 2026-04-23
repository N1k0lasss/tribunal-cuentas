<?php

namespace App\Filament\Admin\Resources\Resolucions\Pages;

use App\Filament\Admin\Resources\Resolucions\ResolucionResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class CreateResolucion extends CreateRecord
{
    protected static string $resource = ResolucionResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     *
     * @throws ValidationException
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! Schema::hasColumns('resolucions', ['gde_expediente', 'gde_documento'])) {
            throw ValidationException::withMessages([
                'numero' => 'Falta actualizar la base de datos. Ejecutá "php artisan migrate" para habilitar los campos GDE de Resoluciones.',
            ]);
        }

        return $data;
    }
}
