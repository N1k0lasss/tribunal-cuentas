<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Saldo extends Model
{
    use HasFactory;

    protected $fillable = [
        'agente_id',
        'anio',
        'tipo_saldo',
        'dias_otorgados',
        'dias_usados'
    ];

    public function agente()
    {
        return $this->belongsTo(Agente::class);
        // Un saldo pertenece a un agente
    }
}
