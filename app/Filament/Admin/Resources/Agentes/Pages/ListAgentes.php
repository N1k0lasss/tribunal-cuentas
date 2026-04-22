<?php

namespace App\Filament\Admin\Resources\Agentes\Pages;

use App\Filament\Admin\Resources\Agentes\AgenteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAgentes extends ListRecords
{
    protected static string $resource = AgenteResource::class;

    protected ?string $heading = 'Agentes';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nuevo agente')
                ->color('warning')
                ->icon('heroicon-o-plus'),
        ];
    }
}
