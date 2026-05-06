<?php

namespace App\Models;

use App\Models\Concerns\InteractsWithWorkflowRules;
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
 * @property int|null $solicitud_origen_id
 * @property Carbon $fecha_solicitud
 * @property Carbon $fecha_inicio
 * @property Carbon $fecha_fin
 * @property int $dias_solicitados
 * @property string|null $motivo
 * @property string $estado
 * @property bool $es_prorroga
 * @property Carbon|null $justificacion_vence_en
 * @property Carbon|null $justificacion_presentada_en
 * @property Carbon|null $justificacion_vencimiento_notificado_en
 * @property Carbon|null $prorroga_vence_en
 * @property Carbon|null $prorroga_vencimiento_notificado_en
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Agente $agente
 * @property-read TipoSolicitud $tipoSolicitud
 * @property-read Collection<int, Aprobacion> $aprobaciones
 * @property-read int|null $aprobaciones_count
 * @property-read Resolucion|null $resolucion
 * @property-read Collection<int, Documento> $documentos
 * @property-read int|null $documentos_count
 * @property-read Solicitud|null $solicitudOrigen
 * @property-read Collection<int, Solicitud> $prorrogas
 * @property-read int|null $prorrogas_count
 * @property-read Collection<int, WorkflowEvento> $workflowEventos
 * @property-read int|null $workflow_eventos_count
 */
class Solicitud extends Model
{
    /** @use HasFactory<SolicitudFactory> */
    use HasFactory, InteractsWithWorkflowRules;

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
        foreach (self::FLUJO_ETAPAS as $etapa) {
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

        $index = array_search($actual, self::FLUJO_ETAPAS, true);

        return self::FLUJO_ETAPAS[$index + 1] ?? null;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'agente_id',
        'tipo_solicitud_id',
        'solicitud_origen_id',
        'fecha_solicitud',
        'fecha_inicio',
        'fecha_fin',
        'dias_solicitados',
        'hora_inicio',
        'hora_fin',
        'horas_solicitadas',
        'motivo',
        'estado',
        'es_prorroga',
        'justificacion_vence_en',
        'justificacion_presentada_en',
        'justificacion_vencimiento_notificado_en',
        'prorroga_vence_en',
        'prorroga_vencimiento_notificado_en',
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
            'es_prorroga' => 'boolean',
            'justificacion_vence_en' => 'datetime',
            'justificacion_presentada_en' => 'datetime',
            'justificacion_vencimiento_notificado_en' => 'datetime',
            'prorroga_vence_en' => 'datetime',
            'prorroga_vencimiento_notificado_en' => 'datetime',
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
     * @return BelongsTo<Solicitud, $this>
     */
    public function solicitudOrigen(): BelongsTo
    {
        return $this->belongsTo(self::class, 'solicitud_origen_id');
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

    /**
     * @return HasMany<Solicitud, $this>
     */
    public function prorrogas(): HasMany
    {
        return $this->hasMany(self::class, 'solicitud_origen_id');
    }

    /**
     * @return HasMany<WorkflowEvento, $this>
     */
    public function workflowEventos(): HasMany
    {
        return $this->hasMany(WorkflowEvento::class);
    }

    /**
     * Get the firmas digitales for the solicitud.
     *
     * @return HasMany<FirmaDigital, $this>
     */
    public function firmasDigitales(): HasMany
    {
        return $this->hasMany(FirmaDigital::class);
    }

    /**
     * Verifica si la solicitud tiene todas las firmas requeridas.
     */
    public function tieneFirmasCompletas(): bool
    {
        return $this->firmasDigitales()->count() === count(self::FLUJO_ETAPAS);
    }

    /**
     * Obtiene la firma de una etapa específica.
     */
    public function getFirmaEtapa(string $etapa): ?FirmaDigital
    {
        return $this->firmasDigitales()->where('etapa', $etapa)->first();
    }

    public function requiereJustificacionMedica(): bool
    {
        return (bool) $this->tipoSolicitud?->requiere_intervencion_medica;
    }

    public function justificacionEstaVencida(): bool
    {
        if ($this->justificacion_vence_en === null || $this->justificacion_presentada_en !== null) {
            return false;
        }

        return now()->greaterThan($this->justificacion_vence_en);
    }

    public function prorrogaEstaVencida(): bool
    {
        if ($this->prorroga_vence_en === null) {
            return false;
        }

        return now()->greaterThan($this->prorroga_vence_en);
    }

    public function requiereNotificacionVencimientoJustificacion(): bool
    {
        return $this->justificacionEstaVencida() && $this->justificacion_vencimiento_notificado_en === null;
    }

    public function requiereNotificacionVencimientoProrroga(): bool
    {
        return $this->prorrogaEstaVencida() && $this->prorroga_vencimiento_notificado_en === null;
    }
}
