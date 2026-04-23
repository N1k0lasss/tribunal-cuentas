<?php

namespace App\Filament\Admin\Resources\Documentos\Pages;

use App\Filament\Admin\Resources\Documentos\DocumentoResource;
use App\Models\Documento;
use App\Services\JustificacionService;
use Filament\Resources\Pages\CreateRecord;

class CreateDocumento extends CreateRecord
{
    protected static string $resource = DocumentoResource::class;

    protected function handleRecordCreation(array $data): Documento
    {
        $documento = Documento::create($data);

        app(JustificacionService::class)->registrarDocumento($documento);

        return $documento;
    }
}
