<?php

namespace Database\Factories;

use App\Models\TipoSolicitud;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TipoSolicitud>
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
                'Licencia por Razones Particulares',
            ]),
            'categoria' => $this->faker->randomElement(['Ordinaria', 'Extraordinaria', 'Especial']),
            'articulo_legal' => 'Art. '.$this->faker->numberBetween(1, 100).' Inc. '.$this->faker->randomLetter(),
            'duracion_maxima_dias' => $this->faker->randomElement([30, 60, 90, 180]),
            'unidad_medida' => 'dias',
            'tipo_dias' => 'corridos',
            'goce_sueldo_porcentaje' => 100,
            'requiere_intervencion_medica' => false,
            'limite_mensual_dias_horas' => null,
            'requiere_adjunto' => $this->faker->boolean(40),
        ];
    }
}
