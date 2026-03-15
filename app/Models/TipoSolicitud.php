<?php

namespace App\Models;

use Database\Factories\TipoSolicitudFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $nombre
 * @property string|null $categoria
 * @property string|null $articulo_legal
 * @property int|null $duracion_maxima_dias
 * @property bool $requiere_adjunto
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Solicitud> $solicitudes
 * @property-read int|null $solicitudes_count
 */
class TipoSolicitud extends Model
{
    /** @use HasFactory<TipoSolicitudFactory> */
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
        'requiere_adjunto',
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
     * @return HasMany<Solicitud, $this>
     */
    public function solicitudes(): HasMany
    {
        return $this->hasMany(Solicitud::class);
    }
}
