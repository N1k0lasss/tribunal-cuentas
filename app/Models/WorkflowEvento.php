<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $solicitud_id
 * @property string $evento
 * @property string|null $etapa
 * @property string|null $estado_anterior
 * @property string|null $estado_nuevo
 * @property int|null $actor_user_id
 * @property int|null $actor_agente_id
 * @property bool $es_delegacion
 * @property int|null $delegacion_id
 * @property string|null $detalle
 * @property string|null $ip
 * @property string|null $user_agent
 * @property Carbon $ocurrio_en
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Solicitud $solicitud
 * @property-read User|null $actorUser
 * @property-read Agente|null $actorAgente
 * @property-read Delegacion|null $delegacion
 */
class WorkflowEvento extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'solicitud_id',
        'evento',
        'etapa',
        'estado_anterior',
        'estado_nuevo',
        'actor_user_id',
        'actor_agente_id',
        'es_delegacion',
        'delegacion_id',
        'detalle',
        'ip',
        'user_agent',
        'ocurrio_en',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'es_delegacion' => 'boolean',
            'ocurrio_en' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Solicitud, $this>
     */
    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(Solicitud::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function actorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    /**
     * @return BelongsTo<Agente, $this>
     */
    public function actorAgente(): BelongsTo
    {
        return $this->belongsTo(Agente::class, 'actor_agente_id');
    }

    /**
     * @return BelongsTo<Delegacion, $this>
     */
    public function delegacion(): BelongsTo
    {
        return $this->belongsTo(Delegacion::class);
    }
}
