<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resolucion extends Model
{
    use HasFactory;

    protected $table = 'resolucions';

    protected $fillable = [
        'numero',
        'fecha_emision',
        'detalle',
        'solicitud_id'
    ];

    protected $casts = [
        'fecha_emision' => 'date',
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
