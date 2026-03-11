<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $nombre
 * @property string $apellido
 * @property string $dni
 * @property string|null $cargo
 * @property string|null $tipo_contrato
 * @property int $area_id
 * @property \Illuminate\Support\Carbon $fecha_ingreso
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\Area $area
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Solicitud> $solicitudes
 * @property-read int|null $solicitudes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Saldo> $saldos
 * @property-read int|null $saldos_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Documento> $documentos
 * @property-read int|null $documentos_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Aprobacion> $aprobaciones
 * @property-read int|null $aprobaciones_count
 */
class Agente extends Model
{
    /** @use HasFactory<\Database\Factories\AgenteFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
        'apellido',
        'dni',
        'cargo',
        'tipo_contrato',
        'area_id',
        'fecha_ingreso',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_ingreso' => 'date',
        ];
    }

    /**
     * Get the area that owns the agente.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Area, $this>
     */
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    /**
     * Get the solicitudes for the agente.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Solicitud, $this>
     */
    public function solicitudes(): HasMany
    {
        return $this->hasMany(Solicitud::class);
    }

    /**
     * Get the saldos for the agente.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Saldo, $this>
     */
    public function saldos(): HasMany
    {
        return $this->hasMany(Saldo::class);
    }

    /**
     * Get the documentos for the agente.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Documento, $this>
     */
    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class);
    }

    /**
     * Get the aprobaciones made by the agente.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Aprobacion, $this>
     */
    public function aprobaciones(): HasMany
    {
        return $this->hasMany(Aprobacion::class, 'aprobado_por');
    }
}
