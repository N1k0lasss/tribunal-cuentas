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

## Usuarios de prueba

| Usuario | Email | Contraseña |
|---|---|---|
| Admin | admin@test.com | password |
| Agente | agente@test.com | password |
| Jefe | jefe@test.com | password |
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
