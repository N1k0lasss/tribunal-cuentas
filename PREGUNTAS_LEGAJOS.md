# Integración API de Legajos — qué necesitamos para continuar

**Fecha:** 13/10/2026
**Estado de la integración:** Fase 1 (solo lectura) funcionando — listado de personas + consulta por DNI.

Este documento es un mensaje para el **equipo de Legajos**. Se divide en dos bloques: lo **necesario para continuar** (Fase 2) y las **mejoras a futuro** (no urgentes).

> **En una frase:** lo único que nos falta para avanzar es el endpoint de envío de licencias; el resto son mejoras que pueden esperar.

---

## 1. Necesario para continuar (Fase 2)

1. **Endpoint para enviar licencias.**
   El documento menciona `POST /api/legajos/{id}/licencias` (§3.2), pero **hoy ese endpoint no existe**. Es lo único que nos falta para poder **enviar** las licencias desde nuestro sistema al de ustedes.
   - ¿Está previsto crearlo? ¿Para cuándo?
   - ¿Qué formato esperaría (campos, tipos de licencia, fechas)?
   - ¿Es **idempotente**? (necesitamos saber si reintentar el mismo envío puede duplicar una licencia).

---

## 2. Mejoras a futuro (no urgentes)

Con la API actual ya podemos trabajar; esto solo la haría más cómoda/rápida:

2. **Filtro por DNI (`?dni=`)** y **paginación** en `/api/personas` (hoy trae todo de una).
3. **`updated_since`** o filtro por fecha de modificación: para sincronizar **solo lo que cambió**.
4. **Incluir el legajo en el listado** (`num_legajo`, `estado`, `cargo`, `área`, `fecha_de_ingreso`, `baja`): hoy hay que consultar **DNI por DNI**, lo que hace el sync lento.
5. **Aclaraciones de documentación:**
   - `is_active` en `/api/personas`: ¿qué representa? Hoy viene en `false` en varios casos y **no coincide** con el estado del legajo, por eso lo ignoramos.
   - `baja: {motivo, fecha_baja}`: **existe** pero no está en el contrato. ¿Pueden formalizarlo? ¿`motivo` tiene valores fijos?
   - `estado` (1/0): ¿`0` = baja definitiva? ¿Hay otros estados (licencia, suspendido, etc.)?
6. **Infraestructura:** ¿la IP `10.0.7.6` es fija? ¿Hay ambiente de **prueba/staging**? ¿Hay **límite de requests** (rate limit)? El certificado es **autofirmado** — ¿es permanente o van a usar uno válido?

---

## Notas internas (no enviar)

- **Bloqueante real:** solo el punto 1 (endpoint de envío de licencias).
- **El punto sobre idempotencia** conviene confirmarlo con ellos, aunque la política ante fallos la definimos nosotros (reintento / "pendiente de envío").
- El resto son optimizaciones de **rendimiento, prolijidad y a futuro**; el trabajo se puede cerrar sin ellas.
- Ver también: `integracion-legajos-api.md` (contrato) y `BITACORA_BACKEND.md` (bitácora del proyecto).
