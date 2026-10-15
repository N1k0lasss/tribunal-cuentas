<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Feriados;
use Carbon\Carbon;

class FeriadosArgentinaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $year = 2026;

        $feriados = [
            // Feriados Nacionales Inamovibles
            ['nombre' => 'Año Nuevo', 'fecha' => "$year-01-01", 'tipo' => 'Nacional', 'trasladable' => false, 'recurrente' => true],
            ['nombre' => 'Día Nacional de la Memoria por la Verdad y la Justicia', 'fecha' => "$year-03-24", 'tipo' => 'Nacional', 'trasladable' => false, 'recurrente' => true],
            ['nombre' => 'Día del Veterano y de los Caídos en la Guerra de Malvinas', 'fecha' => "$year-04-02", 'tipo' => 'Nacional', 'trasladable' => false, 'recurrente' => true],
            ['nombre' => 'Día del Trabajador', 'fecha' => "$year-05-01", 'tipo' => 'Nacional', 'trasladable' => false, 'recurrente' => true],
            ['nombre' => 'Día de la Revolución de Mayo', 'fecha' => "$year-05-25", 'tipo' => 'Nacional', 'trasladable' => false, 'recurrente' => true],
            ['nombre' => 'Paso a la Inmortalidad del Gral. Don Martín Miguel de Güemes', 'fecha' => "$year-06-17", 'tipo' => 'Nacional', 'trasladable' => true, 'recurrente' => true],
            ['nombre' => 'Paso a la Inmortalidad del Gral. Manuel Belgrano', 'fecha' => "$year-06-20", 'tipo' => 'Nacional', 'trasladable' => false, 'recurrente' => true],
            ['nombre' => 'Día de la Independencia', 'fecha' => "$year-07-09", 'tipo' => 'Nacional', 'trasladable' => false, 'recurrente' => true],
            ['nombre' => 'Paso a la Inmortalidad del Gral. José de San Martín', 'fecha' => "$year-08-17", 'tipo' => 'Nacional', 'trasladable' => true, 'recurrente' => true],
            ['nombre' => 'Día del Respeto a la Diversidad Cultural', 'fecha' => "$year-10-12", 'tipo' => 'Nacional', 'trasladable' => true, 'recurrente' => true],
            ['nombre' => 'Día de la Soberanía Nacional', 'fecha' => "$year-11-20", 'tipo' => 'Nacional', 'trasladable' => true, 'recurrente' => true],
            ['nombre' => 'Inmaculada Concepción de María', 'fecha' => "$year-12-08", 'tipo' => 'Nacional', 'trasladable' => false, 'recurrente' => true],
            ['nombre' => 'Navidad', 'fecha' => "$year-12-25", 'tipo' => 'Nacional', 'trasladable' => false, 'recurrente' => true],

            // Feriados Provinciales de Santiago del Estero
            ['nombre' => 'Día de la Autonomía Provincial (Santiago del Estero)', 'fecha' => "$year-04-27", 'tipo' => 'Provincial', 'trasladable' => false, 'recurrente' => true],
            ['nombre' => 'Día de la Fundación de Santiago del Estero', 'fecha' => "$year-07-25", 'tipo' => 'Provincial', 'trasladable' => false, 'recurrente' => true],

            // Feriados Específicos del año (Ejemplo: Carnaval y Semana Santa para 2026)
            ['nombre' => 'Carnaval', 'fecha' => "$year-02-16", 'tipo' => 'Nacional', 'trasladable' => false, 'recurrente' => false],
            ['nombre' => 'Carnaval', 'fecha' => "$year-02-17", 'tipo' => 'Nacional', 'trasladable' => false, 'recurrente' => false],
            ['nombre' => 'Jueves Santo', 'fecha' => "$year-04-02", 'tipo' => 'Nacional', 'trasladable' => false, 'recurrente' => false],
            ['nombre' => 'Viernes Santo', 'fecha' => "$year-04-03", 'tipo' => 'Nacional', 'trasladable' => false, 'recurrente' => false],
        ];

        foreach ($feriados as $feriado) {
            $fecha = Carbon::parse($feriado['fecha']);
            Feriados::updateOrCreate(
                [
                    'nombre' => $feriado['nombre'],
                    'fecha_inicio' => $fecha->startOfDay()->toDateTimeString(),
                    'fecha_fin' => $fecha->endOfDay()->toDateTimeString(),
                ],
                [
                    'todo_el_dia' => true,
                    'tipo' => $feriado['tipo'],
                    'trasladable' => $feriado['trasladable'],
                    'recurrente' => $feriado['recurrente'],
                ]
            );
        }
    }
}
