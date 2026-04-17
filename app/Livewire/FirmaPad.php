<?php

namespace App\Livewire;

use Livewire\Component;

class FirmaPad extends Component
{
    public string $firmaBase64 = '';

    public string $campoId;

    public int $solicitudId;

    public string $etapa;

    public function mount(string $campoId, int $solicitudId, string $etapa): void
    {
        $this->campoId = $campoId;
        $this->solicitudId = $solicitudId;
        $this->etapa = $etapa;
    }

    public function actualizarFirma(string $base64): void
    {
        $this->firmaBase64 = $base64;
    }

    public function limpiarFirma(): void
    {
        $this->firmaBase64 = '';
    }

    public function render()
    {
        return view('livewire.firma-pad');
    }
}
