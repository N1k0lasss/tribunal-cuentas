<?php

namespace App\Services;

use App\Models\Aprobacion;
use App\Models\FirmaDigital;
use App\Models\Solicitud;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class FirmaDigitalService
{
    public function crearFirma(
        Solicitud $solicitud,
        Aprobacion $aprobacion,
        string $etapa,
        ?string $firmaBase64 = null,
        ?string $pin = null,
        ?User $user = null
    ): FirmaDigital {
        $user = $user ?? auth()->user();

        $hashDocumento = FirmaDigital::generarHashDocumento($solicitud, $etapa);

        $firma = FirmaDigital::create([
            'solicitud_id' => $solicitud->id,
            'aprobacion_id' => $aprobacion->id,
            'agente_id' => $user->agente?->id ?? $user->id,
            'etapa' => $etapa,
            'hash_documento' => $hashDocumento,
            'ip_firma' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'fecha_firma' => now(),
            'firma_base64' => $firmaBase64,
            'pin_hash' => $pin ? Hash::make($pin) : null,
        ]);

        return $firma;
    }

    public function verificarPin(FirmaDigital $firma, string $pin): bool
    {
        if (! $firma->pin_hash) {
            return true;
        }

        return Hash::check($pin, $firma->pin_hash);
    }

    public function generarTokenVerificacion(FirmaDigital $firma): string
    {
        return Str::random(32);
    }

    public function obtenerResumenFirmas(Solicitud $solicitud): array
    {
        $firmas = $solicitud->firmasDigitales()->with('agente')->get();

        return [
            'completo' => $firmas->count() === count(Solicitud::FLUJO_ETAPAS),
            'total' => count(Solicitud::FLUJO_ETAPAS),
            'firmadas' => $firmas->count(),
            'firmantes' => $firmas->map(fn ($f) => [
                'etapa' => $f->etapa,
                'etapa_label' => $this->getEtapaLabel($f->etapa),
                'agente' => $f->agente ? "{$f->agente->apellido}, {$f->agente->nombre}" : 'N/A',
                'fecha' => $f->fecha_firma->format('d/m/Y H:i'),
                'ip' => $f->ip_firma,
                'hash' => substr($f->hash_documento, 0, 16).'...',
            ])->values()->toArray(),
        ];
    }

    private function getEtapaLabel(string $etapa): string
    {
        return match ($etapa) {
            'jefatura' => 'Jefatura',
            'secretaria' => 'Secretaría General',
            'direccion' => 'Presidencia',
            default => $etapa,
        };
    }
}
