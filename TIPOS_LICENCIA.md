# Tipos de licencia / solicitud — Sistema de Licencias (TCSE)

**Fecha:** 13/10/2026

Este listado es el **catálogo interno** del Sistema de Licencias. Se envía al equipo de Legajos para acordar la **equivalencia** entre nuestros tipos y los de ellos (campo `tipo_licencia` del `POST /api/legajos/{id}/licencias`).

## Cómo usar este archivo

En la columna **"Equivalencia (Legajos)"**, por favor indíquennos:
- el **código** de su tipo equivalente, o
- **"—"** si ese tipo **no se registra** en el legajo.

### Aclaraciones importantes
- No todos los ítems son "licencias" propiamente dichas: hay **franquicias** y **tardanzas** que se miden en **horas** (ver columna *Unidad*).
- La columna **Días** indica cómo contamos el tiempo: **corridos** o **hábiles**.
- Los **artículos legales** citados son los de nuestro régimen (importante para el mapeo).

## Catálogo

| # | Nombre | Categoría | Artículo | Unidad | Días | Equivalencia (Legajos) |
|---|---|---|---|---|---|---|
| 1 | Receso de Enero | Receso | ART 3 | días | corridos | |
| 2 | Receso de Julio | Receso | ART 3 | días | hábiles | |
| 3 | Enfermedad Común (Etapa 1 - 100%) | Salud y Maternidad | ART 6 | días | corridos | |
| 4 | Enfermedad Común (Etapa 2 - 75%) | Salud y Maternidad | ART 6 | días | corridos | |
| 5 | Enfermedad Común (Etapa 3 - Sin Goce) | Salud y Maternidad | ART 6 | días | corridos | |
| 6 | Enfermedad Grave o Largo Tratamiento | Salud y Maternidad | ART 10 | días | corridos | |
| 7 | Enfermedad de Familiar | Salud y Maternidad | ART 11 | días | corridos | |
| 8 | Familiar Discapacitado a Cargo | Salud y Maternidad | ART 11 BIS | días | corridos | |
| 9 | Maternidad | Salud y Maternidad | ART 12 | días | corridos | |
| 10 | Enfermedad Profesional / Accidente Trabajo | Salud y Maternidad | ART 13 | días | corridos | |
| 11 | Cargos Políticos | Extraordinaria | ART 14 | días | corridos | |
| 12 | Cargo de Mayor Jerarquía | Extraordinaria | ART 15 | días | corridos | |
| 13 | Rendir Examen | Extraordinaria | ART 16 | días | hábiles | |
| 14 | Estudio y Capacitación | Extraordinaria | ART 18 | días | corridos | |
| 15 | Actividades Deportivas | Extraordinaria | ART 19 | días | corridos | |
| 16 | Matrimonio del Agente | Extraordinaria | ART 20 | días | hábiles | |
| 17 | Matrimonio de Hijos, Hermanos, Padres | Extraordinaria | ART 20 | días | hábiles | |
| 18 | Nacimiento de Hijo | Extraordinaria | ART 20 | días | hábiles | |
| 19 | Fallecimiento (Cónyuge, hijos, padres, suegros, hermanos) | Extraordinaria | ART 20 | días | hábiles | |
| 20 | Fallecimiento (Cuñados, yernos, nueras) | Extraordinaria | ART 20 | días | hábiles | |
| 21 | Fallecimiento (Otros parentescos hasta 4to grado) | Extraordinaria | ART 20 | días | hábiles | |
| 22 | Razones Particulares | Extraordinaria | ART 21 | días | corridos | |
| 23 | Donación de Sangre / Extracción Dentaria | Justificación | ART 22 | días | corridos | |
| 24 | Fenómenos Meteorológicos / Fuerza Mayor | Justificación | ART 22 | días | corridos | |
| 25 | Franquicia por Estudio | Franquicia | ART 23 | horas | — | |
| 26 | Franquicia por Maternidad (Lactancia) | Franquicia | ART 23 | horas | — | |
| 27 | Franquicia por Razones Particulares | Franquicia | ART 23 | horas | — | |
| 28 | Tardanza (hasta 30 min) | Falta/Tardanza | ART 24 | horas | — | |
| 29 | Inasistencia Injustificada | Falta/Tardanza | ART 25 | días | corridos | |

**Total: 29 tipos** (25 en días + 4 en horas).

### Preguntas asociadas
1. ¿El campo `tipo_licencia` de ustedes es una **lista cerrada**? Si lo es, ¿nos pasan la lista?
2. ¿**Registran todos** estos tipos, o **solo las licencias** (dejando afuera franquicias/tardanzas)?
3. Si hay tipos suyos que **no figuran** acá, indíquennos cuáles.

---

*Fuente: `database/seeders/TipoSolicitudSeeder.php` — tabla `tipo_solicituds`.*
