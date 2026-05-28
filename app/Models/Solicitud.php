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

    // ── Estados del workflow ────────────────────────────────────
    public const ESTADO_PENDIENTE = 'pendiente';
    public const ESTADO_EN_REVISION = 'en_revision';
    public const ESTADO_APROBADO = 'aprobado';
    public const ESTADO_RECHAZADO = 'rechazado';

    // ── Etapas de aprobación (valores del enum en la DB) ───────
    // Nota: 'direccion' en la DB representa "Presidencia" en el negocio.
    public const ETAPA_JEFATURA = 'jefatura';
    public const ETAPA_SECRETARIA = 'secretaria';
    public const ETAPA_DIRECCION = 'direccion'; // = Presidencia

    /**
     * Flujo secuencial de etapas de aprobación.
     *
     * @var list<string>
     */
    public const FLUJO_ETAPAS = [
        self::ETAPA_JEFATURA,
        self::ETAPA_SECRETARIA,
        self::ETAPA_DIRECCION,
    ];

    /**
     * Mapeo: etapa de aprobación → rol del usuario responsable.
     *
     * @var array<string, string>
     */
    public const ETAPA_ROL_MAP = [
        self::ETAPA_JEFATURA => 'jefatura',
        self::ETAPA_SECRETARIA => 'secretaria_general',
        self::ETAPA_DIRECCION => 'presidencia',
    ];

    public const CATEGORIA_SALUD_MATERNIDAD = 'Salud y Maternidad';

    /**
     * Determina si la solicitud ya entró al flujo de aprobación.
     */
    public function estaEnFlujo(): bool
    {
        return $this->estado !== self::ESTADO_PENDIENTE;
    }

    /**
     * Determina si la solicitud puede ser editada por el agente.
     * Solo se permite mientras esté en estado "pendiente".
     */
    public function puedeSerEditada(): bool
    {
        return $this->estado === self::ESTADO_PENDIENTE;
    }

    /**
     * Devuelve la etapa actual de aprobación pendiente, o null si ya se completaron todas.
     */
    public function etapaActual(): ?string
    {
        foreach ($this->flujoEtapas() as $etapa) {
            $aprobacion = $this->aprobaciones->firstWhere('etapa', $etapa);

            if (! $aprobacion || $aprobacion->estado === 'pendiente') {
                return $etapa;
            }

            if ($aprobacion->estado === 'rechazado') {
                return null; // Flujo detenido por rechazo
            }
        }

        return null; // Todas las etapas aprobadas
    }

    /**
     * Devuelve la siguiente etapa de aprobación después de la actual, o null si es la última.
     */
    public function siguienteEtapa(): ?string
    {
        $actual = $this->etapaActual();

        if ($actual === null) {
            return null;
        }

        $flujoEtapas = $this->flujoEtapas();
        $index = array_search($actual, $flujoEtapas, true);

        return $flujoEtapas[$index + 1] ?? null;
    }

    /**
     * Devuelve el flujo de aprobación aplicable para esta solicitud.
     *
     * @return list<string>
     */
    public function flujoEtapas(): array
    {
        if ($this->esCategoriaSaludYMaternidad()) {
            return [self::ETAPA_SECRETARIA];
        }

        return self::FLUJO_ETAPAS;
    }

    public function esCategoriaSaludYMaternidad(): bool
    {
        $categoria = $this->tipoSolicitud?->categoria;

        if ($categoria === null && $this->tipo_solicitud_id !== null) {
            $categoria = $this->tipoSolicitud()->value('categoria');
        }

        return $categoria === self::CATEGORIA_SALUD_MATERNIDAD;
    }

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
        'hora_inicio',
        'hora_fin',
        'horas_solicitadas',
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
    public function solicitudOrigen(): BelongsTo
    {
        return $this->belongsTo(Solicitud::class, 'solicitud_origen_id');
    }

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
