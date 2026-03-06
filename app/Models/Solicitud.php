<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Solicitud extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'agente_id',
        'tipo_solicitud_id',
        'fecha_solicitud',
        'fecha_inicio',
        'fecha_fin',
        'dias_solicitados',
        'motivo',
        'estado'
    ];

     public function agente()
    {
        return $this->belongsTo(Agente::class);
        // Una solicitud pertenece a un agente
    }

    public function tipoSolicitud()
    {
        return $this->belongsTo(TipoSolicitud::class);
        // Una solicitud pertenece a un tipo de solicitud
    }

    public function aprobaciones()
    {
        return $this->hasMany(Aprobacion::class);
        // Una solicitud puede tener mas de una aprobacion
    }

    public function resolucion()
    {
        return $this->hasOne(Resolucion::class);
        // Una solicitud puede tener una resolucion
    }

    public function documentos()
    {
        return $this->hasMany(Documento::class);
        // Una solicitud puede tener mas de un documento
    }
}
