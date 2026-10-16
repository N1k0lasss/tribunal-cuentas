<?php

namespace App\Console\Commands;

use App\Exceptions\LegajoApiException;
use App\Models\Agente;
use App\Models\Area;
use App\Services\LegajoApiService;
use Illuminate\Console\Command;

/**
 * Sincroniza la tabla local `agentes` con la API del Sistema de Legajos.
 *
 * `agentes` es solo la referencia de identidad (dni + vínculo con el legajo);
 * los datos que dependen del legajo (nombre, apellido, cargo, área, fecha de
 * ingreso) se importan desde la API, que es la fuente de verdad
 * (integracion-legajos-api.md §2.1). Idempotente: se puede re-ejecutar.
 */
class SyncLegajosCommand extends Command
{
    /**
     * Equivalencia código → etiqueta del tipo de contrato, tal como la
     * clasifica el Sistema de Legajos (confirmado por el equipo de Legajos
     * el 07/10/2026). La API envía `legajo.tipo_contrato` como texto
     * ("Funcionario", "Locación", "Permanente"); antes lo enviaba como código
     * numérico (0/1/2), por eso se mantiene el mapa como respaldo. El valor
     * se guarda como `agentes.situacion_revista`.
     */
    private const TIPOS_CONTRATO = [
        0 => 'Funcionario',
        1 => 'Locación',
        2 => 'Permanente',
    ];

    protected $signature = 'legajos:sync
        {--dni= : Sincroniza solamente un DNI puntual}';

    protected $description = 'Importa o actualiza los agentes locales desde la API del Sistema de Legajos';

