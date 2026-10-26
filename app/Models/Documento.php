<?php

namespace App\Models;

use Database\Factories\DocumentoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

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
    /** @use HasFactory<DocumentoFactory> */
    use HasFactory;

    use LogsActivity;

    /**
     * Tipo de documento correspondiente a la solicitud firmada digitalmente
     * por una entidad en el circuito de aprobación.
     */
    public const TIPO_SOLICITUD_FIRMADA = 'solicitud_firmada';

    /**
     * Tipo de documento correspondiente a la resolución firmada digitalmente.
     */
    public const TIPO_RESOLUCION_FIRMADA = 'resolucion_firmada';

    protected static bool $permitirDocumentoSistema = false;

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

    public function esSistema(): bool
    {
        return in_array($this->tipo_documento, [
            self::TIPO_RESOLUCION_FIRMADA,
            self::TIPO_SOLICITUD_FIRMADA,
        ], true);
    }

    public function esResolucionFirmada(): bool
    {
        return $this->tipo_documento === self::TIPO_RESOLUCION_FIRMADA;
    }

    public function esImagen(): bool
    {
        return in_array(
            strtolower(pathinfo((string) $this->ruta, PATHINFO_EXTENSION)),
            ['jpg', 'jpeg', 'png', 'webp'],
            true,
        );
    }

    public function esPdf(): bool
    {
        return strtolower(pathinfo((string) $this->ruta, PATHINFO_EXTENSION)) === 'pdf';
    }

    public function rutaValida(): bool
    {
        return is_string($this->ruta) && self::rutaValidaPath($this->ruta);
    }

    public static function rutaValidaPath(string $ruta): bool
    {
        $ruta = ltrim($ruta, '/');

        return str_starts_with($ruta, 'documentos/')
            && ! str_contains($ruta, '..')
            && ! str_contains($ruta, "\0");
    }

    public function archivoExiste(): bool
    {
        return $this->rutaValida() && Storage::disk('local')->exists($this->ruta);
    }

    public static function conPermisoDocumentoSistema(callable $callback): mixed
    {
        self::$permitirDocumentoSistema = true;

        try {
            return $callback();
        } finally {
            self::$permitirDocumentoSistema = false;
        }
    }

    protected static function booted(): void
    {
        static::saving(function (Documento $documento): void {
            if ($documento->solicitud_id !== null) {
                $documento->agente_id = $documento->solicitud?->agente_id;
            }
        });

        static::creating(function (Documento $documento): void {
            if ($documento->esSistema() && ! self::$permitirDocumentoSistema) {
                throw new \DomainException('Los documentos del sistema solo se crean desde el flujo autorizado.');
            }
        });

        static::updating(function (Documento $documento): void {
            $eraSistema = in_array($documento->getOriginal('tipo_documento'), [
                self::TIPO_RESOLUCION_FIRMADA,
                self::TIPO_SOLICITUD_FIRMADA,
            ], true);

            if (($eraSistema || $documento->esSistema()) && ! self::$permitirDocumentoSistema) {
                throw new \DomainException('Los documentos del sistema no se pueden modificar desde el CRUD.');
            }
        });

        static::deleting(function (Documento $documento): void {
            if ($documento->esSistema()) {
                throw new \DomainException('Los documentos del sistema no se pueden eliminar.');
            }
        });

        static::updated(function (Documento $documento): void {
            if ($documento->esSistema() || ! $documento->wasChanged('ruta')) {
                return;
            }

            $rutaAnterior = $documento->getOriginal('ruta');

            if ($rutaAnterior
                && $rutaAnterior !== $documento->ruta
                && self::rutaValidaPath($rutaAnterior)) {
                Storage::disk('local')->delete($rutaAnterior);
            }
        });

        static::deleted(function (Documento $documento): void {
            if ($documento->esSistema()) {
                return;
            }

            if (self::rutaValidaPath($documento->ruta)) {
                Storage::disk('local')->delete($documento->ruta);
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
