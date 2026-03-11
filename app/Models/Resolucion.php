<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $numero
 * @property \Illuminate\Support\Carbon $fecha_emision
 * @property string|null $detalle
 * @property int $solicitud_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\Solicitud $solicitud
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Documento> $documentos
 * @property-read int|null $documentos_count
 */
class Resolucion extends Model
{
    /** @use HasFactory<\Database\Factories\ResolucionFactory> */
    use HasFactory;

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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Solicitud, $this>
     */
    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(Solicitud::class);
    }

    /**
     * Get the documentos for the resolucion.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Documento, $this>
     */
    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class);
    }
}
