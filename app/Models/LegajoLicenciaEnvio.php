<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $solicitud_id
 * @property int $documento_id
 * @property string $dni
 * @property int|null $legajo_id
 * @property array<string, mixed> $datos
 * @property string $estado
 * @property int $intentos
 * @property Carbon|null $proximo_intento_at
 * @property Carbon|null $iniciado_at
 * @property Carbon|null $enviado_at
 * @property int|null $http_status
 * @property string|null $ultimo_error
 * @property-read Solicitud $solicitud
 * @property-read Documento $documento
 */
class LegajoLicenciaEnvio extends Model
{
    public const ESTADO_PENDIENTE = 'pendiente';

    public const ESTADO_PROCESANDO = 'procesando';

    public const ESTADO_ENVIADO = 'enviado';

    public const ESTADO_ERROR = 'error';

    public const ESTADO_CONFLICTO = 'conflicto';

    /**
     * El tipo de solicitud no tiene un equivalente configurado en Legajos.
     * Se registra para auditoría, pero no se envía ni se reintenta.
     */
    public const ESTADO_NO_APLICA = 'no_aplica';

    protected $fillable = [
        'solicitud_id',
        'documento_id',
        'dni',
        'legajo_id',
        'datos',
        'estado',
        'intentos',
        'proximo_intento_at',
        'iniciado_at',
        'enviado_at',
        'http_status',
        'ultimo_error',
    ];

    protected function casts(): array
    {
        return [
            'datos' => 'array',
            'intentos' => 'integer',
            'legajo_id' => 'integer',
            'proximo_intento_at' => 'datetime',
            'iniciado_at' => 'datetime',
            'enviado_at' => 'datetime',
        ];
    }

    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(Solicitud::class);
    }

    public function documento(): BelongsTo
    {
        return $this->belongsTo(Documento::class);
    }
}
