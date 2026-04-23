<?php

namespace Database\Factories;

use App\Models\Agente;
use App\Models\Solicitud;
use App\Models\TipoSolicitud;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Solicitud>
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
        $fechaInicio = Carbon::instance($this->faker->dateTimeBetween('now', '+2 months'))->startOfDay();
        $diasSolicitados = $this->faker->numberBetween(1, 15);
        $fechaFin = $fechaInicio->copy()->addDays($diasSolicitados);

        return [
            'agente_id' => Agente::factory(),
            'tipo_solicitud_id' => TipoSolicitud::factory()->state([
                'unidad_medida' => 'dias',
                'tipo_dias' => 'corridos',
                'duracion_maxima_dias' => max(30, $diasSolicitados + 10),
                'requiere_intervencion_medica' => false,
            ]),
            'fecha_solicitud' => $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'fecha_inicio' => $fechaInicio->format('Y-m-d'),
            'fecha_fin' => $fechaFin->format('Y-m-d'),
            'dias_solicitados' => $diasSolicitados,
            'motivo' => $this->faker->optional()->sentence(),
            'estado' => $this->faker->randomElement(['pendiente', 'en_revision', 'aprobado', 'rechazado']),
        ];
    }
}
