<?php

namespace Database\Factories;

use App\Models\Agente;
use App\Models\Saldo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Saldo>
 */
class SaldoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $otorgados = $this->faker->numberBetween(15, 35);
        $usados = $this->faker->numberBetween(0, $otorgados);

        return [
            'agente_id' => Agente::factory(),
            'anio' => $this->faker->numberBetween(2022, 2026),
            'tipo_saldo' => $this->faker->randomElement(['Licencia Anual Ordinaria', 'Franco Compensatorio', 'Licencia por Estudio']),
            'dias_otorgados' => $otorgados,
            'dias_usados' => $usados,
        ];
    }
}
