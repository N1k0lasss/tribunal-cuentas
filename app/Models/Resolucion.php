<?php

namespace App\Models;

use Database\Factories\ResolucionFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $numero
 * @property Carbon $fecha_emision
 * @property string|null $detalle
 * @property int $solicitud_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Solicitud $solicitud
 * @property-read Collection<int, Documento> $documentos
 * @property-read int|null $documentos_count
 */
class Resolucion extends Model
{
    /** @use HasFactory<ResolucionFactory> */
    use HasFactory;

    protected $table = 'resolucions';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'numero',
        'gde_expediente',
        'gde_documento',
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
     * Get the documentos for the resolucion.
     *
     * @return HasMany<Documento, $this>
     */
    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class);
    }
}
