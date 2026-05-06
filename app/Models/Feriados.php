<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feriados extends Model
{
    protected $table = 'feriados';
    protected $fillable = [
        'nombre',
        'fecha_inicio',
        'fecha_fin',
        'todo_el_dia',
        'tipo',
        'trasladable',
        'recurrente',
    ];
    protected $casts = [
        'fecha_inicio' => 'datetime', 
        'fecha_fin' => 'datetime',
        'todo_el_dia' => 'boolean',
    ];
}
