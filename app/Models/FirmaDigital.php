<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $solicitud_id
 * @property int $aprobacion_id
 * @property int $agente_id
 * @property string $etapa
 * @property string $hash_documento
 * @property string $ip_firma
 * @property string|null $user_agent
 * @property Carbon $fecha_firma
 * @property string|null $firma_base64
 * @property string|null $pin_hash
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Solicitud $solicitud
 * @property-read Aprobacion $aprobacion
 * @property-read Agente $agente
 */
class FirmaDigital extends Model
{
    use HasFactory;

    protected $table = 'firmas_digitales';

    protected $fillable = [
        'solicitud_id',
        'aprobacion_id',
        'agente_id',
        'etapa',
        'hash_documento',
        'ip_firma',
        'user_agent',
        'fecha_firma',
        'firma_base64',
        'pin_hash',
    ];

    protected function casts(): array
    {
        return [
            'fecha_firma' => 'datetime',
        ];
    }

    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(Solicitud::class);
    }

    public function aprobacion(): BelongsTo
    {
        return $this->belongsTo(Aprobacion::class);
    }

    public function agente(): BelongsTo
    {
        return $this->belongsTo(Agente::class);
    }

    /**
     * Genera un hash único del estado del documento.
     */
    public static function generarHashDocumento(Solicitud $solicitud, string $etapa): string
    {
        $datos = [
            'solicitud_id' => $solicitud->id,
            'etapa' => $etapa,
            'estado' => $solicitud->estado,
            'fecha_inicio' => $solicitud->fecha_inicio?->toDateString(),
            'fecha_fin' => $solicitud->fecha_fin?->toDateString(),
            'dias_solicitados' => $solicitud->dias_solicitados,
            'timestamp' => now()->timestamp,
        ];

        return hash('sha256', json_encode($datos));
    }

    /**
     * Verifica si el documento fue alterado después de la firma.
     */
    public function verificarIntegridad(Solicitud $solicitud): bool
    {
        $hashActual = self::generarHashDocumento($solicitud, $this->etapa);

        // Comparamos solo las partes que deberían ser constantes
        $hashOriginal = $this->hash_documento;

        // Recreamos el hash sin el timestamp
        $datosOriginales = [
            'solicitud_id' => $solicitud->id,
            'etapa' => $this->etapa,
            'estado' => $solicitud->estado,
            'fecha_inicio' => $solicitud->fecha_inicio?->toDateString(),
            'fecha_fin' => $solicitud->fecha_fin?->toDateString(),
            'dias_solicitados' => $solicitud->dias_solicitados,
        ];

        return hash('sha256', json_encode($datosOriginales)) === substr($hashOriginal, 0, 64);
    }
}
