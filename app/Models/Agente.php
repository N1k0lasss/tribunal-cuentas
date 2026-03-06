<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agente extends Model
{
    use HasFactory;
    protected $fillable = [
        'nombre',
        'apellido',
        'dni',
        'cargo',
        'tipo_contrato',
        'area_id',
        'fecha_ingreso'
    ];
     public function area()
    {
        return $this->belongsTo(Area::class);
        // Un agente pertenece a un area
    }

    public function solicitudes()
    {
        return $this->hasMany(Solicitud::class);
        // Un agente tiene muchas solicitudes
    }

    public function saldos()
    {
        return $this->hasMany(Saldo::class);
        // Un agente tiene muchos saldos
    }

    public function documentos()
    {
        return $this->hasMany(Documento::class);
        // Un agente tiene muchos documentos
    }
} 
