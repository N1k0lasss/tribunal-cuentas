<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property int $titular_user_id
 * @property int $delegado_user_id
 * @property string $etapa
 * @property Carbon $vigente_desde
 * @property Carbon $vigente_hasta
 * @property string|null $motivo
 * @property bool $activa
 * @property int|null $creada_por_user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $titular
 * @property-read User $delegado
 * @property-read User|null $creadaPor
 */
class Delegacion extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $table = 'delegaciones';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'titular_user_id',
        'delegado_user_id',
        'etapa',
        'vigente_desde',
        'vigente_hasta',
        'motivo',
        'activa',
        'creada_por_user_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'vigente_desde' => 'datetime',
            'vigente_hasta' => 'datetime',
            'activa' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function titular(): BelongsTo
    {
        return $this->belongsTo(User::class, 'titular_user_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function delegado(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delegado_user_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creadaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creada_por_user_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
