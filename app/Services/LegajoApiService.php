<?php

namespace App\Services;

use App\Exceptions\LegajoApiException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Cliente de la API del Sistema de Legajos (fuente de verdad de los datos
 * del agente). Ver integracion-legajos-api.md.
 *
 * Configuración en .env: LEGAJOS_API_URL, LEGAJOS_API_TOKEN,
 * LEGAJOS_API_VERIFY_SSL y LEGAJOS_API_TIMEOUT.
 *
 * Fase actual: solo consulta de datos (GET). El alta de licencias
 * aprobadas (POST .../licencias) se incorporará en una próxima fase.
 */
class LegajoApiService
{
    /**
     * Memoria corta por DNI: una misma pantalla (varias entradas del infolist)
     * no debe disparar varias llamadas iguales a la API. Se memorizan las
     * respuestas exitosas (y los 404) durante MEMORIA_SEGUNDOS y los fallos
     * durante ERRORES_SEGUNDOS (así, si la API está caída, la ficha no repite
     * la consulta campo por campo y no se cuelga).
     *
     * @var array<string, array{ts: float, value: array{persona: array<string, mixed>, legajo: array<string, mixed>|null}|null}>
     */
    private array $memoria = [];

    /**
     * Errores recientes por DNI (mensaje apto para el usuario + detalle técnico).
     *
     * @var array<string, array{ts: float, mensaje: string, detalle: string|null}>
     */
    private array $errores = [];

    private const MEMORIA_SEGUNDOS = 30;

    private const ERRORES_SEGUNDOS = 10;

    /**
     * Datos de la persona y su legajo activo por DNI.
     *
     * Normaliza la respuesta de la API: `legajo` viene como lista (aunque
     * el documento lo muestra como objeto) y `fecha_de_ingreso` llega con
     * hora; acá se devuelve como primer legajo y en formato Y-m-d.
     *
     * @return array{persona: array<string, mixed>, legajo: array<string, mixed>|null}|null null si no existe la persona con ese DNI
     *
     * @throws LegajoApiException si la API no responde o el token es inválido
     */
    public function obtenerPorDni(string $dni): ?array
    {
        $dni = trim($dni);

        $this->lanzarErrorReciente($dni);

        $cacheado = $this->memoria[$dni] ?? null;

        if ($cacheado !== null && (microtime(true) - $cacheado['ts']) < self::MEMORIA_SEGUNDOS) {
            return $cacheado['value'];
        }

        try {
            return $this->consultarPorDni($dni);
        } catch (LegajoApiException $e) {
            // Los errores de configuración son permanentes (no dependen de la
            // disponibilidad de la API): esos no se memorizan.
            if ($this->configurado()) {
                $this->errores[$dni] = [
                    'ts' => microtime(true),
                    'mensaje' => $e->getMessage(),
                    'detalle' => $e->detalle(),
                ];
            }

            throw $e;
        }
    }

    /**
     * Consulta real a la API para un DNI (sin caché de memoria).
     *
     * @return array{persona: array<string, mixed>, legajo: array<string, mixed>|null}|null
     *
     * @throws LegajoApiException
     */
    private function consultarPorDni(string $dni): ?array
    {
        $url = rtrim((string) config('services.legajos.url'), '/')
            .'/api/legajos/por-dni/'.urlencode($dni);

        try {
            $response = $this->client()->get($url);
        } catch (ConnectionException $e) {
            throw $this->errorDeConexion($e, "por-dni de DNI {$dni}");
        } catch (RequestException $e) {
            // Red de seguridad: con "throw => false" en el retry no debería llegar acá.
            $response = $e->response;
        }

        if ($response->status() === 404) {
            return $this->memorizar($dni, null);
        }

        if ($response->status() === 401) {
            throw new LegajoApiException(
                'No se pudo consultar la API de Legajos: la credencial fue rechazada (token inválido o vencido).',
                detalle: 'HTTP 401 para '.$url,
            );
        }

        if (! $response->successful()) {
            throw new LegajoApiException(
                'No se pudo consultar la API de Legajos en este momento. Vuelva a intentar en unos minutos.',
                detalle: 'HTTP '.$response->status().' para '.$url,
            );
        }

        $data = $response->json();

        return $this->memorizar($dni, [
            'persona' => $this->normalizarPersona(is_array($data['persona'] ?? null) ? $data['persona'] : []),
            'legajo' => $this->normalizarLegajo($data['legajo'] ?? null),
        ]);
    }

