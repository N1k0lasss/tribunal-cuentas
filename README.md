# Sistema de Gestión de Licencias y Permisos

## Configuración

```bash
# Instalar dependencias
composer install

# Copiar .env.example a .env y generar clave de aplicación
cp .env.example .env
php artisan key:generate

# (Modificar .env y configurar base de datos con credenciales válidas)

# Recrear base de datos (eliminar tablas y volver a crear)
php artisan migrate:fresh

# Ejecutar seeders (datos de prueba)
php artisan db:seed

# Ejecutar servidor local
php artisan serve
```

## Conexión con la API de legajos

Los agentes se sincronizan desde la API externa de legajos
(`https://10.0.7.6/api`) mediante Bearer token.

### 1. Variables de entorno

En `.env` (copiadas desde `.env.example`), completar el token:

```env
LEGAJOS_API_URL=https://10.0.7.6/api
LEGAJOS_API_TOKEN=        # <-- completar con el token de acceso
LEGAJOS_API_VERIFY_SSL=false
LEGAJOS_API_TIMEOUT=15
LEGAJOS_API_RETRIES=2
```

> `.env.example` trae el token **vacío** a propósito: el token es un secreto y
> **no se sube al repositorio**. Cada quien debe completarlo en su `.env`.

### 2. Requisitos

- **Misma red**: `10.0.7.6` es una IP privada, hay que poder alcanzarla
  (red local del tribunal / VPN). Sin ruta de red, el comando falla con un
  mensaje claro sin romper la aplicación.
- **TLS**: `LEGAJOS_API_VERIFY_SSL=false` porque el certificado del servidor
  es autofirmado. No hace falta instalar ningún certificado.
- La conexión MySQL `legajos` (`LEGAJOS_DB_*`) **ya no se utiliza**: la
  sincronización es 100% HTTP. Esas variables pueden quedar como están.

### 3. Verificar la conexión

```bash
# Diagnóstico general (trae la lista de personas)
php artisan app:legajos-api-check

# Diagnóstico con un DNI puntual
php artisan app:legajos-api-check --dni=20987654
```

Salida esperada:

```
API: https://10.0.7.6/api
TLS verify_ssl: desactivado
Token: 6|aUuJ…******

GET /personas -> OK: 30 personas (400 ms)
GET /legajos/por-dni/20987654 -> OK: Roberto Carlos, 1 legajo(s) (93 ms)
```

### 4. Sincronizar agentes

```bash
php artisan app:sync-agentes-desde-legajos
```

Salida esperada:

```
Agentes sincronizados: 24
Sin legajo (ignorados): 6
No encontrados en la API: 0
```

Comportamiento:

- Crea o actualiza los agentes por DNI (`Agente::updateOrCreate`); **nunca
  borra** registros locales (los agentes manuales y los usuarios quedan intactos).
- Las personas sin legajo se ignoran.
- Un DNI inexistente en la API (404) se cuenta como "no encontrado" y el
  proceso continúa.
- Ante un error de red o de la API el comando termina con código de error y
  detalla cada DNI fallido.

### 5. Tests

```bash
vendor/bin/pest tests/Feature/LegajosApiClientTest.php tests/Feature/SyncAgentesDesdeLegajosTest.php
```

Los 14 tests corren **sin conexión a la red**: usan `Http::fake()` y el
`phpunit.xml` define `LEGAJOS_API_URL` / `LEGAJOS_API_TOKEN` de prueba.

## Usuarios de prueba

| Usuario    | Email               | Contraseña |
|------------|---------------------|------------|
| Admin      | admin@test.com      | password   |
| Agente     | agente@test.com     | password   |
| Jefe       | jefe@test.com       | password   |
| Secretario | secretario@test.com | password |
| Presidente | presidente@test.com | password |

## Integración con la API de Legajos

El sistema consulta la API del **Sistema de Legajos** como fuente de verdad de los datos del agente (ver `integracion-legajos-api.md` y `BITACORA_BACKEND.md`).

### 1. Configurar credenciales en `.env`

```dotenv
LEGAJOS_API_URL=https://<host-de-legajos>   # acepta también la base con /api
LEGAJOS_API_TOKEN=<token-de-la-api>
LEGAJOS_API_VERIFY_SSL=false   # el certificado de la API es autofirmado
LEGAJOS_API_TIMEOUT=10
LEGAJOS_API_CONNECT_TIMEOUT=3
```

> ⚠️ El `.env` **no se versiona** (está en `.gitignore`): la URL y el token se piden al equipo.

### 2. Carga inicial de agentes

```bash
php artisan legajos:sync
```

Trae los agentes desde la API y los guarda localmente (idempotente: se puede correr varias veces).

