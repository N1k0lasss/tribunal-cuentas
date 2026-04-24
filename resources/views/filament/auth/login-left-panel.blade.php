{{-- Left Split Panel — only injected on the login page --}}
<div class="tcse-split-left" id="tcse-login-left-panel">
    {{-- Background image --}}
    <div class="tcse-split-left__bg"></div>

    {{-- Overlay gradient --}}
    <div class="tcse-split-left__overlay"></div>

    {{-- Content --}}
    <div class="tcse-split-left__content">

        {{-- Top: Institution badge --}}
        <div class="tcse-split-left__top">
            <span class="tcse-split-left__badge">Provincia de Santiago del Estero</span>
        </div>

        {{-- Center: Logo + headline --}}
        <div class="tcse-split-left__center">
            @php
                $logoPath = public_path('images/tcse-logo.png');
                $hasLogo = file_exists($logoPath);
            @endphp

            <div class="tcse-split-left__logo-wrap">
                @if ($hasLogo)
                    <img src="{{ asset('images/tcse-logo.png') }}" alt="TC" class="tcse-split-left__logo">
                @else
                    <div class="tcse-split-left__logo-fallback">TC</div>
                @endif
            </div>

            <h1 class="tcse-split-left__title">
                Sistema de Licencias<br>y Permisos
            </h1>
            <p class="tcse-split-left__subtitle">
                Gestión digital integral
            </p>

            <div class="tcse-split-left__divider"></div>

            <ul class="tcse-split-left__features">
                <li>
                    <svg viewbox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 1.5L2 5.5V10C2 14.125 5.5 17.975 10 19C14.5 17.975 18 14.125 18 10V5.5L10 1.5Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                        <path d="M7 10L9 12L13 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Solicitud digital con trazabilidad legal
                </li>
                <li>
                    <svg viewbox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2 5H18M2 10H18M2 15H12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    Circuito secuencial de aprobaciones
                </li>
                <li>
                    <svg viewbox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="10" cy="8" r="4" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M3 18C3 15.239 6.134 13 10 13C13.866 13 17 15.239 17 18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    Legajo electrónico del agente
                </li>
            </ul>
        </div>

        {{-- Bottom: Year/Institution --}}
        <div class="tcse-split-left__bottom">
            <p>Tribunal de Cuentas &mdash; {{ date('Y') }}</p>
        </div>
    </div>
</div>

<style>
/* ── Split-screen wrapper ──────────────────────────── */
.fi-simple-main-ctn {
    position: relative;
    display: flex !important;
    align-items: stretch;
    min-height: 100dvh;
    width: 100%;
    max-width: 100% !important;
    padding: 0 !important;
}

/* Move main to the right half */
.fi-simple-main {
    width: 50% !important;
    min-width: 380px;
    max-width: none !important;
    margin: 0 0 0 auto !important;
    padding: 2rem 3rem !important;
    display: flex !important;
    flex-direction: column !important;
    justify-content: center !important;
    background: #ffffff !important;
    min-height: 100dvh;
    box-shadow: -20px 0 60px -20px rgba(0,0,0,0.18);
    position: relative;
    z-index: 10;
}

/* ── Left panel ────────────────────────────────────── */
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
    background-position: center;
    transform: scale(1.04);
    transition: transform 12s ease;
}

.tcse-split-left:hover .tcse-split-left__bg {
    transform: scale(1.0);
}

.tcse-split-left__overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        150deg,
        rgba(76, 5, 52, 0.82) 0%,
        rgba(45, 10, 40, 0.78) 40%,
        rgba(14, 26, 50, 0.85) 100%
    );
}

.tcse-split-left__content {
    position: relative;
    z-index: 2;
    height: 100%;
    display: flex;
    flex-direction: column;
    padding: 2.5rem 3rem;
}

/* Top badge */
.tcse-split-left__top {
    flex-shrink: 0;
}

.tcse-split-left__badge {
    display: inline-block;
    padding: 0.3rem 0.9rem;
    border-radius: 999px;
    border: 1px solid rgba(255,255,255,0.25);
    background: rgba(255,255,255,0.08);
    color: rgba(255,255,255,0.75);
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    backdrop-filter: blur(6px);
}

