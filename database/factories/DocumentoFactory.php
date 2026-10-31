<?php

namespace Database\Factories;

use App\Models\Agente;
use App\Models\Documento;
use App\Models\Resolucion;
use App\Models\Solicitud;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @extends Factory<Documento>
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
            'nombre_archivo' => $this->faker->regexify('[A-Za-z0-9]{10}').'.pdf',
            'ruta' => 'documentos/'.$this->faker->uuid().'.pdf',
            'tipo_documento' => $this->faker->randomElement(['Certificado Médico', 'DNI', 'Nota Interna', 'Justificativo']),
            'descripcion' => $this->faker->optional()->sentence(),
            'solicitud_id' => $this->faker->boolean(70) ? Solicitud::factory() : null,
            'resolucion_id' => $this->faker->boolean(30) ? Resolucion::factory() : null,
            'agente_id' => Agente::factory(),
        ];
    }

    public function withFile(?string $contenido = null, string $extension = 'pdf'): static
    {
        return $this
            ->state(fn (): array => [
                'ruta' => 'documentos/'.Str::uuid().'.'.$extension,
                'nombre_archivo' => 'documento.'.$extension,
            ])
            ->afterCreating(function (Documento $documento) use ($contenido, $extension): void {
                $contenido ??= $extension === 'pdf'
                    ? "%PDF-1.4\n1 0 obj\n<<>>\nendobj\ntrailer\n<<>>\n%%EOF\n"
                    : base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Y9ZQmcAAAAASUVORK5CYII=');

                Storage::disk('local')->put($documento->ruta, $contenido);
            });
    }
}