    public function handle(LegajoApiService $legajos): int
    {
        try {
            $personas = $legajos->listarPersonas();
        } catch (LegajoApiException $e) {
            // El comando lo usa un admin: mostramos el detalle técnico si lo hay
            // (la ficha, en cambio, muestra el mensaje limpio).
            $this->error('No se pudo consultar la API de Legajos: '.($e->detalle() ?? $e->getMessage()));

            return self::FAILURE;
        }

        $dni = $this->option('dni');

        if (filled($dni)) {
            $personas = array_values(
                array_filter($personas, fn (array $p): bool => (string) ($p['dni'] ?? '') === (string) $dni),
            );
        }

        if ($personas === []) {
            $this->warn(filled($dni)
                ? "El DNI {$dni} no existe en la API de Legajos."
                : 'La API de Legajos no devolvió ninguna persona.');

            return self::FAILURE;
        }

        $this->info(sprintf('Personas a sincronizar: %d', count($personas)));
        $this->newLine();

        $contadores = [
            'creados' => 0,
            'actualizados' => 0,
            'sin_cambios' => 0,
            'bajas' => 0,
            'sin_legajo' => 0,
            'errores' => 0,
        ];
        $sinLegajo = [];
        $tiposSinMapear = [];
        $bajas = [];

        foreach ($personas as $persona) {
            $dniPersona = (string) ($persona['dni'] ?? '');

            if ($dniPersona === '') {
                $contadores['errores']++;
                $this->warn('Persona sin DNI: se omite.');

                continue;
            }

            try {
                $datos = $legajos->obtenerPorDni($dniPersona);
            } catch (LegajoApiException $e) {
                $contadores['errores']++;
                $this->warn("DNI {$dniPersona}: ".($e->detalle() ?? $e->getMessage()));

                continue;
            }

            if ($datos === null) {
                $contadores['errores']++;
                $this->warn("DNI {$dniPersona}: no existe en la API (cambió desde el listado), se omite.");

                continue;
            }

            $legajo = $datos['legajo'] ?? null;

            if ($legajo === null) {
                $contadores['sin_legajo']++;
                $sinLegajo[] = $dniPersona;
            }

            // El área se mapea por nombre (no hay tabla de equivalencias entre
            // áreas locales y del legajo). Sin legajo activo va a "Sin asignar".
            $area = Area::firstOrCreate([
                'nombre' => $legajo['area']['nombre'] ?? 'Sin asignar',
            ]);

            // tipo_contrato llega como texto ("Funcionario", "Locación",
            // "Permanente"); se admite también el código numérico viejo (0/1/2).
            $tipoContrato = $legajo['tipo_contrato'] ?? null;
            $situacionRevista = null;

            if ($tipoContrato !== null) {
                $situacionRevista = self::etiquetaTipoContrato($tipoContrato);

                if ($situacionRevista === null) {
                    $tiposSinMapear[(string) $tipoContrato] = true;
                }
            }

            // Baja del legajo: `estado` 0 = dado de baja. Se importan igual
            // (para poder verlos), pero marcados, con motivo y fecha de baja.
            $baja = $legajo['baja'] ?? null;
            $estadoLegajo = ($legajo['estado'] ?? null) !== null ? (int) $legajo['estado'] : null;

            if ($estadoLegajo === 0) {
                $contadores['bajas']++;
                $bajas[] = $dniPersona;
            }

            $agente = Agente::updateOrCreate(['dni' => $dniPersona], [
                // apellido/nombre: por-dni ya los trae; si faltan, se usa el listado.
                'nombre' => $datos['persona']['nombre'] ?? $persona['nombre'] ?? '',
                'apellido' => $datos['persona']['apellido'] ?? $persona['apellido'] ?? '',
                'cargo' => $legajo['cargo']['nombre'] ?? null,
                'area_id' => $area->id,
                'fecha_ingreso' => $legajo['fecha_ingreso'] ?? null,
                'situacion_revista' => $situacionRevista,
                'estado_legajo' => $estadoLegajo,
                'fecha_baja' => $baja['fecha'] ?? null,
                'motivo_baja' => $baja['motivo'] ?? null,
            ]);

            if ($agente->wasRecentlyCreated) {
                $contadores['creados']++;
                $this->line(sprintf('  + Creado  DNI %-10s %s %s', $dniPersona, $agente->apellido, $agente->nombre));
            } elseif ($agente->wasChanged()) {
                $contadores['actualizados']++;
                $this->line(sprintf('  ~ Act.    DNI %-10s %s %s', $dniPersona, $agente->apellido, $agente->nombre));
            } else {
                $contadores['sin_cambios']++;
            }
        }

        $this->newLine();
        $this->table(
            ['Creados', 'Actualizados', 'Sin cambios', 'Bajas', 'Sin legajo activo', 'Errores'],
            [[
                $contadores['creados'],
                $contadores['actualizados'],
                $contadores['sin_cambios'],
                $contadores['bajas'],
                $contadores['sin_legajo'],
                $contadores['errores'],
            ]],
        );

        if ($sinLegajo !== []) {
            $this->warn('Sin legajo activo (sin cargo, área ni fecha de ingreso): '.implode(', ', $sinLegajo));
        }

        if ($bajas !== []) {
            $this->warn('Legajos dados de baja (se importan marcados como "Baja"): '.implode(', ', $bajas));
        }

        if ($tiposSinMapear !== []) {
            $this->warn('Valores de tipo_contrato sin mapear (situacion_revista queda vacía): '.implode(', ', array_keys($tiposSinMapear)));
        }

        $procesados = $contadores['creados'] + $contadores['actualizados'] + $contadores['sin_cambios'];

        if ($procesados === 0) {
            $this->error('No se sincronizó ningún agente.');

            return self::FAILURE;
        }

        $this->info("Sincronización finalizada: {$procesados} agente(s) procesados.");

        return self::SUCCESS;
    }

    /**
     * Traduce el `tipo_contrato` de la API a la etiqueta de `situacion_revista`.
     * La API lo envía como texto ("Funcionario", "Locación", "Permanente");
     * se acepta también el código numérico viejo (0/1/2). Devuelve null si no
     * se reconoce (el comando lo avisa y deja la situación vacía).
     */
    private static function etiquetaTipoContrato(mixed $tipo): ?string
    {
        if (is_string($tipo)) {
            $tipo = trim($tipo);

            return in_array($tipo, self::TIPOS_CONTRATO, true) ? $tipo : null;
        }

        if (is_numeric($tipo)) {
            return self::TIPOS_CONTRATO[(int) $tipo] ?? null;
        }

        return null;
    }
}
