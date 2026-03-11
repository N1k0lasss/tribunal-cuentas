<?php

namespace Database\Factories;

use App\Models\Agente;
use App\Models\TipoSolicitud;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Solicitud>
 */
class SolicitudFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fechaInicio = $this->faker->dateTimeBetween('now', '+2 months');
        $diasSolicitados = $this->faker->numberBetween(1, 15);
        $fechaFin = (clone $fechaInicio)->modify('+' . $diasSolicitados . ' days');

        return [
            'agente_id' => Agente::factory(),
            'tipo_solicitud_id' => TipoSolicitud::factory(),
            'fecha_solicitud' => $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'fecha_inicio' => $fechaInicio->format('Y-m-d'),
            'fecha_fin' => $fechaFin->format('Y-m-d'),
            'dias_solicitados' => $diasSolicitados,
            'motivo' => $this->faker->optional()->sentence(),
            'estado' => $this->faker->randomElement(['pendiente', 'en_revision', 'aprobado', 'rechazado']),
        ];
    }
}
