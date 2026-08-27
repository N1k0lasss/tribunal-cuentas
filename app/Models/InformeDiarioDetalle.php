<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $informe_diario_id
 * @property int $agente_id
 * @property string $tipo_novedad
 * @property int|null $solicitud_id
 * @property int|null $tipo_solicitud_id
 * @property string|null $hora_llegada
 * @property int|null $minutos_tardanza
 * @property string|null $observaciones
 * @property-read InformeDiario $informeDiario
 * @property-read Agente $agente
 * @property-read Solicitud|null $solicitud
 * @property-read TipoSolicitud|null $tipoSolicitud
 */
class InformeDiarioDetalle extends Model
{
    use HasFactory;

    protected $table = 'informe_diario_detalles';

    protected $fillable = [
        'informe_diario_id',
        'agente_id',
        'tipo_novedad',
        'solicitud_id',
        'tipo_solicitud_id',
        'hora_llegada',
        'minutos_tardanza',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'minutos_tardanza' => 'integer',
        ];
    }

    public function informeDiario(): BelongsTo
    {
        return $this->belongsTo(InformeDiario::class, 'informe_diario_id');
    }

    public function agente(): BelongsTo
    {
        return $this->belongsTo(Agente::class, 'agente_id');
    }

    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(Solicitud::class, 'solicitud_id');
    }

    public function tipoSolicitud(): BelongsTo
    {
        return $this->belongsTo(TipoSolicitud::class, 'tipo_solicitud_id');
    }
}
