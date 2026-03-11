<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $nombre
 * @property string|null $categoria
 * @property string|null $articulo_legal
 * @property int|null $duracion_maxima_dias
 * @property bool $requiere_adjunto
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Solicitud> $solicitudes
 * @property-read int|null $solicitudes_count
 */
class TipoSolicitud extends Model
{
    /** @use HasFactory<\Database\Factories\TipoSolicitudFactory> */
    use HasFactory;

    protected $table = 'tipo_solicituds';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
        'categoria',
        'articulo_legal',
        'duracion_maxima_dias',
        'requiere_adjunto'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'requiere_adjunto' => 'boolean',
            'duracion_maxima_dias' => 'integer',
        ];
    }

    /**
     * Get the solicitudes for the tipo de solicitud.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Solicitud, $this>
     */
    public function solicitudes(): HasMany
    {
        return $this->hasMany(Solicitud::class);
    }
}
