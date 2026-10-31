<?php

namespace Database\Factories;

use App\Models\Resolucion;
use App\Models\Solicitud;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Resolucion>
 */
class ResolucionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'numero' => $this->faker->unique()->bothify('RES-####-202?'),
            'fecha_emision' => $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'detalle' => $this->faker->optional()->paragraph(),
            'solicitud_id' => Solicitud::factory(),
        ];
    }

    /**
     * Resolución diaria que agrupa varias licencias.
     */
    public function daily(): static
    {
        return $this->state(fn (): array => [
            'solicitud_id' => null,
            'fecha_emision' => today(config('app.business_timezone'))->toDateString(),
        ]);
    }
}
