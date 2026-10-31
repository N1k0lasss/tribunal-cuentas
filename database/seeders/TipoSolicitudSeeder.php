<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoSolicitudSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $tipos = [
            // CAPÍTULO I - Recesos
            [
                'nombre' => 'Receso de Enero',
                'categoria' => 'Receso',
                'articulo_legal' => 'ART 3',
                'duracion_maxima_dias' => 31,
                'unidad_medida' => 'dias',
                'tipo_dias' => 'corridos',
                'goce_sueldo_porcentaje' => 100,
                'requiere_intervencion_medica' => false,
                'limite_mensual_dias_horas' => null,
                'antiguedad_minima_meses' => 6,
                'requiere_adjunto' => false,
            ],
            [
                'nombre' => 'Receso de Julio',
                'categoria' => 'Receso',
                'articulo_legal' => 'ART 3',
                'duracion_maxima_dias' => 10,
                'unidad_medida' => 'dias',
                'tipo_dias' => 'habiles',
                'goce_sueldo_porcentaje' => 100,
                'requiere_intervencion_medica' => false,
                'limite_mensual_dias_horas' => null,
                'antiguedad_minima_meses' => 6,
                'requiere_adjunto' => false,
            ],

            // CAPÍTULO II - SALUD Y MATERNIDAD
            [
                'nombre' => 'Enfermedad Común (Etapa 1 - 100%)',
                'categoria' => 'Salud y Maternidad',
                'articulo_legal' => 'ART 6',
                'duracion_maxima_dias' => 30, // por año calendario
                'unidad_medida' => 'dias',
                'tipo_dias' => 'corridos',
                'goce_sueldo_porcentaje' => 100,
                'requiere_intervencion_medica' => true,
                'limite_mensual_dias_horas' => null,
                'requiere_adjunto' => true,
            ],
            [
                'nombre' => 'Enfermedad Común (Etapa 2 - 75%)',
                'categoria' => 'Salud y Maternidad',
                'articulo_legal' => 'ART 6',
                'duracion_maxima_dias' => 30, // adicionales
                'unidad_medida' => 'dias',
                'tipo_dias' => 'corridos',
                'goce_sueldo_porcentaje' => 75,
                'requiere_intervencion_medica' => true,
                'limite_mensual_dias_horas' => null,
                'requiere_adjunto' => true,
            ],
            [
                'nombre' => 'Enfermedad Común (Etapa 3 - Sin Goce)',
                'categoria' => 'Salud y Maternidad',
                'articulo_legal' => 'ART 6',
                'duracion_maxima_dias' => 30, // adicionales
                'unidad_medida' => 'dias',
                'tipo_dias' => 'corridos',
                'goce_sueldo_porcentaje' => 0,
                'requiere_intervencion_medica' => true,
                'limite_mensual_dias_horas' => null,
                'requiere_adjunto' => true,
            ],
            [
                'nombre' => 'Enfermedad Grave o Largo Tratamiento',
                'categoria' => 'Salud y Maternidad',
                'articulo_legal' => 'ART 10',
                'duracion_maxima_dias' => 730, // Hasta 2 años
                'unidad_medida' => 'dias',
                'tipo_dias' => 'corridos',
                'goce_sueldo_porcentaje' => 100,
                'requiere_intervencion_medica' => true,
                'limite_mensual_dias_horas' => null,
                'requiere_adjunto' => true,
            ],
            [
                'nombre' => 'Enfermedad de Familiar',
                'categoria' => 'Salud y Maternidad',
                'articulo_legal' => 'ART 11',
                'duracion_maxima_dias' => 30, // Hasta 30 días anuales (hasta 60 por grave / traslado)
                'unidad_medida' => 'dias',
                'tipo_dias' => 'corridos',
                'goce_sueldo_porcentaje' => 100,
                'requiere_intervencion_medica' => false,
                'limite_mensual_dias_horas' => null,
                'requiere_adjunto' => true, // Adjuntar constancia médica del familiar
            ],
            [
                'nombre' => 'Familiar Discapacitado a Cargo',
                'categoria' => 'Salud y Maternidad',
                'articulo_legal' => 'ART 11 BIS',
                'duracion_maxima_dias' => 90, // Hasta 90 (local) u 180 si es fuera de pcia
                'unidad_medida' => 'dias',
                'tipo_dias' => 'corridos',
                'goce_sueldo_porcentaje' => 100,
                'requiere_intervencion_medica' => true,
                'limite_mensual_dias_horas' => null,
                'requiere_adjunto' => true,
            ],
            [
                'nombre' => 'Maternidad',
                'categoria' => 'Salud y Maternidad',
                'articulo_legal' => 'ART 12',
                'duracion_maxima_dias' => 120, // 100 para anormalidades/grave
                'unidad_medida' => 'dias',
                'tipo_dias' => 'corridos',
                'goce_sueldo_porcentaje' => 100,
                'requiere_intervencion_medica' => true,
                'limite_mensual_dias_horas' => null,
                'requiere_adjunto' => true,
            ],
            [
                'nombre' => 'Enfermedad Profesional / Accidente Trabajo',
                'categoria' => 'Salud y Maternidad',
                'articulo_legal' => 'ART 13',
                'duracion_maxima_dias' => 730, // 2 años
                'unidad_medida' => 'dias',
                'tipo_dias' => 'corridos',
                'goce_sueldo_porcentaje' => 100,
                'requiere_intervencion_medica' => true,
                'limite_mensual_dias_horas' => null,
                'requiere_adjunto' => true,
            ],

            // CAPÍTULO III - LICENCIAS EXTRAORDINARIAS
            [
                'nombre' => 'Cargos Políticos',
                'categoria' => 'Extraordinaria',
                'articulo_legal' => 'ART 14',
                'duracion_maxima_dias' => null, // Lo que dure el mandato + 15 dias post
                'unidad_medida' => 'dias',
                'tipo_dias' => 'corridos',
                'goce_sueldo_porcentaje' => 0,
                'requiere_intervencion_medica' => false,
                'limite_mensual_dias_horas' => null,
                'antiguedad_minima_meses' => 6,
                'requiere_adjunto' => true,
            ],
            [
                'nombre' => 'Cargo de Mayor Jerarquía',
                'categoria' => 'Extraordinaria',
                'articulo_legal' => 'ART 15',
                'duracion_maxima_dias' => null, // Lo que dure designación + 10 días post
                'unidad_medida' => 'dias',
                'tipo_dias' => 'corridos',
                'goce_sueldo_porcentaje' => 0,
                'requiere_intervencion_medica' => false,
                'limite_mensual_dias_horas' => null,
                'antiguedad_minima_meses' => 6,
                'requiere_adjunto' => true,
            ],
            [
                'nombre' => 'Rendir Examen',
                'categoria' => 'Extraordinaria',
                'articulo_legal' => 'ART 16',
                'duracion_maxima_dias' => 10, // Max 10 por año, 5 por cada materia sumables
                'unidad_medida' => 'dias',
                'tipo_dias' => 'habiles',
                'goce_sueldo_porcentaje' => 100,
                'requiere_intervencion_medica' => false,
                'limite_mensual_dias_horas' => null,
                'requiere_adjunto' => true, // Certificado examen
            ],
            [
                'nombre' => 'Estudio y Capacitación',
                'categoria' => 'Extraordinaria',
                'articulo_legal' => 'ART 18',
                'duracion_maxima_dias' => null,
                'unidad_medida' => 'dias',
                'tipo_dias' => 'corridos',
                'goce_sueldo_porcentaje' => 100,
                'requiere_intervencion_medica' => false,
                'limite_mensual_dias_horas' => null,
                'antiguedad_minima_meses' => 12,
                'requiere_adjunto' => true,
            ],
            [
                'nombre' => 'Actividades Deportivas',
                'categoria' => 'Extraordinaria',
                'articulo_legal' => 'ART 19',
                'duracion_maxima_dias' => null, // Según duración
                'unidad_medida' => 'dias',
                'tipo_dias' => 'corridos',
                'goce_sueldo_porcentaje' => 100,
                'requiere_intervencion_medica' => false,
                'limite_mensual_dias_horas' => null,
                'requiere_adjunto' => true,
            ],
            [
                'nombre' => 'Matrimonio del Agente',
                'categoria' => 'Extraordinaria',
                'articulo_legal' => 'ART 20',
                'duracion_maxima_dias' => 10,
                'unidad_medida' => 'dias',
                'tipo_dias' => 'habiles',
                'goce_sueldo_porcentaje' => 100,
                'requiere_intervencion_medica' => false,
                'limite_mensual_dias_horas' => null,
                'requiere_adjunto' => true,
            ],
            [
                'nombre' => 'Matrimonio de Hijos, Hermanos, Padres',
                'categoria' => 'Extraordinaria',
                'articulo_legal' => 'ART 20',
                'duracion_maxima_dias' => 2,
                'unidad_medida' => 'dias',
                'tipo_dias' => 'habiles',
                'goce_sueldo_porcentaje' => 100,
                'requiere_intervencion_medica' => false,
                'limite_mensual_dias_horas' => null,
                'requiere_adjunto' => true,
            ],
            [
                'nombre' => 'Nacimiento de Hijo',
                'categoria' => 'Extraordinaria',
                'articulo_legal' => 'ART 20',
                'duracion_maxima_dias' => 5,
                'unidad_medida' => 'dias',
                'tipo_dias' => 'habiles',
                'goce_sueldo_porcentaje' => 100,
                'requiere_intervencion_medica' => false,
                'limite_mensual_dias_horas' => null,
                'requiere_adjunto' => true,
            ],
            [
                'nombre' => 'Fallecimiento (Cónyuge, hijos, padres, suegros, hermanos)',
                'categoria' => 'Extraordinaria',
                'articulo_legal' => 'ART 20',
                'duracion_maxima_dias' => 5,
                'unidad_medida' => 'dias',
                'tipo_dias' => 'habiles',
                'goce_sueldo_porcentaje' => 100,
                'requiere_intervencion_medica' => false,
                'limite_mensual_dias_horas' => null,
                'requiere_adjunto' => true,
            ],
            [
                'nombre' => 'Fallecimiento (Cuñados, yernos, nueras)',
                'categoria' => 'Extraordinaria',
                'articulo_legal' => 'ART 20',
                'duracion_maxima_dias' => 3,
                'unidad_medida' => 'dias',
                'tipo_dias' => 'habiles',
                'goce_sueldo_porcentaje' => 100,
                'requiere_intervencion_medica' => false,
                'limite_mensual_dias_horas' => null,
                'requiere_adjunto' => true,
            ],
            [
                'nombre' => 'Fallecimiento (Otros parentescos hasta 4to grado)',
                'categoria' => 'Extraordinaria',
                'articulo_legal' => 'ART 20',
                'duracion_maxima_dias' => 2,
                'unidad_medida' => 'dias',
                'tipo_dias' => 'habiles',
                'goce_sueldo_porcentaje' => 100,
                'requiere_intervencion_medica' => false,
                'limite_mensual_dias_horas' => null,
                'requiere_adjunto' => true,
            ],
            [
                'nombre' => 'Razones Particulares',
                'categoria' => 'Extraordinaria',
                'articulo_legal' => 'ART 21',
                'duracion_maxima_dias' => 540, // 18 meses
                'unidad_medida' => 'dias',
                'tipo_dias' => 'corridos',
                'goce_sueldo_porcentaje' => 0,
                'requiere_intervencion_medica' => false,
                'limite_mensual_dias_horas' => null,
                'antiguedad_minima_meses' => 12,
                'requiere_adjunto' => false, // Opcional, dependiendo de la solicitud interna
            ],

            // CAPÍTULO IV - JUSTIFICACIÓN Y FRANQUICIAS
            [
                'nombre' => 'Donación de Sangre / Extracción Dentaria',
                'categoria' => 'Justificación',
                'articulo_legal' => 'ART 22',
                'duracion_maxima_dias' => 1, // Max 1 dia cada 3 meses
                'unidad_medida' => 'dias',
                'tipo_dias' => 'corridos',
                'goce_sueldo_porcentaje' => 100,
                'requiere_intervencion_medica' => true, // Para validar
                'limite_mensual_dias_horas' => null,
                'requiere_adjunto' => true,
            ],
            [
                'nombre' => 'Fenómenos Meteorológicos / Fuerza Mayor',
                'categoria' => 'Justificación',
                'articulo_legal' => 'ART 22',
                'duracion_maxima_dias' => 1,
                'unidad_medida' => 'dias',
                'tipo_dias' => 'corridos',
                'goce_sueldo_porcentaje' => 100,
                'requiere_intervencion_medica' => false,
                'limite_mensual_dias_horas' => null,
                'requiere_adjunto' => false, // Opcionalmente pueden pedir justificación de transporte
            ],
            [
                'nombre' => 'Franquicia por Estudio',
                'categoria' => 'Franquicia',
                'articulo_legal' => 'ART 23',
                'duracion_maxima_dias' => null,
                'unidad_medida' => 'horas',
                'tipo_dias' => null,
                'goce_sueldo_porcentaje' => 100,
                'requiere_intervencion_medica' => false,
                'limite_mensual_dias_horas' => 2, // Horas diarias maximas aunque es 2:30, guardamos aproximado y validamos por código
                'requiere_adjunto' => true, // Constancia regular
            ],
            [
                'nombre' => 'Franquicia por Maternidad (Lactancia)',
                'categoria' => 'Franquicia',
                'articulo_legal' => 'ART 23',
                'duracion_maxima_dias' => null,
                'unidad_medida' => 'horas',
                'tipo_dias' => null,
                'goce_sueldo_porcentaje' => 100,
                'requiere_intervencion_medica' => false,
                'limite_mensual_dias_horas' => 2, // Horas diarias
                'requiere_adjunto' => false, // Predefinida con Acta Nacimiento inicial
            ],
            [
                'nombre' => 'Franquicia por Razones Particulares',
                'categoria' => 'Franquicia',
                'articulo_legal' => 'ART 23',
                'duracion_maxima_dias' => null,
                'unidad_medida' => 'horas',
                'tipo_dias' => null,
                'goce_sueldo_porcentaje' => 100,
                'requiere_intervencion_medica' => false,
                'limite_mensual_dias_horas' => 5, // 5 horas mensuales max (limite 2 horas dia)
                'requiere_adjunto' => false,
            ],

            // CAPÍULO V - TARDANZAS / INASISTENCIAS (Opcionales para uso de RRHH internamente)
            [
                'nombre' => 'Tardanza (hasta 30 min)',
                'categoria' => 'Falta/Tardanza',
                'articulo_legal' => 'ART 24',
                'duracion_maxima_dias' => null,
                'unidad_medida' => 'horas',
                'tipo_dias' => null,
                'goce_sueldo_porcentaje' => 0, // Descuentan dia c/3 tardanzas
                'requiere_intervencion_medica' => false,
                'limite_mensual_dias_horas' => null,
                'requiere_adjunto' => false,
            ],
            [
                'nombre' => 'Inasistencia Injustificada',
                'categoria' => 'Falta/Tardanza',
                'articulo_legal' => 'ART 25',
                'duracion_maxima_dias' => 1,
                'unidad_medida' => 'dias',
                'tipo_dias' => 'corridos',
                'goce_sueldo_porcentaje' => 0,
                'requiere_intervencion_medica' => false,
                'limite_mensual_dias_horas' => null,
                'requiere_adjunto' => false,
            ],
        ];

        foreach ($tipos as $tipo) {
            $tipo['created_at'] = $now;
            $tipo['updated_at'] = $now;
            DB::table('tipo_solicituds')->insert($tipo);
        }
    }
}
