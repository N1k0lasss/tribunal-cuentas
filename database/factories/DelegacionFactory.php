<?php

namespace Database\Factories;

use App\Models\Delegacion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Delegacion>
 */
class DelegacionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $inicio = now()->subHour();

        return [
            'titular_user_id' => User::factory(),
            'delegado_user_id' => User::factory(),
            'etapa' => $this->faker->randomElement(['jefatura', 'secretaria', 'direccion']),
            'vigente_desde' => $inicio,
            'vigente_hasta' => $inicio->copy()->addDays(5),
            'motivo' => $this->faker->sentence(),
            'activa' => true,
            'creada_por_user_id' => null,
        ];
    }
}
