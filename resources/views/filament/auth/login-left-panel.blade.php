{{-- Left Split Panel — TCSE Login --}}
<div class="tcse-split-left" id="tcse-login-left-panel">
    {{-- Background image --}}
    <div class="tcse-split-left__bg"></div>

    {{-- Overlay gradient --}}
    <div class="tcse-split-left__overlay"></div>

    {{-- Decorative blur orb --}}
    <div class="tcse-split-left__orb tcse-split-left__orb--1"></div>
    <div class="tcse-split-left__orb tcse-split-left__orb--2"></div>

    {{-- Content --}}
    <div class="tcse-split-left__content">

        {{-- Top: Institution badge --}}
        <div class="tcse-split-left__top">
            <span class="tcse-split-left__badge">
                <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg" style="display:inline-block;vertical-align:middle;margin-right:5px;">
                    <circle cx="5" cy="5" r="4" stroke="currentColor" stroke-width="1.2"/>
                    <circle cx="5" cy="5" r="1.5" fill="currentColor"/>
                </svg>
                Provincia de Santiago del Estero
            </span>
        </div>

        {{-- Center: Logo + headline --}}
        <div class="tcse-split-left__center">

            {{-- Logo circular institucional --}}
            <div class="tcse-split-left__logo-circle">
                <img
                    src="https://lh5.googleusercontent.com/proxy/ix6C3trZYbM3XE6OgO2BqOUHQJt-tA849Bvz2vVwFn4l2LwIhWRBmpYx9ufIocivTUBtFaEyDZnktm9__INzzV-3VhjlM-BnaPCoUEtWjvd-GZbRHuJnC-MoXQYxEEpBGSsP6dgzLJWXG6o"
                    alt="Tribunal de Cuentas"
                    class="tcse-split-left__logo-img"
                    onerror="this.style.display='none';this.nextElementSibling.style.display='flex';"
                >
                <div class="tcse-split-left__logo-fallback" style="display:none;">TC</div>
            </div>

            <h1 class="tcse-split-left__title">
                Sistema de Gestión<br>de Licencias
            </h1>
            <p class="tcse-split-left__subtitle">
                Gestión Digital Integral
            </p>

            <div class="tcse-split-left__divider"></div>

            <ul class="tcse-split-left__features">
                <li>
                    <div class="tcse-feature-icon">
                        <svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10 1.5L2 5.5V10C2 14.125 5.5 17.975 10 19C14.5 17.975 18 14.125 18 10V5.5L10 1.5Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                            <path d="M7 10L9 12L13 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <span>Solicitud digital con trazabilidad legal</span>
                </li>
                <li>
                    <div class="tcse-feature-icon">
                        <svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3 5H17M3 10H17M3 15H11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <span>Circuito secuencial de aprobaciones</span>
                </li>
                <li>
                    <div class="tcse-feature-icon">
                        <svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="10" cy="7" r="3.5" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M3 18C3 15 6.134 13 10 13C13.866 13 17 15 17 18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <span>Legajo electrónico del agente</span>
                </li>
            </ul>
        </div>

        {{-- Bottom --}}
        <div class="tcse-split-left__bottom">
            <div class="tcse-split-left__bottom-line"></div>
            <p>Tribunal de Cuentas &mdash; {{ date('Y') }}</p>
        </div>
    </div>
</div>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700;800&display=swap');

/* ── Animaciones de entrada ──────────────────────── */
@keyframes tcse-fade-up {
    from { opacity: 0; transform: translateY(18px); }
    to   { opacity: 1; transform: translateY(0); }
}
@keyframes tcse-fade-in {
    from { opacity: 0; }
    to   { opacity: 1; }
}
@keyframes tcse-orb-float {
    0%, 100% { transform: translate(0, 0) scale(1); }
    50%       { transform: translate(15px, -20px) scale(1.08); }
}
@keyframes tcse-logo-pop {
    0%   { opacity: 0; transform: scale(0.8); }
    60%  { transform: scale(1.04); }
    100% { opacity: 1; transform: scale(1); }
}

/* ── Split-screen wrapper ──────────────────────────── */
.fi-simple-main-ctn {
    position: relative;
    display: flex !important;
    align-items: stretch;
    min-height: 100dvh;
    width: 100%;
    max-width: 100% !important;
    padding: 0 !important;
    background: #f0f4f8 !important;
}

/* Panel derecho (formulario) */
.fi-simple-main {
    width: 50% !important;
    min-width: 380px;
    max-width: none !important;
    margin: 0 0 0 auto !important;
    padding: 2.5rem 3rem !important;
    display: flex !important;
    flex-direction: column !important;
    justify-content: center !important;
    background: #ffffff !important;
    min-height: 100dvh;
    box-shadow: -20px 0 60px -10px rgba(26, 54, 93, 0.18);
    position: relative;
    z-index: 10;
    animation: tcse-fade-in 0.5s ease 0.15s both;
}

