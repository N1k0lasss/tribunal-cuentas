<?php

namespace App\Models;

use Database\Factories\AgenteFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int|null $user_id
 * @property-read User|null $user
 * @property int $id
 * @property int|null $legajo
 * @property string $nombre
 * @property string $apellido
 * @property string $dni
 * @property string|null $cargo
 * @property string|null $situacion_revista
 * @property int|null $estado_legajo
 * @property Carbon|null $fecha_baja
 * @property string|null $motivo_baja
 * @property int $area_id
 * @property Carbon $fecha_ingreso
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Area $area
 * @property-read Collection<int, Solicitud> $solicitudes
 * @property-read int|null $solicitudes_count
 * @property-read Collection<int, Documento> $documentos
 * @property-read int|null $documentos_count
 * @property-read Collection<int, Aprobacion> $aprobaciones
 * @property-read int|null $aprobaciones_count
 */
class Agente extends Model
{
    /** @use HasFactory<AgenteFactory> */
    use HasFactory;

    use LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'legajo',
        'nombre',
        'apellido',
        'dni',
        'cargo',
        'situacion_revista',
        'estado_legajo',
        'fecha_baja',
        'motivo_baja',
        'area_id',
        'fecha_ingreso',
        'user_id',
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
            'fecha_baja' => 'date',
            'estado_legajo' => 'integer',
        ];
    }

    /**
     * Get the area that owns the agente.
     *
     * @return BelongsTo<Area, $this>
     */
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    /**
     * Get the solicitudes for the agente.
     *
     * @return HasMany<Solicitud, $this>
     */
    public function solicitudes(): HasMany
    {
        return $this->hasMany(Solicitud::class);
    }

    /**
     * Get the documentos for the agente.
     *
     * @return HasMany<Documento, $this>
     */
    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class);
    }

    /**
     * Get the aprobaciones made by the agente.
     *
     * @return HasMany<Aprobacion, $this>
     */
    public function aprobaciones(): HasMany
    {
        return $this->hasMany(Aprobacion::class, 'aprobado_por');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function informeDetalles(): HasMany
    {
        return $this->hasMany(InformeDiarioDetalle::class, 'agente_id');
    }

    /**
     * Indica si el legajo del agente figura dado de baja en el Sistema de
     * Legajos (`estado_legajo` = 0). Devuelve `false` cuando todavía no se
     * importó el estado (`null`), para no marcar como baja a quien no se sabe.
     */
    public function estaDeBaja(): bool
    {
        return $this->estado_legajo === 0;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
