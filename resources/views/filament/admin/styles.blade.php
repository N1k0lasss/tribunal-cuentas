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
        padding-top: 0.5rem !important;
        padding-bottom: 0.5rem !important;
        min-height: 6rem !important;
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

    /* El selector del tema (modo claro/oscuro) no debe ser blanco,
       sino usar un color legible según el fondo del menú desplegable */
    .fi-theme-switcher-btn,
    .fi-theme-switcher-btn svg,
    .fi-theme-switcher-btn:hover svg {
        color: #374151 !important;
    }
    html.dark .fi-theme-switcher-btn,
    html.dark .fi-theme-switcher-btn svg,
    html.dark .fi-theme-switcher-btn:hover svg {
        color: #e5e7eb !important;
    }
    /* Menú lateral (Sidebar) - Hover en tono azul */
    .fi-sidebar-item-button:hover {
        background-color: rgba(26, 54, 93, 0.08) !important; /* Tono Navy claro */
    }
    .dark .fi-sidebar-item-button:hover {
        background-color: rgba(26, 54, 93, 0.3) !important; /* Tono Navy para modo oscuro */
    }

    /* Agrandar el avatar/círculo de perfil del usuario en el header */
    .fi-topbar .fi-avatar {
        width: 3.5rem !important;
        height: 3.5rem !important;
    }

    /* Asegurar que la imagen del logo no se desborde y mantenga la proporción */
    .fi-logo img {
        height: 6rem !important;
        max-height: 6rem !important;
        width: auto !important;
        object-fit: contain !important;
    }

    /* Footer Admin Panel */
    .tcse-admin-footer {
        background-color: #1A365D;
        color: #ffffff;
        padding: 1.25rem 2rem;
        margin-top: 2.5rem;
        border-top-left-radius: 0.75rem;
        border-top-right-radius: 0.75rem;
        box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.05);
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        width: 100%;
        font-family: 'Inter', sans-serif;
    }

    .dark .tcse-admin-footer {
        background-color: #0d1b2e;
        border-top-color: rgba(255, 255, 255, 0.05);
        box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.2);
    }

    .tcse-admin-footer-content {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        max-width: 1400px;
        margin: 0 auto;
        gap: 1.5rem;
    }

    .tcse-admin-footer-brand {
        display: flex;
        align-items: center;
    }

    .tcse-admin-footer-logo-banner {
        height: 3.25rem;
        max-height: 3.25rem;
        width: auto;
        object-fit: contain;
    }

    .tcse-admin-footer-info {
        text-align: right;
    }

    .tcse-admin-footer-copy {
        font-size: 0.72rem;
        color: rgba(255, 255, 255, 0.8) !important;
        margin: 0 !important;
        line-height: 1.2 !important;
    }

    .tcse-admin-footer-rights {
        font-size: 0.62rem;
        color: rgba(255, 255, 255, 0.5) !important;
        margin: 0.15rem 0 0 !important;
        letter-spacing: 0.02em;
        line-height: 1.2 !important;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .tcse-admin-footer-content {
            flex-direction: column;
            text-align: center;
            gap: 1rem;
        }

        .tcse-admin-footer-brand {
            flex-direction: column;
            gap: 0.5rem;
        }

        .tcse-admin-footer-info {
            text-align: center;
        }
    }
</style>
