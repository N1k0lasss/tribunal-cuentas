<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TipoSolicitud>
 */
class TipoSolicitudFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => $this->faker->randomElement([
                'Licencia Anual Ordinaria',
                'Licencia por Enfermedad',
                'Franco Compensatorio',
                'Licencia por Maternidad',
                'Licencia por Examen',
                'Licencia por Razones Particulares'
            ]),
            'categoria' => $this->faker->randomElement(['Ordinaria', 'Extraordinaria', 'Especial']),
            'articulo_legal' => 'Art. ' . $this->faker->numberBetween(1, 100) . ' Inc. ' . $this->faker->randomLetter(),
            'duracion_maxima_dias' => $this->faker->randomElement([5, 10, 15, 30, 90, 180]),
            'requiere_adjunto' => $this->faker->boolean(40),
        ];
    }
}
