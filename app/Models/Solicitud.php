<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $agente_id
 * @property int $tipo_solicitud_id
 * @property \Illuminate\Support\Carbon $fecha_solicitud
 * @property \Illuminate\Support\Carbon $fecha_inicio
 * @property \Illuminate\Support\Carbon $fecha_fin
 * @property int $dias_solicitados
 * @property string|null $motivo
 * @property string $estado
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\Agente $agente
 * @property-read \App\Models\TipoSolicitud $tipoSolicitud
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Aprobacion> $aprobaciones
 * @property-read int|null $aprobaciones_count
 * @property-read \App\Models\Resolucion|null $resolucion
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Documento> $documentos
 * @property-read int|null $documentos_count
 */
class Solicitud extends Model
{
    /** @use HasFactory<\Database\Factories\SolicitudFactory> */
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Agente, $this>
     */
    public function agente(): BelongsTo
    {
        return $this->belongsTo(Agente::class);
    }

    /**
     * Get the tipo de solicitud that owns the solicitud.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\TipoSolicitud, $this>
     */
    public function tipoSolicitud(): BelongsTo
    {
        return $this->belongsTo(TipoSolicitud::class);
    }

    /**
     * Get the aprobaciones for the solicitud.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Aprobacion, $this>
     */
    public function aprobaciones(): HasMany
    {
        return $this->hasMany(Aprobacion::class);
    }

    /**
     * Get the resolucion associated with the solicitud.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<\App\Models\Resolucion, $this>
     */
    public function resolucion(): HasOne
    {
        return $this->hasOne(Resolucion::class);
    }

    /**
     * Get the documentos for the solicitud.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Documento, $this>
     */
    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class);
    }
}
