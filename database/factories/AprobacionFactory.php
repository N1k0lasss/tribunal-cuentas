<?php

namespace Database\Factories;

use App\Models\Agente;
use App\Models\Solicitud;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Aprobacion>
 */
class AprobacionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'solicitud_id' => Solicitud::factory(),
            'aprobado_por' => Agente::factory(),
            'etapa' => $this->faker->randomElement(['jefatura', 'secretaria', 'direccion']),
            'estado' => $this->faker->randomElement(['pendiente', 'aprobado', 'rechazado']),
            'observacion' => $this->faker->optional()->sentence(),
            'fecha' => $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
        ];
    }
}
