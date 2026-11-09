# Integración API de Legajos — qué necesitamos para continuar

**Fecha original:** 13/10/2026
**Última actualización:** 06/11/2026
**Estado de la integración:** Fase 1 (solo lectura) y Fase 2 (envío de licencias) funcionando. El endpoint `POST /api/legajos/{id}/licencias` ya respondió `201` en una prueba real.

Este documento es un mensaje para el **equipo de Legajos**. Se divide en dos bloques: lo **necesario para cerrar la Fase 2** y las **mejoras a futuro** (no urgentes).

---

## 1. Necesario para cerrar la Fase 2

1. **Confirmar idempotencia del envío.**
   Necesitamos que reintentar el mismo envío **no duplique** la licencia.
   - ¿El endpoint hace único el campo `solicitud_id`?
   - ¿Qué devuelve ante un reintento idéntico: `200`, `201` o `409`?
   - ¿Aceptan la cabecera `Idempotency-Key: licencia-<solicitud_id>`? Nosotros ya la enviamos.
2. **Equivalencia de los tipos en horas.**
   Nuestro catálogo tiene cuatro tipos medidos en **horas** (franquicias y tardanzas) que no tienen `fecha_fin` ni `dias_solicitados`.
   - ¿Cómo los registra Legajos?
   - ¿Hay un campo de horas/unidad, o un tipo equivalente?
   - Mientras tanto, esos tipos **no se envían** y quedan registrados como `no_aplica` para no inventar datos.
3. **Formato de `nro_resolucion`.**
   En nuestro sistema el número de resolución es texto (puede ser `RESOL-2026-100`). El contrato original pedía `int`.
   - ¿Aceptan string de hasta 50 caracteres?

---

## 2. Mejoras a futuro (no urgentes)

Con la API actual ya podemos trabajar; esto solo la haría más cómoda/rápida:

4. **Filtro por DNI (`?dni=`)** y **paginación** en `/api/personas` (hoy trae todo de una).
5. **`updated_since`** o filtro por fecha de modificación: para sincronizar **solo lo que cambió**.
6. **Incluir el legajo en el listado** (`num_legajo`, `estado`, `cargo`, `área`, `fecha_de_ingreso`, `baja`): hoy hay que consultar **DNI por DNI**, lo que hace el sync lento.
7. **Aclaraciones de documentación:**
   - `is_active` en `/api/personas`: ¿qué representa? Hoy viene en `false` en varios casos y **no coincide** con el estado del legajo, por eso lo ignoramos.
   - `baja: {motivo, fecha_baja}`: **existe** pero no está en el contrato. ¿Pueden formalizarlo? ¿`motivo` tiene valores fijos?
   - `estado` (1/0): ¿`0` = baja definitiva? ¿Hay otros estados (licencia, suspendido, etc.)?
8. **Infraestructura:** ¿la IP `10.0.7.6` es fija? ¿Hay ambiente de **prueba/staging**? ¿Hay **límite de requests** (rate limit)? El certificado es **autofirmado** — ¿es permanente o van a usar uno válido?

---

## Notas internas (no enviar)

- **Bloqueante real:** solo el punto 1 (idempotencia) y el punto 2 (tipos en horas).
- **Idempotencia:** nuestra política es no reintentar el POST a nivel HTTP; lo hace el job con backoff. La clave `Idempotency-Key` ya se envía.
- **Tipos en horas:** se excluyen a propósito hasta acordar el mapeo; no son un error del sistema.
- El resto son optimizaciones de **rendimiento, prolijidad y a futuro**; el trabajo se puede cerrar sin ellas.
- Ver también: `integracion-legajos-api.md` (contrato completo) y `CONTRATO_POST_LICENCIAS.md` (resumen del POST).
