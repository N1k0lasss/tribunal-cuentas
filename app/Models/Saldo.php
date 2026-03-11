<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $agente_id
 * @property int $anio
 * @property string $tipo_saldo
 * @property int $dias_otorgados
 * @property int $dias_usados
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\Agente $agente
 */
class Saldo extends Model
{
    /** @use HasFactory<\Database\Factories\SaldoFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'agente_id',
        'anio',
        'tipo_saldo',
        'dias_otorgados',
        'dias_usados'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'anio' => 'integer',
            'dias_otorgados' => 'integer',
            'dias_usados' => 'integer',
        ];
    }

    /**
     * Get the agente that owns the saldo.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Agente, $this>
     */
    public function agente(): BelongsTo
    {
        return $this->belongsTo(Agente::class);
    }
}
