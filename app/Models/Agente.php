<?php

namespace App\Models;

use Database\Factories\AgenteFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int|null $user_id
 * @property-read User|null $user
 * @property int $id
 * @property string $nombre
 * @property string $apellido
 * @property string $dni
 * @property string|null $cargo
 * @property string|null $tipo_contrato
 * @property int $area_id
 * @property Carbon $fecha_ingreso
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Area $area
 * @property-read Collection<int, Solicitud> $solicitudes
 * @property-read int|null $solicitudes_count
 * @property-read Collection<int, Saldo> $saldos
 * @property-read int|null $saldos_count
 * @property-read Collection<int, Documento> $documentos
 * @property-read int|null $documentos_count
 * @property-read Collection<int, Aprobacion> $aprobaciones
 * @property-read int|null $aprobaciones_count
 */
class Agente extends Model
{
    /** @use HasFactory<AgenteFactory> */
    use HasFactory;

    public const TIPO_CONTRATO_COMUN = 'comun';

    public const TIPO_CONTRATO_SERVICIO = 'servicio';

    public const TIPO_CONTRATO_PLANTA_PERMANENTE = 'planta_permanente';

    /**
     * @var list<string>
     */
    public const TIPOS_CONTRATO = [
        self::TIPO_CONTRATO_COMUN,
        self::TIPO_CONTRATO_SERVICIO,
        self::TIPO_CONTRATO_PLANTA_PERMANENTE,
    ];

    /**
     * @var array<string, string>
     */
    public const TIPO_CONTRATO_LABELS = [
        self::TIPO_CONTRATO_COMUN => 'Común',
        self::TIPO_CONTRATO_SERVICIO => 'Servicio',
        self::TIPO_CONTRATO_PLANTA_PERMANENTE => 'Planta Permanente',
    ];

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
        ];
    }

    public function getTipoContratoLabelAttribute(): string
    {
        return self::TIPO_CONTRATO_LABELS[$this->tipo_contrato] ?? (string) $this->tipo_contrato;
    }

    public static function normalizarTipoContrato(?string $tipoContrato): ?string
    {
        if ($tipoContrato === null) {
            return null;
        }

        $normalizado = mb_strtolower(trim($tipoContrato));

        return match ($normalizado) {
            'comun', 'común', 'planta transitoria', 'transitoria' => self::TIPO_CONTRATO_COMUN,
            'servicio', 'contrato de locación', 'contrato de locacion', 'locación', 'locacion' => self::TIPO_CONTRATO_SERVICIO,
            'planta permanente', 'permanente' => self::TIPO_CONTRATO_PLANTA_PERMANENTE,
            default => in_array($tipoContrato, self::TIPOS_CONTRATO, true) ? $tipoContrato : null,
        };
    }

    protected function tipoContrato(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value): ?string => self::normalizarTipoContrato($value),
        );
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
     * Get the saldos for the agente.
     *
     * @return HasMany<Saldo, $this>
     */
    public function saldos(): HasMany
    {
        return $this->hasMany(Saldo::class);
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
}
