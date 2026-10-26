<?php

namespace App\Models;

use Database\Factories\SaldoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property int $agente_id
 * @property int $anio
 * @property string $tipo_saldo
 * @property int $dias_otorgados
 * @property int $dias_usados
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Agente $agente
 */
class Saldo extends Model
{
    /** @use HasFactory<SaldoFactory> */
    use HasFactory;

    use LogsActivity;

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
        'dias_usados',
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
     * @return BelongsTo<Agente, $this>
     */
    public function agente(): BelongsTo
    {
        return $this->belongsTo(Agente::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
