<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre_archivo',
        'ruta',
        'tipo_documento',
        'descripcion',
        'solicitud_id',
        'resolucion_id',
        'agente_id'
    ];

    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class);
        // Un documento pertenece a una solicitud
    }

    public function resolucion()
    {
        return $this->belongsTo(Resolucion::class);
        // Un documento pertenece a una resolucion
    }

    public function agente()
    {
        return $this->belongsTo(Agente::class);
        // Un documento pertenece a un agente
    }
}