> 🌐 **Requiere estar conectado a la red interna del Tribunal.** La API vive en una IP interna y **no** es accesible desde internet; si no estás en la red, `legajos:sync` falla (el sistema sigue funcionando con los datos locales que ya tenga).

Para sincronizar un solo DNI:

```bash
php artisan legajos:sync --dni=22456700
```

Si la API no responde, el sistema sigue funcionando con los datos locales de la tabla `agentes`.

### 3. Envío de licencias aprobadas

Al completarse la última firma, el sistema guarda el envío con el PDF firmado final y **encola** el job `EnviarLicenciaALegajos` con `->afterCommit()`: la aprobación responde sin esperar a Legajos y, si la transacción revierte, no se encola nada. Un error de red no revierte la aprobación.

El job corre en la cola `legajos` y es el dueño de los reintentos (5 intentos con esperas de 5, 15, 30 y 60 minutos por defecto). Se configura con:

```dotenv
LEGAJOS_ENVIO_QUEUE=legajos
LEGAJOS_ENVIO_TRIES=5
LEGAJOS_ENVIO_TIMEOUT=30
LEGAJOS_ENVIO_UNIQUE_FOR=7200
```

> ⚠️ **Es obligatorio tener un worker de cola activo.** Sin worker, los envíos quedan encolados en la tabla `jobs` y no se ejecutan. En desarrollo:

```bash
php artisan queue:work --queue=legajos,default
```

`composer dev` ya levanta el worker (`queue:listen --queue=legajos,default`), el planificador y Vite.

En producción el worker debe estar supervisado para que se reinicie solo:

- **Linux**: Supervisor o una unidad systemd con `Restart=always` ejecutando `php artisan queue:work --queue=legajos,default --sleep=3`.
- **Windows**: NSSM o una Tarea Programada al inicio del sistema con el mismo comando.

El planificador actúa como **red de seguridad del outbox** (no como el mecanismo principal): cada cinco minutos ejecuta `php artisan app:enviar-licencias-legajos`, que reencola los envíos pendientes vencidos y recupera los que quedaron en `procesando` por una interrupción. Debe estar activo con `php artisan schedule:run` cada minuto (cron del servidor) o `php artisan schedule:work` en desarrollo. El comando también se puede ejecutar manualmente.

Los estados de cada envío se consultan en «Configuración → Envíos de licencias». Desde ahí también se puede **reintentar manualmente** un envío en `error` o en `conflicto`: el reintento se encola al instante y reinicia el ciclo de reintentos. Para los `409`, primero hay que resolver la discrepancia con Legajos y luego reintentar. Los HTTP 409 indican que ya existe el mismo `solicitud_id` con un legajo, datos o archivo distinto: quedan en revisión para resolver la discrepancia con Legajos y no se marcan como enviados. Si se confirma en Legajos que la licencia **sí** quedó registrada (por ejemplo, porque se perdió la respuesta `201`), el panel permite **Marcar enviado** sin volver a llamar a la API.

#### Tipos sin equivalente en Legajos

El envío se decide por el campo `legajos_tipo_licencia` de cada régimen legal («Configuración → Régimen Legal»), **no** por la unidad de medida. Si está vacío, la aprobación se registra igual, pero el envío queda en estado **`no_aplica`** y no se llama a la API: así no se generan errores `422` que parezcan fallas del sistema. Los cuatro tipos medidos en horas (franquicias y tardanzas) quedan sin mapeo hasta acordar su equivalencia con Legajos. Cuando se acuerde, se carga el equivalente en el régimen legal y los envíos nuevos se envían normalmente; los `no_aplica` anteriores se pueden reencolar desde el panel. Al reencolarlos se recalcula el tipo equivalente: si el campo sigue vacío, no se envía y el panel avisa.

#### Idempotencia

Cada POST incluye la cabecera `Idempotency-Key: licencia-{solicitud_id}`. Si Legajos la implementa, un reintento devuelve el registro existente en vez de duplicarlo. Si no la implementa, el contrato igual exige que `solicitud_id` sea único. El cliente HTTP no reintenta el POST por su cuenta: los reintentos los maneja el job con backoff, para evitar duplicados por doble capa.

### 4. Migraciones de la integración

Las migraciones ya están en el repo; se aplican con `php artisan migrate` (o `migrate:fresh` en un entorno limpio). Las específicas de la integración son:

- `2026_06_02_000001_rename_tipo_contrato_to_situacion_revista_in_agentes_table`
- `2026_10_07_100000_make_fecha_ingreso_nullable_in_agentes_table`
- `2026_10_13_100000_add_estado_legajo_to_agentes_table`
