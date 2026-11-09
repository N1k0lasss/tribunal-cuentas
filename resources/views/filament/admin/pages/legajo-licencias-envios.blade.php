<x-filament-panels::page>
    <p class="text-sm text-gray-600 dark:text-gray-300">
        Las aprobaciones no se revierten si Legajos no está disponible. Los envíos pendientes se reintentan automáticamente cada cinco minutos.
        Un HTTP 409 significa que ya existe la solicitud con otro legajo, datos o archivo. No se reintenta automáticamente:
        hay que revisar la discrepancia con el equipo de Legajos antes de resolverla.
    </p>

    {{ $this->table }}
</x-filament-panels::page>