    /**
     * Re-lanza el último error conocido para un DNI si es reciente, sin volver
     * a consultar la API. Evita que la ficha (una consulta por campo) repita
     * la llamada cuando la API está caída.
     *
     * @throws LegajoApiException
     */
    private function lanzarErrorReciente(string $dni): void
    {
        $error = $this->errores[$dni] ?? null;

        if ($error === null || (microtime(true) - $error['ts']) >= self::ERRORES_SEGUNDOS) {
            return;
        }

        throw new LegajoApiException($error['mensaje'], detalle: $error['detalle']);
    }

    /**
     * Arma el error de conexión con un mensaje apto para el usuario, registra
     * el detalle técnico (URL, error de red) en el log y lo adjunta a la
     * excepción para el comando `legajos:sync`.
     */
    private function errorDeConexion(ConnectionException $e, string $contexto): LegajoApiException
    {
        Log::warning('No se pudo conectar con la API de Legajos (' . $contexto . ').', [
            'error' => $e->getMessage(),
        ]);

        return new LegajoApiException(
            'No se pudo consultar la API de Legajos. Vuelva a intentar en unos minutos.',
            previous: $e,
            detalle: $e->getMessage(),
        );
    }

    /**
     * @param  array{persona: array<string, mixed>, legajo: array<string, mixed>|null}|null  $valor
     * @return array{persona: array<string, mixed>, legajo: array<string, mixed>|null}|null
     */
    private function memorizar(string $dni, ?array $valor): ?array
    {
        unset($this->errores[$dni]);

        $this->memoria[$dni] = ['ts' => microtime(true), 'value' => $valor];

        return $valor;
    }

    /**
     * Listado completo de personas del Sistema de Legajos
     * (GET /api/personas).
     *
     * Endpoint extra, fuera del documento de integración (no tiene paginación
     * ni filtro por DNI): se usa para la carga inicial y la sincronización de
     * agentes locales (`php artisan legajos:sync`).
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws LegajoApiException si la API no responde o el token es inválido
     */
    public function listarPersonas(): array
    {
        $url = rtrim((string) config('services.legajos.url'), '/').'/api/personas';

        try {
            $response = $this->client()->get($url);
        } catch (ConnectionException $e) {
            throw $this->errorDeConexion($e, 'listado de personas');
        } catch (RequestException $e) {
            $response = $e->response;
        }

        if ($response->status() === 401) {
            throw new LegajoApiException(
                'No se pudo consultar la API de Legajos: la credencial fue rechazada (token inválido o vencido).',
                detalle: 'HTTP 401 para '.$url,
            );
        }

        if (! $response->successful()) {
            throw new LegajoApiException(
                'No se pudo consultar la API de Legajos en este momento. Vuelva a intentar en unos minutos.',
                detalle: 'HTTP '.$response->status().' para '.$url,
            );
        }

        $personas = $response->json('personas');

        return is_array($personas) ? array_values($personas) : [];
    }

    /**
     * Campos mínimos que necesita el Sistema de Licencias (tabla de la sección
     * 3.1 del documento), aplanados para uso directo.
     *
     * Incluye `estado` y los datos de la baja (`motivo_baja`/`fecha_baja`) del
     * legajo, para validar en vivo que el agente no esté dado de baja.
     *
     * @return array<string, mixed>|null null si no existe la persona con ese DNI
     *
     * @throws LegajoApiException
     */
    public function datosBasicos(string $dni): ?array
    {
        $datos = $this->obtenerPorDni($dni);

        if ($datos === null) {
            return null;
        }

        $persona = $datos['persona'];
        $legajo = $datos['legajo'];

        return [
            'dni' => $persona['dni'] ?? $dni,
            'nombre' => $persona['nombre'] ?? null,
            'apellido' => $persona['apellido'] ?? null,
            'cuil' => $persona['cuil'] ?? null,
            'email' => $persona['email'] ?? null,
            'num_legajo' => $legajo['num_legajo'] ?? null,
            'categoria' => $legajo['categoria']['nombre'] ?? null,
            'categoria_descripcion' => $legajo['categoria']['descripcion'] ?? null,
            'cargo' => $legajo['cargo']['nombre'] ?? null,
            'area' => $legajo['area']['nombre'] ?? null,
            'fecha_ingreso' => isset($legajo['fecha_ingreso']) && $legajo['fecha_ingreso'] !== null
                ? Carbon::parse($legajo['fecha_ingreso'])
                : null,
            'estado' => $legajo['estado'] ?? null,
            'baja' => $legajo['baja'] ?? null,
            'motivo_baja' => $legajo['baja']['motivo'] ?? null,
            'fecha_baja' => $legajo['baja']['fecha'] ?? null,
            'con_legajo_activo' => $legajo !== null,
        ];
    }

