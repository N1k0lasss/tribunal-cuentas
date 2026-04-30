<?php

namespace Database\Factories;

use App\Models\Agente;
use App\Models\Area;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Agente>
 */
class AgenteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => $this->faker->firstName(),
            'apellido' => $this->faker->lastName(),
            'dni' => $this->faker->unique()->numerify('########'),
            'cargo' => $this->faker->jobTitle(),
            'tipo_contrato' => $this->faker->randomElement(['Planta Permanente', 'Contrato de Locación', 'Planta Transitoria']),
            'area_id' => Area::factory(),
            'fecha_ingreso' => $this->faker->dateTimeBetween('-10 years', 'now')->format('Y-m-d'),
        ];
    }
}