/* ── Panel izquierdo ──────────────────────────────── */
.tcse-split-left {
    position: fixed;
    top: 0;
    left: 0;
    width: 50%;
    height: 100%;
    z-index: 5;
    overflow: hidden;
}

.tcse-split-left__bg {
    position: absolute;
    inset: 0;
    background-image: url('/images/login-bg.jpg');
    background-size: cover;
    background-position: center top;
    transform: scale(1.05);
    transition: transform 14s ease;
}

.tcse-split-left:hover .tcse-split-left__bg {
    transform: scale(1.0);
}

/* Overlay: azul marino institucional #1A365D */
.tcse-split-left__overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        155deg,
        rgba(10, 25, 55, 0.91) 0%,
        rgba(26, 54, 93, 0.85) 40%,
        rgba(15, 35, 70, 0.92) 100%
    );
}

/* Orbs decorativos */
.tcse-split-left__orb {
    position: absolute;
    border-radius: 50%;
    pointer-events: none;
    animation: tcse-orb-float 9s ease-in-out infinite;
}
.tcse-split-left__orb--1 {
    width: 320px;
    height: 320px;
    top: -80px;
    right: -80px;
    background: radial-gradient(circle, rgba(66, 153, 225, 0.14) 0%, transparent 70%);
    animation-delay: 0s;
}
.tcse-split-left__orb--2 {
    width: 250px;
    height: 250px;
    bottom: 40px;
    left: -60px;
    background: radial-gradient(circle, rgba(26, 54, 93, 0.3) 0%, transparent 70%);
    animation-delay: -4s;
}

/* Contenido */
.tcse-split-left__content {
    position: relative;
    z-index: 2;
    height: 100%;
    display: flex;
    flex-direction: column;
    padding: 2.5rem 3rem;
    font-family: 'Inter', sans-serif;
}

/* Badge superior */
.tcse-split-left__top {
    flex-shrink: 0;
    animation: tcse-fade-up 0.6s ease 0.1s both;
}

.tcse-split-left__badge {
    display: inline-flex;
    align-items: center;
    padding: 0.3rem 0.85rem;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    background: rgba(255, 255, 255, 0.07);
    color: rgba(255, 255, 255, 0.75);
    font-size: 0.7rem;
    font-weight: 600;
    letter-spacing: 0.09em;
    text-transform: uppercase;
    backdrop-filter: blur(8px);
}

/* Centro */
.tcse-split-left__center {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

/* Logo circular */
.tcse-split-left__logo-circle {
    width: 88px;
    height: 88px;
    border-radius: 50%;
    background-color: #1A365D;
    border: 3px solid rgba(255, 255, 255, 0.9);
    box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.12), 0 8px 32px rgba(0, 0, 0, 0.35);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    margin-bottom: 1.75rem;
    animation: tcse-logo-pop 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) 0.2s both;
    padding: 10px;
}

.tcse-split-left__logo-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    filter: brightness(0) invert(1);
}

.tcse-split-left__logo-fallback {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 1.8rem;
    font-weight: 800;
    font-family: 'Playfair Display', Georgia, serif;
    letter-spacing: -0.02em;
}

/* Título */
.tcse-split-left__title {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: clamp(1.65rem, 2.4vw, 2.15rem);
    font-weight: 700;
    color: #ffffff;
    line-height: 1.22;
    margin: 0 0 0.6rem;
    letter-spacing: -0.01em;
    animation: tcse-fade-up 0.65s ease 0.3s both;
}

/* Subtítulo */
.tcse-split-left__subtitle {
    font-size: 0.78rem;
    color: rgba(255, 255, 255, 0.5);
    margin: 0;
    font-weight: 500;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    animation: tcse-fade-up 0.65s ease 0.4s both;
}

/* Divisor */
.tcse-split-left__divider {
    width: 2.5rem;
    height: 2px;
    background: linear-gradient(90deg, rgba(66, 153, 225, 0.8), rgba(66, 153, 225, 0));
    margin: 1.75rem 0;
    border-radius: 999px;
    animation: tcse-fade-in 0.5s ease 0.55s both;
}