    /**
     * Fecha de ingreso del legajo activo (fuente para calcular antigüedad).
     *
     * @throws LegajoApiException
     */
    public function fechaIngreso(string $dni): ?Carbon
    {
        $datos = $this->datosBasicos($dni);

        return $datos['fecha_ingreso'] ?? null;
    }

    /**
     * Antigüedad en meses según fecha de ingreso del legajo.
     * null si no existe la persona o no tiene legajo activo.
     *
     * @throws LegajoApiException
     */
    public function antiguedadEnMeses(string $dni): ?int
    {
        return $this->antiguedad($dni, 'diffInMonths');
    }

    /**
     * Antigüedad en años según fecha de ingreso del legajo.
     * null si no existe la persona o no tiene legajo activo.
     *
     * @throws LegajoApiException
     */
    public function antiguedadEnAnios(string $dni): ?int
    {
        return $this->antiguedad($dni, 'diffInYears');
    }

    /**
     * @throws LegajoApiException
     */
    private function antiguedad(string $dni, string $metodo): ?int
    {
        $fecha = $this->fechaIngreso($dni);

        if ($fecha === null) {
            return null;
        }

        return (int) $fecha->{$metodo}(now());
    }

    private function client(): PendingRequest
    {
        if (! $this->configurado()) {
            throw new LegajoApiException(
                'Falta configurar LEGAJOS_API_URL y/o LEGAJOS_API_TOKEN en el archivo .env.',
            );
        }

        $client = Http::acceptJson()
            ->withToken((string) config('services.legajos.token'))
            // connectTimeout acota el caso "host caído" (no llega a conectar);
            // timeout acota el caso "conecta pero no responde". Entre los dos
            // y la memoria de errores, la ficha no puede colgarse 30 s.
            ->connectTimeout((int) config('services.legajos.connect_timeout', 3))
            ->timeout((int) config('services.legajos.timeout', 10))
            // Reintenta solo ante cortes de conexión o errores 5xx (se detectaron
            // cortes transitorios al probar); los 4xx (404/401) se resuelven abajo
            // sin reintentar.
            ->retry(
                times: 2,
                sleepMilliseconds: 500,
                when: fn ($exception) => ! $exception instanceof RequestException
                    || $exception->response->serverError(),
                throw: false,
            );

        // El servidor de Legajos usa certificado autofirmado.
        if (! config('services.legajos.verify', true)) {
            $client = $client->withoutVerifying();
        }

        return $client;
    }

    /**
     * Indica si hay URL y token configurados para consultar la API.
     */
    private function configurado(): bool
    {
        return filled(config('services.legajos.url')) && filled(config('services.legajos.token'));
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizarPersona(array $persona): array
    {
        return [
            'id' => $persona['id'] ?? null,
            'nombre' => $persona['nombre'] ?? null,
            'apellido' => $persona['apellido'] ?? null,
            'dni' => isset($persona['dni']) ? (string) $persona['dni'] : null,
            'cuil' => isset($persona['cuil']) ? (string) $persona['cuil'] : null,
            'email' => $persona['email'] ?? null,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function normalizarLegajo(mixed $legajo): ?array
    {
        // Sin legajo activo: la API responde 200 con "legajo": [] (el documento
        // contemplaba 404). Si viene como lista, se toma el primer elemento.
        if ($legajo === null || $legajo === []) {
            return null;
        }

        if (is_array($legajo) && array_is_list($legajo)) {
            $legajo = $legajo[0] ?? null;
        }

        if (! is_array($legajo) || $legajo === []) {
            return null;
        }

        $fecha = $legajo['fecha_de_ingreso'] ?? null;
        $baja = $legajo['baja'] ?? null;

        return [
            'id' => $legajo['id'] ?? null,
            'num_legajo' => $legajo['num_legajo'] ?? null,
            'estado' => $legajo['estado'] ?? null,
            'fecha_ingreso' => $fecha !== null ? Carbon::parse($fecha)->toDateString() : null,
            'tipo_contrato' => $legajo['tipo_contrato'] ?? null,
            'categoria' => $legajo['categoria'] ?? null,
            'cargo' => $legajo['cargo'] ?? null,
            'area' => $legajo['area'] ?? null,
            // Baja del legajo: {motivo, fecha_baja} o null si sigue activo.
            'baja' => is_array($baja) ? [
                'motivo' => $baja['motivo'] ?? null,
                'fecha' => isset($baja['fecha_baja']) && $baja['fecha_baja'] !== null
                    ? Carbon::parse($baja['fecha_baja'])->toDateString()
                    : null,
            ] : null,
        ];
    }
}
