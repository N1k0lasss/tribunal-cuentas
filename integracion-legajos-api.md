# Propuesta de integración: Sistema de Licencias ↔ Sistema de Legajos

**De:** Equipo Sistema de Licencias (Tribunal de Cuentas)
**Para:** Equipo Sistema de Legajos
**Fecha:** 2026-09-22

---

## 1. Objetivo

Conectar el **Sistema de Licencias** (base `tribunal_cuentas`) con el **Sistema de Legajos** (base `legajos`) mediante una **API**, de modo que:

1. **El Sistema de Licencias pueda consultar los datos del legajo** de cada agente (datos personales, cargo, categoría, área, fecha de ingreso, etc.).
2. **Cuando una licencia se aprueba en el Sistema de Licencias, ese alta quede registrada en el legajo** del agente.

Se elige **API** en lugar de conexión directa a la base de datos porque cada base pertenece a un grupo distinto y así se evita compartir credenciales de MySQL ni depender de que las bases estén en el mismo servidor.

---

## 2. Consideraciones generales

- **Administración del token:** un usuario con rol `administrador` puede actualizar la credencial desde «Configuración → API de Legajos» en el panel. Se guarda cifrada en la base de datos; la pantalla muestra el momento de carga y estima el vencimiento a las 24 horas, sin volver a mostrar el token. `LEGAJOS_API_TOKEN` en `.env` se conserva como respaldo hasta que se cargue una credencial desde el panel. La actualización debe hacerse cuando Legajos entregue el nuevo token; no hay renovación automática. El vencimiento mostrado es estimado desde la carga y puede diferir si Legajos emitió el token antes.

- **Formato:** consulta REST + JSON; alta de licencia en `multipart/form-data`.
- **Autenticación:** token por API key enviado en el header `Authorization: Bearer <token>` (o `X-API-KEY: <token>`, a definir).
- **Identificación del agente:** el punto de unión entre ambos sistemas es el **DNI** de la persona (campo `dni`, único en `personas`). El Sistema de Licencias guarda el `dni` de cada agente en su tabla `agentes`.
- **Zona horaria (acuerdo fijo):** todos los timestamps se envían **SIEMPRE** en `America/Argentina/Buenos_Aires` (GMT-3). Argentina no tiene horario de verano, así que no hay cambios estacionales. Las fechas puras (inicio/fin de licencia) van como `YYYY-MM-DD` sin hora; los momentos (fecha de aprobación) van como `YYYY-MM-DD HH:MM:SS` en hora argentina. **No se envía nada en UTC**: si un equipo guarda en UTC internamente, debe convertir a hora argentina antes de enviar.

### 2.1. Fuente de verdad y duplicación de datos

Hoy el Sistema de Licencias ya guarda localmente en su tabla `agentes` datos como `cargo`, `situacion_revista`, `area_id` y `fecha_ingreso` (tabla propia, id distinta a las tablas del legajo). Al conectar por API, hay que evitar mantener **dos versiones de la misma información que se desincronicen**:

- **`legajos` es la fuente de verdad** para los datos del agente (nombre, apellido, dni, cargo, categoría, área, fecha de ingreso, estado). Cualquier cambio en esos datos se hace en el Sistema de Legajos.
- El Sistema de Licencias puede **mostrar/consultar** esos datos por API (endpoint 3.1), y **usarlos para el cálculo de licencias** (antigüedad, tipos válidos).
- La tabla `agentes` local se mantiene como **referencia de identidad** (dni + vínculo con el legajo), pero los campos que dependen del legajo (cargo, categoría, área, fecha de ingreso) **no se editan manualmente**: se leen desde el legajo cuando se necesitan.
- **Regla acordada:** el alta de licencias por API (endpoint 3.2) **agrega** información al legajo; nunca **sobrescribe** los datos que el otro sistema ya administra.

> Decisión pendiente de confirmar en conjunto: si el Sistema de Licencias debe guardar una **copia local** de los datos del legajo para rapidez (con sync periódico) o consultar **siempre en vivo** por API. La recomendación técnica es: consultar en vivo para los datos de cabecera, y guardar copia solo del vínculo (dni/legajo) que ya existe hoy.

### 2.2. Manejo de fallos y reintentos (cuando la API no responde)

El caso crítico es: **una licencia se aprueba en el Sistema de Licencias, pero el envío al legajo falla** (API caída, timeout, token inválido). Propuesta acordada:

- Si el `POST` de licencia aprobada falla, **la aprobación no se revierte**; el envío permanece en la bandeja local.
- El Sistema de Licencias reintenta automáticamente los errores transitorios con esperas de 5 → 15 → 30 → 60 minutos.
- El administrador puede consultar pendientes, errores y conflictos en «Configuración → Envíos de licencias». Un HTTP `409` indica que el `solicitud_id` ya existe con otro legajo, otros datos o distinto archivo; no se reintenta automáticamente y la discrepancia debe resolverse con Legajos.
- **Idempotencia confirmada por Legajos:** un reenvío idéntico (mismo legajo, datos y archivo) responde `200` sin duplicar; una licencia nueva responde `201`.

---

## 3. Endpoints solicitados

### 3.1. Obtener datos del legajo por DNI

Consulta los datos de la persona y su legajo activo para un DNI dado.

```
GET /api/legajos/por-dni/{dni}
```

**Respuesta exitosa — `200 OK`**

