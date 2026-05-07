<style>
    /* Variables globales de color primario (Navy Blue) para todo Filament */
    :root, .dark {
        --c-primary-300: 43, 76, 126;
        --c-primary-400: 26, 54, 93;
        --c-primary-500: 26, 54, 93;
        --c-primary-600: 19, 39, 67;
        --c-primary-700: 13, 27, 46;
        --primary-500: 26, 54, 93;
        --primary-600: 19, 39, 67;
    }

    /* Botones del Calendario (FullCalendar) */
    .fc .fc-button-primary {
        background-color: #1A365D !important;
        border-color: #1A365D !important;
        color: white !important;
    }
    .fc .fc-button-primary:hover {
        background-color: #132743 !important;
        border-color: #132743 !important;
    }
    .fc .fc-button-primary:not(:disabled):active, 
    .fc .fc-button-primary:not(:disabled).fc-button-active {
        background-color: #0d1b2e !important;
        border-color: #0d1b2e !important;
    }

    /* Botones de acción principales de Filament (ej: "Nuevo Feriado", "Guardar", etc.) */
    .fi-btn.fi-color-primary,
    button[wire\:click*="create"],
    a[href*="/create"] {
        background-color: #1A365D !important;
        color: white !important;
    }
    .fi-btn.fi-color-primary:hover,
    button[wire\:click*="create"]:hover,
    a[href*="/create"]:hover {
        background-color: #132743 !important;
    }
    .fi-btn.fi-color-primary *,
    button[wire\:click*="create"] *,
    a[href*="/create"] * {
        color: white !important;
    }

    /* Forzar fondo Navy y aumentar el alto en el header principal de Filament */
    .fi-topbar {
        background-color: #1A365D !important;
        border-bottom: none !important;
    }
    .fi-topbar > nav {
        min-height: 10rem !important; /* Aumenta el tamaño del header */
        padding-top: 1.5rem !important;
        padding-bottom: 1.5rem !important;
    }
    
    /* Asegurar que los íconos y texto del header sean blancos o legibles */
    .fi-topbar button, 
    .fi-topbar a, 
    .fi-topbar svg, 
    .fi-topbar .fi-topbar-item-label {
        color: rgba(255, 255, 255, 0.9) !important;
    }

    .fi-topbar button:hover, 
    .fi-topbar a:hover {
        color: white !important;
    }
    /* Menú lateral (Sidebar) - Hover en tono azul */
    .fi-sidebar-item-button:hover {
        background-color: rgba(26, 54, 93, 0.08) !important; /* Tono Navy claro */
    }
    .dark .fi-sidebar-item-button:hover {
        background-color: rgba(26, 54, 93, 0.3) !important; /* Tono Navy para modo oscuro */
    }
</style>
