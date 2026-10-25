<?php

namespace App\Models;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Database\Factories\DocumentoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $nombre_archivo
 * @property string $ruta
 * @property string $tipo_documento
 * @property string|null $descripcion
 * @property int|null $solicitud_id
 * @property int|null $aprobacion_id
 * @property int|null $resolucion_id
 * @property int|null $agente_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Solicitud|null $solicitud
 * @property-read Aprobacion|null $aprobacion
 * @property-read Resolucion|null $resolucion
 * @property-read Agente|null $agente
 */
class Documento extends Model
{
    use LogsActivity;
    /** @use HasFactory<DocumentoFactory> */
    use HasFactory;

    /**
     * Tipo de documento correspondiente a la solicitud firmada digitalmente
     * por una entidad en el circuito de aprobación.
     */
    public const TIPO_SOLICITUD_FIRMADA = 'solicitud_firmada';

    /**
     * Tipo de documento correspondiente a la resolución firmada digitalmente.
     */
    public const TIPO_RESOLUCION_FIRMADA = 'resolucion_firmada';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nombre_archivo',
        'ruta',
        'tipo_documento',
        'descripcion',
        'solicitud_id',
        'aprobacion_id',
        'resolucion_id',
        'agente_id',
    ];

    /**
     * Get the solicitud that owns the documento.
     *
     * @return BelongsTo<Solicitud, $this>
     */
    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(Solicitud::class);
    }

    /**
     * Get the aprobacion (etapa) in which the documento was uploaded.
     *
     * @return BelongsTo<Aprobacion, $this>
     */
    public function aprobacion(): BelongsTo
    {
        return $this->belongsTo(Aprobacion::class);
    }

    /**
     * Get the resolucion that owns the documento.
     *
     * @return BelongsTo<Resolucion, $this>
     */
    public function resolucion(): BelongsTo
    {
        return $this->belongsTo(Resolucion::class);
    }

    /**
     * Get the agente that owns the documento.
     *
     * @return BelongsTo<Agente, $this>
     */
    public function agente(): BelongsTo
    {
        return $this->belongsTo(Agente::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

