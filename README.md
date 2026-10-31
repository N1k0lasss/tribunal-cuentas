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
LEGAJOS_API_URL=https://<host-de-legajos>
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

### 3. Migraciones de la integración

Las migraciones ya están en el repo; se aplican con `php artisan migrate` (o `migrate:fresh` en un entorno limpio). Las específicas de la integración son:

- `2026_06_02_000001_rename_tipo_contrato_to_situacion_revista_in_agentes_table`
- `2026_10_07_100000_make_fecha_ingreso_nullable_in_agentes_table`
- `2026_10_13_100000_add_estado_legajo_to_agentes_table`
