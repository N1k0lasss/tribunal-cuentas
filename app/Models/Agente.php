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
        'fecha_ingreso',
    ];

    protected function casts(): array
    {
        return [
            'fecha_ingreso' => 'date',
        ];
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
        // Un agente pertenece a un area
    }

    public function solicitudes()
    {
        return $this->hasMany(Solicitud::class);
        // Un agente puede tener muchas solicitudes
    }

    public function saldos()
    {
        return $this->hasMany(Saldo::class);
        // Un agente puede tener muchos saldos
    }

    public function documentos()
    {
        return $this->hasMany(Documento::class);
        // Un agente puede tener muchos documentos
    }

    public function aprobaciones()
    {
        return $this->hasMany(Aprobacion::class, 'aprobado_por');
        // Un agente puede tener muchas aprobaciones
        // Y un agente puede aprobar muchas solicitudes
    }
}
