<div class="tcse-login-panel">
    @php
        $logoPath = public_path('images/tcse-logo.png');
        $hasLogo = file_exists($logoPath);
    @endphp

    <div class="tcse-login-panel__badge">Tribunal de Cuentas</div>

    <div class="tcse-login-panel__brand">
        @if ($hasLogo)
            <img src="{{ asset('images/tcse-logo.png') }}" alt="Tribunal de Cuentas de Santiago del Estero" class="tcse-login-panel__logo">
        @else
            <div class="tcse-login-panel__logo-fallback">TC</div>
        @endif
        <div>
            <h2 class="tcse-login-panel__title">Sistema de Licencias y Permisos</h2>
            <p class="tcse-login-panel__subtitle">Gestión digital integral para agentes, jefaturas y autoridades.</p>
        </div>
    </div>

    <ul class="tcse-login-panel__list">
        <li>Solicitud digital con trazabilidad legal completa.</li>
        <li>Circuito secuencial de aprobación y resolución final.</li>
        <li>Historial unificado para legajo electrónico del agente.</li>
    </ul>
</div>
