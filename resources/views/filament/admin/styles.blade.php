<style>
    /* Forzar fondo Navy y aumentar el alto en el header principal de Filament */
    .fi-topbar {
        background-color: #1A365D !important;
        border-bottom: none !important;
    }
    .fi-topbar > nav {
        min-height: 7rem !important; /* Aumenta el tamaño del header */
        padding-top: 1rem !important;
        padding-bottom: 1rem !important;
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
</style>
