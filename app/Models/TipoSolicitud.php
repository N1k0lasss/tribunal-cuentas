<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoSolicitud extends Model
{
    use HasFactory;

    protected $table = 'tipo_solicituds';
     protected $fillable = [
        'nombre',
        'categoria',
        'articulo_legal',
        'duracion_max_dias',
        'requiere_adjunto'
    ];
     public function solicitudes()
    {
        return $this->hasMany(Solicitud::class);
        // Un tipo de solicitud tiene muchas solicitudes
    }
}
