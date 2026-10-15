# Contrato — Registrar una licencia aprobada (Fase 2)

**Proyecto:** Sistema de Licencias y Permisos (TCSE)
**Fecha:** 13/10/2026
**Para:** equipo del Sistema de Legajos

Este documento describe **únicamente** el endpoint que necesita el Sistema de Licencias para **enviar una licencia aprobada** al Sistema de Legajos. Es la contraparte de la lectura (Fase 1, ya funcionando: `GET /api/personas` y `GET /api/legajos/por-dni/{dni}`).

---

## Endpoint

```
POST /api/legajos/{legajo_id}/licencias
```

- `legajo_id`: id del legajo del agente (el mismo `legajo.id` que ya devuelve `GET /api/legajos/por-dni/{dni}`).
- Autenticación: `Authorization: Bearer <token>` (igual que los endpoints de lectura).

## Body (JSON)

```json
{
  "id_solicitud": 125,
  "tipo_licencia": "Receso de Enero",
  "fecha_inicio": "2026-10-01",
  "fecha_fin": "2026-10-14",
  "dias_solicitados": 14,
  "motivo": "Licencia anual ordinaria",
  "nro_resolucion": 123,
  "fecha_aprobacion": "2026-09-20"
}
```

## Campos

| Campo | Tipo | Requerido | Descripción |
|---|---|---|---|
| `id_solicitud` | int | **sí** | **Clave de idempotencia.** Id de la solicitud en el Sistema de Licencias. **Único global** (no se repite entre legajos). |
| `tipo_licencia` | string | **sí** | Nombre del tipo (ver `TIPOS_LICENCIA.md`). |
| `fecha_inicio` | date (`Y-m-d`) | **sí** | Inicio de la licencia. |
| `fecha_fin` | date (`Y-m-d`) | **sí** | Fin de la licencia. |
| `dias_solicitados` | int | **sí** | Días **ya calculados** por nosotros (corridos o hábiles según el tipo). **Solicitamos que se respete este número, no se recalcule.** |
| `motivo` | string | no | Motivo declarado. |
| `nro_resolucion` | int | no | Número de resolución que aprobó la licencia. |
| `fecha_aprobacion` | date (`Y-m-d`) | **sí** | Fecha de aprobación (hora argentina). |

## Respuesta exitosa — `201 Created`

```json
{
  "ok": true,
  "id": 6,
  "mensaje": "Licencia registrada en el legajo"
}
```

## Errores

| Código | Cuándo |
|---|---|
| `401 Unauthorized` | Token inválido o faltante. |
| `404 Not Found` | `legajo_id` inexistente. |
| `422 Unprocessable Entity` | Validación fallida. Cuerpo: `{"message": "...", "errors": {...}}`. |

## Reglas acordadas

1. **Solo altas.** En nuestro sistema la aprobación es **definitiva e irrevocable**, y la licencia se envía **solo cuando queda aprobada**. Nunca se modifica ni se anula una ya enviada → **no se necesitan PUT ni DELETE**.
2. **Idempotencia (importante).** Si la API está caída, **nosotros reintentamos** el mismo envío. Por eso el endpoint **no debe duplicar**: si ya recibió ese `id_solicitud`, debe devolver la existente (o `200`/`409` con `ok: true`) sin crear otro registro.
3. **Días.** Enviamos `dias_solicitados` **ya calculado** (corridos o hábiles según el tipo). Pedimos **no recalcularlo** para no tener dos números distintos.
4. **`tipo_licencia`.** Hoy enviamos el **nombre** de nuestro catálogo (29 tipos; ver `TIPOS_LICENCIA.md`). Si ustedes manejan una **lista cerrada**, pásennos la lista y acordamos la equivalencia.

## Ejemplo (cURL)

```bash
curl -X POST "https://10.0.7.6/api/legajos/1009/licencias" \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "id_solicitud": 125,
    "tipo_licencia": "Receso de Enero",
    "fecha_inicio": "2026-10-01",
    "fecha_fin": "2026-10-14",
    "dias_solicitados": 14,
    "motivo": "Licencia anual ordinaria",
    "nro_resolucion": 123,
    "fecha_aprobacion": "2026-09-20"
  }'
```

---

*Documento de referencia. La versión completa del contrato de integración está en `integracion-legajos-api.md`.*
