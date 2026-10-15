<?php

namespace Database\Factories;

use App\Models\Area;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Area>
 */
class AreaFactory extends Factory
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
                'Recursos Humanos',
                'Administración',
                'Contabilidad',
                'Legales',
                'Sistemas',
                'Auditoría',
                'Presidencia',
                'Vocalía M',
                'Vocalía R',
                'Secretaría General',
            ]),
        ];
    }
}
