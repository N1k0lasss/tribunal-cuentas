<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $nombre_archivo
 * @property string $ruta
 * @property string $tipo_documento
 * @property string|null $descripcion
 * @property int|null $solicitud_id
 * @property int|null $resolucion_id
 * @property int|null $agente_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\Solicitud|null $solicitud
 * @property-read \App\Models\Resolucion|null $resolucion
 * @property-read \App\Models\Agente|null $agente
 */
class Documento extends Model
{
    /** @use HasFactory<\Database\Factories\DocumentoFactory> */
    use HasFactory;

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
        'resolucion_id',
        'agente_id'
    ];

    /**
     * Get the solicitud that owns the documento.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Solicitud, $this>
     */
    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(Solicitud::class);
    }

    /**
     * Get the resolucion that owns the documento.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Resolucion, $this>
     */
    public function resolucion(): BelongsTo
    {
        return $this->belongsTo(Resolucion::class);
    }

    /**
     * Get the agente that owns the documento.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Agente, $this>
     */
    public function agente(): BelongsTo
    {
        return $this->belongsTo(Agente::class);
    }
}
