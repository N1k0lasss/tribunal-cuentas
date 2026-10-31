<?php

namespace App\Models;

use Database\Factories\ResolucionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property string $numero
 * @property Carbon $fecha_emision
 * @property string|null $detalle
 * @property int|null $solicitud_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Solicitud|null $solicitud
 * @property-read Collection<int, Solicitud> $solicitudes
 * @property-read int|null $solicitudes_count
 * @property-read Collection<int, Documento> $documentos
 * @property-read int|null $documentos_count
 * @property-read Documento|null $pdfFirmado
 */
class Resolucion extends Model
{
    /** @use HasFactory<ResolucionFactory> */
    use HasFactory;

    use LogsActivity;

    protected $table = 'resolucions';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'numero',
        'fecha_emision',
        'detalle',
        'solicitud_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_emision' => 'date',
        ];
    }

    /**
     * Get the solicitud that owns the resolucion.
     *
     * @return BelongsTo<Solicitud, $this>
     */
    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(Solicitud::class);
    }

    /**
     * Licencias agrupadas por una resolución diaria.
     *
     * @return BelongsToMany<Solicitud, $this>
     */
    public function solicitudes(): BelongsToMany
    {
        return $this->belongsToMany(Solicitud::class, 'resolucion_solicitud')
            ->withTimestamps()
            ->orderByPivot('id');
    }

    /**
     * Resoluciones diarias (las que no pertenecen a una única solicitud).
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeDiarias($query)
    {
        return $query->whereNull('solicitud_id');
    }

    /**
     * Get the documentos for the resolucion.
     *
     * @return HasMany<Documento, $this>
     */
    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class);
    }

    /**
     * PDF firmado vigente de la resolución.
     *
     * @return HasOne<Documento, $this>
     */
    public function pdfFirmado(): HasOne
    {
        return $this->hasOne(Documento::class)
            ->where('tipo_documento', Documento::TIPO_RESOLUCION_FIRMADA);
    }

    public function tienePdfFirmado(): bool
    {
        if ($this->relationLoaded('pdfFirmado')) {
            return $this->pdfFirmado !== null;
        }

        return $this->pdfFirmado()->exists();
    }

    protected static function booted(): void
    {
        // Las resoluciones son documentos auditables y no deben eliminarse.
        static::deleting(fn (): bool => false);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
