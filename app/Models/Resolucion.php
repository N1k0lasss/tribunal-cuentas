<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resolucion extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero',
        'fecha_emision',
        'detalle',
        'solicitud_id'
    ];

    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class);
        // Una resolucion pertenece a una solicitud
    }

    public function documentos()
    {
        return $this->hasMany(Documento::class);
        // Una resolucion puede tener mas de un documento
    }
}