```json
{
  "persona": {
    "id": 1,
    "nombre": "María marta Eugenia",
    "apellido": "Ledesma",
    "dni": 22456700,
    "cuil": "27224567818",
    "email": "mariamarta.ledesma@tribunalsgo.gob.ar"
  },
  "legajo": {
    "id": 1,
    "num_legajo": 1000,
    "estado": 1,
    "fecha_de_ingreso": "2015-03-01",
    "tipo_contrato": 0,
    "categoria": {
      "id": 1,
      "nombre": "CAT 1",
      "descripcion": "Secretario General - Contador/Fiscal"
    },
    "cargo": {
      "id": 14,
      "nombre": "Presidente/a"
    },
    "area": {
      "id": 13,
      "nombre": "Secretaria General"
    }
  }
}
```

**Errores**

- `404 Not Found` → no existe persona con ese DNI, o no tiene legajo activo.
- `401 Unauthorized` → token inválido o faltante.

**Campos mínimos que necesita el Sistema de Licencias**

| Campo | Fuente en `legajos` | Para qué se usa |
|---|---|---|
| `dni` | `personas.dni` | Identificar al agente |
| `nombre`, `apellido` | `personas` | Datos del agente |
| `num_legajo` | `legajos.num_legajo` | Referencia del legajo |
| `categoria.nombre` / `descripcion` | `categorias` | Calcular tipos de licencia válidos y saldos |
| `cargo.nombre` | `cargos` | Mostrar cargo del agente |
| `area.nombre` | `areas` | Mostrar área del agente |
| `fecha_de_ingreso` | `legajos.fecha_de_ingreso` | Calcular antigüedad (requisitos de licencias) |
| `estado` | `legajos.estado` | Saber si el legajo está activo (1) o dado de baja (0) |

---

### 3.2. Registrar una licencia aprobada en el legajo

Registra la licencia que fue aprobada en el Sistema de Licencias. El Sistema de Legajos la persiste internamente (por ejemplo, como documento del tipo "licencia" en la tabla `documentos`, como ya se hace hoy con `tipodoc = 3`).

```
POST /api/legajos/{legajo_id}/licencias
```

**Formato: `multipart/form-data`**

El identificador de ruta es el `id` interno del legajo devuelto por la consulta por DNI (no `num_legajo`). El PDF va en el campo `archivo`.

| Campo | Tipo | Requerido | Descripción |
|---|---|---|---|
| `archivo` | PDF, máximo 10 MB | sí | PDF final firmado de la solicitud |
| `solicitud_id` | int, mínimo 1 | sí | Identificador único de origen en el Sistema de Licencias |
| `tipo_licencia` | string, máximo 100 | sí | Nombre del tipo de solicitud |
| `fecha_inicio` | `Y-m-d` | sí | Fecha de inicio |
| `fecha_fin` | `Y-m-d` | sí | Fecha de fin, igual o posterior al inicio |
| `dias_solicitados` | int, 1–366 | sí | Cantidad de días calculada en Licencias |
| `motivo` | string, máximo 500 | no | Motivo declarado |
| `nro_resolucion` | string, máximo 50 | no | Número/código de resolución |
| `fecha_aprobacion` | `Y-m-d` | sí | Fecha en que se aprobó la licencia |

Cada envío incluye la cabecera `Idempotency-Key: licencia-<solicitud_id>`, con el mismo valor que el campo `solicitud_id`. Pedimos que el endpoint la use (o que, como mínimo, `solicitud_id` sea único) para que un reintento no duplique la licencia.

El envío queda registrado en una bandeja de salida local antes de intentar la llamada, para que un corte de red no revierta la aprobación. Los errores transitorios se reintentan automáticamente; el administrador puede revisar el estado en «Configuración → Envíos de licencias».

**Respuestas que el cliente debe aceptar**

```json
{
  "ok": true,
  "id": 6,
  "mensaje": "Licencia registrada en el legajo"
}
```

El cliente considera `201 Created` y `200 OK` como enviados. Un `409 Conflict` queda en estado de conflicto para resolver diferencias; no equivale a un envío exitoso.

**Errores**

- `404 Not Found` → `legajo_id` inexistente.
- `422 Unprocessable Entity` → validación fallida (falta un campo obligatorio, fechas inválidas, etc.). Se devuelve `{"message": "...", "errors": {...}}`.
- `401 Unauthorized` → token inválido o faltante.
- `409 Conflict` → el `solicitud_id` ya existe, pero con otro legajo, otros datos o distinto archivo.

---

## 4. Acuerdos y confirmaciones pendientes con Legajos

1. La ruta acordada para el alta es `POST /api/legajos/{legajo_id}/licencias`; `{legajo_id}` es el ID interno.
2. ¿Qué mecanismo de autenticación prefieren (Bearer token, API key en header, token en el path tipo `?api_token=`)?
3. ¿La URL `https://10.0.7.6` es un ambiente de prueba o producción?

---

## 5. Lo que haremos de nuestra parte

- Guardar en nuestro `.env`: `URL` base de la API y `token` de autenticación.
- Enviar el PDF firmado final y los datos como `multipart/form-data`, usando el `id` interno del legajo y el campo `solicitud_id`.
- **Usar el legajo como fuente de verdad en vivo**: consultar `GET .../por-dni/{dni}` para obtener los datos del agente (no editar manualmente cargo/categoría/área/fecha de ingreso a partir de datos stale locales).
- Programar el envío en una bandeja de salida persistente al completarse la última firma; no bloquear ni revertir la aprobación si Legajos no responde.
- **Reintentos y seguimiento**: reintentar errores transitorios cada 5, 15, 30 y 60 minutos; mostrar los estados y errores al administrador. Los HTTP 409 se revisan con Legajos para corregir la discrepancia.
- Manejar los errores de la API (caída del servicio, timeout, token inválido) sin romper el flujo del Sistema de Licencias.