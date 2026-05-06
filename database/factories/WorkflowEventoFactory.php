<?php

namespace Database\Factories;

use App\Models\Solicitud;
use App\Models\WorkflowEvento;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkflowEvento>
 */
class WorkflowEventoFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'solicitud_id' => Solicitud::factory(),
            'evento' => $this->faker->randomElement(['solicitud_creada', 'solicitud_resuelta']),
            'etapa' => $this->faker->randomElement(['jefatura', 'secretaria', 'direccion']),
            'estado_anterior' => 'pendiente',
            'estado_nuevo' => 'en_revision',
            'actor_user_id' => null,
            'actor_agente_id' => null,
            'es_delegacion' => false,
            'delegacion_id' => null,
            'detalle' => $this->faker->sentence(),
            'ip' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'ocurrio_en' => now(),
        ];
    }
}
