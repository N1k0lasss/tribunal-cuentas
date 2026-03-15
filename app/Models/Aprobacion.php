<?php

namespace App\Models;

use Database\Factories\AprobacionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $solicitud_id
 * @property int $aprobado_por
 * @property string $etapa
 * @property string $estado
 * @property string|null $observacion
 * @property Carbon $fecha
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Solicitud $solicitud
 * @property-read Agente $aprobador
 */
class Aprobacion extends Model
{
    /** @use HasFactory<AprobacionFactory> */
    use HasFactory;

    protected $table = 'aprobacions';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'solicitud_id',
        'aprobado_por',
        'etapa',
        'estado',
        'observacion',
        'fecha',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha' => 'date',
        ];
    }

    /**
     * Get the solicitud that owns the aprobacion.
     *
     * @return BelongsTo<Solicitud, $this>
     */
    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(Solicitud::class);
    }

    /**
     * Get the agente who approved the solicitud.
     *
     * @return BelongsTo<Agente, $this>
     */
    public function aprobador(): BelongsTo
    {
        return $this->belongsTo(Agente::class, 'aprobado_por');
    }
}