/* Center content */
.tcse-split-left__center {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.tcse-split-left__logo-wrap {
    margin-bottom: 1.5rem;
}

.tcse-split-left__logo {
    width: 72px;
    height: 72px;
    object-fit: contain;
    filter: brightness(0) invert(1);
    opacity: 0.92;
}

.tcse-split-left__logo-fallback {
    width: 72px;
    height: 72px;
    border-radius: 18px;
    display: grid;
    place-items: center;
    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.25);
    color: #fff;
    font-size: 1.6rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    font-family: 'Georgia', serif;
}

.tcse-split-left__title {
    font-family: 'Georgia', 'Times New Roman', serif;
    font-size: clamp(1.6rem, 2.5vw, 2.2rem);
    font-weight: 700;
    color: #ffffff;
    line-height: 1.25;
    margin: 0 0 0.6rem;
    letter-spacing: -0.01em;
}

.tcse-split-left__subtitle {
    font-size: 0.9rem;
    color: rgba(255,255,255,0.55);
    margin: 0;
    font-weight: 400;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.tcse-split-left__divider {
    width: 2.5rem;
    height: 2px;
    background: linear-gradient(90deg, rgba(255,255,255,0.6), rgba(255,255,255,0));
    margin: 1.75rem 0;
    border-radius: 999px;
}

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
    gap: 0.75rem;
    color: rgba(255,255,255,0.68);
    font-size: 0.85rem;
    line-height: 1.4;
}

.tcse-split-left__features li svg {
    width: 1.1rem;
    height: 1.1rem;
    color: rgba(255,255,255,0.45);
    flex-shrink: 0;
}

/* Bottom */
.tcse-split-left__bottom p {
    color: rgba(255,255,255,0.3);
    font-size: 0.75rem;
    margin: 0;
    letter-spacing: 0.04em;
}

/* ── Right panel (login form) — style overrides ────── */
/* Remove the default page background */
.fi-simple-main .fi-simple-page {
    background: transparent !important;
    box-shadow: none !important;
    border: none !important;
    padding: 0 !important;
    max-width: 420px;
    width: 100%;
    margin: 0 auto;
}

.fi-simple-main .fi-section {
    border: 1px solid rgba(0,0,0,0.085);
    box-shadow: 0 8px 32px -12px rgba(0,0,0,0.12);
    border-radius: 1.125rem !important;
}

/* Logo / heading area on the right */
.fi-simple-page .fi-logo {
    margin-bottom: 0.5rem;
}

/* Heading */
.fi-simple-page .fi-simple-page-heading {
    font-size: 1.4rem;
    font-weight: 700;
    color: #111827;
    letter-spacing: -0.025em;
}

.fi-simple-page .fi-simple-page-subheading {
    font-size: 0.85rem;
    color: #6b7280;
}

/* CTA button */
.fi-simple-main .fi-btn.fi-btn-color-primary {
    background: linear-gradient(135deg, #6d0b4f 0%, #8a1f67 100%) !important;
    border-color: transparent !important;
    box-shadow: 0 4px 14px -4px rgba(109,11,79,0.45);
    font-weight: 600;
    letter-spacing: 0.01em;
    transition: all 0.2s ease;
}

.fi-simple-main .fi-btn.fi-btn-color-primary:hover {
    box-shadow: 0 6px 20px -4px rgba(109,11,79,0.55);
    filter: brightness(1.05);
}

/* Input focus ring */
.fi-simple-main .fi-input-field:focus-within {
    box-shadow: 0 0 0 3px rgba(109,11,79,0.12);
}

/* Forgot password link */
.fi-simple-main .fi-link,
.fi-simple-main .fi-ac-link {
    color: #6d0b4f;
}

/* Remove the old tcse panel if it's rendered (render hook still active) */
.tcse-login-panel {
    display: none !important;
}

/* Remove plain background on whole page */
.fi-simple-layout {
    background: #ffffff !important;
}

/* ── Dark mode ─────────────────────────────────────── */
.dark .fi-simple-main {
    background: #111827 !important;
}

.dark .fi-simple-layout {
    background: #0f172a !important;
}

.dark .fi-simple-main .fi-section {
    border-color: rgba(255,255,255,0.07);
}

/* ── Responsive ────────────────────────────────────── */
@media (max-width: 768px) {
    .tcse-split-left {
        display: none;
    }

    .fi-simple-main {
        width: 100% !important;
        min-height: 100dvh;
        padding: 2rem 1.5rem !important;
        margin: 0 auto !important;
        background: #fff !important;
    }

    .fi-simple-main-ctn {
        justify-content: center;
    }
}
</style>
