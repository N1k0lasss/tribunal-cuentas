<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aprobacion extends Model
{
    use HasFactory;

    protected $table = 'aprobacions';

    protected $fillable = [
        'solicitud_id',
        'aprobado_por',
        'etapa',
        'estado',
        'observacion',
        'fecha'
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class);
        // Una aprobacion pertenece a una solicitud
    }

    public function aprobador()
    {
        return $this->belongsTo(Agente::class, 'aprobado_por');
        // Una aprobacion pertenece a un agente
    }
}