/* Features */
.tcse-split-left__features {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.tcse-split-left__features li {
    display: flex;
    align-items: center;
    gap: 0.9rem;
    color: rgba(255, 255, 255, 0.65);
    font-size: 0.84rem;
    line-height: 1.45;
    animation: tcse-fade-up 0.55s ease both;
}
.tcse-split-left__features li:nth-child(1) { animation-delay: 0.6s; }
.tcse-split-left__features li:nth-child(2) { animation-delay: 0.7s; }
.tcse-split-left__features li:nth-child(3) { animation-delay: 0.8s; }

.tcse-feature-icon {
    width: 2rem;
    height: 2rem;
    border-radius: 0.5rem;
    background: rgba(66, 153, 225, 0.12);
    border: 1px solid rgba(66, 153, 225, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.tcse-feature-icon svg {
    width: 1rem;
    height: 1rem;
    color: rgba(147, 197, 253, 0.85);
}

/* Footer */
.tcse-split-left__bottom {
    flex-shrink: 0;
    animation: tcse-fade-in 0.5s ease 1s both;
}

.tcse-split-left__bottom-line {
    width: 100%;
    height: 1px;
    background: rgba(255, 255, 255, 0.07);
    margin-bottom: 1rem;
}

.tcse-split-left__bottom p {
    color: rgba(255, 255, 255, 0.28);
    font-size: 0.72rem;
    margin: 0;
    letter-spacing: 0.05em;
}

/* ── Panel derecho — refinamiento del formulario ── */
.fi-simple-main .fi-simple-page {
    background: transparent !important;
    box-shadow: none !important;
    border: none !important;
    padding: 0 !important;
    max-width: 400px;
    width: 100%;
    margin: 0 auto;
}

/* Logo/branding en el panel derecho */
.fi-simple-page .fi-logo {
    margin-bottom: 0.25rem;
}

/* Ajustar heading del formulario nativo */
.fi-simple-page .fi-simple-page-heading {
    font-family: 'Inter', sans-serif;
    font-size: 1.5rem;
    font-weight: 700;
    color: #1A365D;
    letter-spacing: -0.03em;
}

.fi-simple-page .fi-simple-page-subheading {
    font-size: 0.85rem;
    color: #64748b;
    margin-top: 0.2rem;
}

/* Card del formulario */
.fi-simple-main .fi-section {
    border: 1px solid rgba(26, 54, 93, 0.1);
    box-shadow: 0 4px 24px -8px rgba(26, 54, 93, 0.12);
    border-radius: 1rem !important;
}

/* Botón submit: azul marino institucional */
.fi-simple-main .fi-btn.fi-btn-color-primary {
    background: linear-gradient(135deg, #1A365D 0%, #2a4a7f 100%) !important;
    border-color: transparent !important;
    box-shadow: 0 4px 16px -4px rgba(26, 54, 93, 0.4);
    font-weight: 600;
    font-family: 'Inter', sans-serif;
    letter-spacing: 0.01em;
    transition: all 0.25s ease;
    border-radius: 0.6rem !important;
}

.fi-simple-main .fi-btn.fi-btn-color-primary:hover {
    box-shadow: 0 6px 22px -4px rgba(26, 54, 93, 0.5);
    filter: brightness(1.08);
    transform: translateY(-1px);
}

.fi-simple-main .fi-btn.fi-btn-color-primary:active {
    transform: translateY(0);
}

/* Focus ring en inputs */
.fi-simple-main .fi-input-field:focus-within {
    box-shadow: 0 0 0 3px rgba(26, 54, 93, 0.12);
    border-color: #1A365D !important;
}

/* Links (Forgot password) */
.fi-simple-main .fi-link,
.fi-simple-main .fi-ac-link {
    color: #1A365D;
    font-weight: 500;
}
.fi-simple-main .fi-link:hover,
.fi-simple-main .fi-ac-link:hover {
    color: #2a4a7f;
}

/* Ocultar panel viejo si existe */
.tcse-login-panel {
    display: none !important;
}

/* Fondo del layout */
.fi-simple-layout {
    background: #f0f4f8 !important;
}

/* ── Modo oscuro ───────────────────────────────── */
.dark .fi-simple-main {
    background: #0f172a !important;
}
.dark .fi-simple-layout {
    background: #0a1628 !important;
}
.dark .fi-simple-main .fi-section {
    border-color: rgba(255, 255, 255, 0.07);
}
.dark .fi-simple-page .fi-simple-page-heading {
    color: #93c5fd;
}

/* ── Responsive ─────────────────────────────────── */
@media (max-width: 900px) {
    .tcse-split-left {
        display: none;
    }
    .fi-simple-main {
        width: 100% !important;
        min-height: 100dvh;
        padding: 2rem 1.5rem !important;
        margin: 0 auto !important;
        background: #fff !important;
        box-shadow: none;
    }
    .fi-simple-main-ctn {
        justify-content: center;
    }
}
</style>
