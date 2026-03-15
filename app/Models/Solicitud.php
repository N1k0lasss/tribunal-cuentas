<?php

namespace App\Models;

use Database\Factories\SolicitudFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $agente_id
 * @property int $tipo_solicitud_id
 * @property Carbon $fecha_solicitud
 * @property Carbon $fecha_inicio
 * @property Carbon $fecha_fin
 * @property int $dias_solicitados
 * @property string|null $motivo
 * @property string $estado
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Agente $agente
 * @property-read TipoSolicitud $tipoSolicitud
 * @property-read Collection<int, Aprobacion> $aprobaciones
 * @property-read int|null $aprobaciones_count
 * @property-read Resolucion|null $resolucion
 * @property-read Collection<int, Documento> $documentos
 * @property-read int|null $documentos_count
 */
class Solicitud extends Model
{
    /** @use HasFactory<SolicitudFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'agente_id',
        'tipo_solicitud_id',
        'fecha_solicitud',
        'fecha_inicio',
        'fecha_fin',
        'dias_solicitados',
        'motivo',
        'estado',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_solicitud' => 'date',
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'dias_solicitados' => 'integer',
        ];
    }

    /**
     * Get the agente that owns the solicitud.
     *
     * @return BelongsTo<Agente, $this>
     */
    public function agente(): BelongsTo
    {
        return $this->belongsTo(Agente::class);
    }

    /**
     * Get the tipo de solicitud that owns the solicitud.
     *
     * @return BelongsTo<TipoSolicitud, $this>
     */
    public function tipoSolicitud(): BelongsTo
    {
        return $this->belongsTo(TipoSolicitud::class);
    }

    /**
     * Get the aprobaciones for the solicitud.
     *
     * @return HasMany<Aprobacion, $this>
     */
    public function aprobaciones(): HasMany
    {
        return $this->hasMany(Aprobacion::class);
    }

    /**
     * Get the resolucion associated with the solicitud.
     *
     * @return HasOne<Resolucion, $this>
     */
    public function resolucion(): HasOne
    {
        return $this->hasOne(Resolucion::class);
    }

    /**
     * Get the documentos for the solicitud.
     *
     * @return HasMany<Documento, $this>
     */
    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class);
    }
}
