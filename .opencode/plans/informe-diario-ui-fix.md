# Plan: Ajustes UI/UX Informe Diario de Novedades

## Cambios solicitados

### 1. Quitar los tres botones de la parte superior
- Eliminar `getHeaderActions()` completo en `app/Filament/Admin/Pages/InformeDiarioPage.php` (o retornarlo vacío)

### 2. Reorganizar métricas en 2 filas de 3 cuadrados
- Mostrar: Total Plantilla, Presentes, Ausente sin aviso, Tardanza, Licencia, Falta justificada
- En la vista `resources/views/filament/admin/pages/informe-diario.blade.php`
- Grid 2 filas x 3 columnas: `grid-cols-1 sm:grid-cols-2 lg:grid-cols-3`

### 3. Ajustar colores de botones inferiores
- "Sincronizar Licencias": color celeste (info/custom cyan)
- "Guardar Informe Diario": color verde claro (success/emerald)
- "Exportar PDF (Sec. General)": mantener destacado (warning/primary según preferencia)

## Archivos a modificar
- `app/Filament/Admin/Pages/InformeDiarioPage.php` - quitar getHeaderActions()
- `resources/views/filament/admin/pages/informe-diario.blade.php` - reorganizar métricas y colores de botones
