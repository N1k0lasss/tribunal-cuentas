<?php

namespace App\Models;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Illuminate\Database\Eloquent\Model;

class Feriados extends Model
{
    use LogsActivity;
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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

