<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $solicitud_id
 * @property int $aprobado_por
 * @property string $etapa
 * @property string $estado
 * @property string|null $observacion
 * @property \Illuminate\Support\Carbon $fecha
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\Solicitud $solicitud
 * @property-read \App\Models\Agente $aprobador
 */
class Aprobacion extends Model
{
    /** @use HasFactory<\Database\Factories\AprobacionFactory> */
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Solicitud, $this>
     */
    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(Solicitud::class);
    }

    /**
     * Get the agente who approved the solicitud.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Agente, $this>
     */
    public function aprobador(): BelongsTo
    {
        return $this->belongsTo(Agente::class, 'aprobado_por');
    }
}
