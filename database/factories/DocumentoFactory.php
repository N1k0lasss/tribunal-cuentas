<?php

namespace Database\Factories;

use App\Models\Agente;
use App\Models\Resolucion;
use App\Models\Solicitud;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Documento>
 */
class DocumentoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre_archivo' => $this->faker->regexify('[A-Za-z0-9]{10}') . '.pdf',
            'ruta' => 'documentos/' . $this->faker->uuid() . '.pdf',
            'tipo_documento' => $this->faker->randomElement(['Certificado Médico', 'DNI', 'Nota Interna', 'Justificativo']),
            'descripcion' => $this->faker->optional()->sentence(),
            'solicitud_id' => $this->faker->boolean(70) ? Solicitud::factory() : null,
            'resolucion_id' => $this->faker->boolean(30) ? Resolucion::factory() : null,
            'agente_id' => Agente::factory(),
        ];
    }
}
