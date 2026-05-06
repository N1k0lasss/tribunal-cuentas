{{-- Left Split Panel — TCSE Login — Navy + Bordeaux --}}
<div class="tcse-split-left" id="tcse-login-left-panel">
    <div class="tcse-split-left__bg"></div>
    <div class="tcse-split-left__overlay"></div>
    <div class="tcse-orb tcse-orb--1"></div>
    <div class="tcse-orb tcse-orb--2"></div>
    <div class="tcse-orb tcse-orb--3"></div>

    <div class="tcse-split-left__content">

        {{-- Badge superior --}}
        <div class="tcse-top" style="animation: tcse-fadeup 0.6s ease 0.1s both;">
            <span class="tcse-badge">
                <span class="tcse-badge__dot"></span>
                Provincia de Santiago del Estero
            </span>
        </div>

        {{-- Centro: Logo + Título --}}
        <div class="tcse-center">

            {{-- Logo TC circular --}}
            <div class="tcse-logo-wrap" style="animation: tcse-pop 0.7s cubic-bezier(0.34,1.56,0.64,1) 0.2s both;">
                <div class="tcse-logo-ring tcse-logo-ring--outer"></div>
                <div class="tcse-logo-ring tcse-logo-ring--inner"></div>
                <div class="tcse-logo-circle">
                    <span class="tcse-logo-text">TC</span>
                </div>
            </div>

            <h1 class="tcse-title" style="animation: tcse-fadeup 0.65s ease 0.35s both;">
                Sistema de Gestión<br>de Licencias
            </h1>
            <p class="tcse-inst" style="animation: tcse-fadeup 0.65s ease 0.45s both;">
                Tribunal de Cuentas de Santiago del Estero
            </p>

            <div class="tcse-divider" style="animation: tcse-fadein 0.5s ease 0.6s both;"></div>

            <ul class="tcse-features">
                <li style="animation: tcse-fadeup 0.5s ease 0.65s both;">
                    <div class="tcse-feat-icon tcse-feat-icon--navy">
                        <svg viewBox="0 0 20 20" fill="none"><path d="M10 2L3 6v5c0 3.866 3.134 7.5 7 8.5 3.866-1 7-4.634 7-8.5V6L10 2z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><path d="M7 10l2 2 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                    <span>Solicitud digital con trazabilidad legal</span>
                </li>
                <li style="animation: tcse-fadeup 0.5s ease 0.75s both;">
                    <div class="tcse-feat-icon tcse-feat-icon--bordeaux">
                        <svg viewBox="0 0 20 20" fill="none"><path d="M3 5h14M3 10h14M3 15h9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </div>
                    <span>Circuito secuencial de aprobaciones</span>
                </li>
                <li style="animation: tcse-fadeup 0.5s ease 0.85s both;">
                    <div class="tcse-feat-icon tcse-feat-icon--navy">
                        <svg viewBox="0 0 20 20" fill="none"><circle cx="10" cy="7" r="3.5" stroke="currentColor" stroke-width="1.5"/><path d="M3 18c0-3 3.134-5 7-5s7 2 7 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </div>
                    <span>Legajo electrónico del agente</span>
                </li>
            </ul>
        </div>

        {{-- Footer --}}
        <div class="tcse-footer" style="animation: tcse-fadein 0.5s ease 1s both;">
            <div class="tcse-footer__line"></div>
            <p>Tribunal de Cuentas &mdash; {{ date('Y') }}</p>
        </div>
    </div>
</div>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,700;0,800;1,700&display=swap');

/* ──────────────────────────────────────────────────
   Keyframes
   ────────────────────────────────────────────────── */
@keyframes tcse-fadeup {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}
@keyframes tcse-fadein {
    from { opacity: 0; }
    to   { opacity: 1; }
}
@keyframes tcse-pop {
    0%   { opacity: 0; transform: scale(0.75); }
    65%  { transform: scale(1.05); }
    100% { opacity: 1; transform: scale(1); }
}
@keyframes tcse-orb-drift {
    0%, 100% { transform: translate(0, 0); }
    33%      { transform: translate(12px, -18px); }
    66%      { transform: translate(-8px, 10px); }
}
@keyframes tcse-ring-spin {
    from { transform: rotate(0deg); }
    to   { transform: rotate(360deg); }
}
@keyframes tcse-slide-in {
    from { opacity: 0; transform: translateX(24px); }
    to   { opacity: 1; transform: translateX(0); }
}

/* ──────────────────────────────────────────────────
   Layout base
   ────────────────────────────────────────────────── */
.fi-simple-layout {
    background: #f0f4f8 !important;
    font-family: 'Inter', sans-serif !important;
}

.fi-simple-main-ctn {
    position: relative;
    display: flex !important;
    align-items: stretch;
    min-height: 100dvh;
    width: 100%;
    max-width: 100% !important;
    padding: 0 !important;
}

/* ──────────────────────────────────────────────────
   Panel derecho (formulario Filament)
   ────────────────────────────────────────────────── */
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
    position: relative;
    z-index: 10;
    box-shadow: -20px 0 60px -10px rgba(26, 54, 93, 0.15);
    animation: tcse-slide-in 0.55s ease 0.1s both;
}

.fi-simple-main .fi-simple-page {
    background: transparent !important;
    box-shadow: none !important;
    border: none !important;
    padding: 0 !important;
    max-width: 400px;
    width: 100%;
    margin: 0 auto;
}

/* ── Logo/Branding del panel derecho ─────────────── */
.fi-simple-page .fi-logo {
    margin-bottom: 0.25rem;
}

/* Heading  */
.fi-simple-page .fi-simple-page-heading {
    font-family: 'Inter', sans-serif;
    font-size: 1.55rem;
    font-weight: 700;
    color: #1A365D;
    letter-spacing: -0.03em;
}

.fi-simple-page .fi-simple-page-subheading {
    font-size: 0.84rem;
    color: #6B7280;
    margin-top: 0.15rem;
}

/* Tarjeta del formulario */
.fi-simple-main .fi-section {
    border: 1px solid rgba(26, 54, 93, 0.1);
    box-shadow: 0 4px 24px -8px rgba(26, 54, 93, 0.1);
    border-radius: 1.1rem !important;
    overflow: hidden;
}

/* Inputs — focus */
.fi-simple-main .fi-input-field:focus-within {
    box-shadow: 0 0 0 3px rgba(107, 28, 94, 0.12);
    border-color: #6b1c5e !important;
}

/* Botón Submit: Gradiente navy → bordeaux */
.fi-simple-main .fi-btn.fi-btn-color-primary {
    background: linear-gradient(135deg, #1A365D 0%, #6b1c5e 100%) !important;
    border-color: transparent !important;
    box-shadow: 0 4px 18px -4px rgba(107, 28, 94, 0.4);
    font-weight: 600;
    font-family: 'Inter', sans-serif;
    letter-spacing: 0.015em;
    border-radius: 0.65rem !important;
    transition: all 0.25s ease;
}

.fi-simple-main .fi-btn.fi-btn-color-primary:hover {
    box-shadow: 0 8px 24px -4px rgba(107, 28, 94, 0.5);
    filter: brightness(1.08);
    transform: translateY(-1px);
}

.fi-simple-main .fi-btn.fi-btn-color-primary:active {
    transform: translateY(0);
    filter: brightness(0.98);
}

/* Links */
.fi-simple-main .fi-link,
.fi-simple-main .fi-ac-link {
    color: #6b1c5e;
    font-weight: 500;
}
.fi-simple-main .fi-link:hover,
.fi-simple-main .fi-ac-link:hover {
    color: #1A365D;
}

/* ──────────────────────────────────────────────────
   Panel izquierdo
   ────────────────────────────────────────────────── */
.tcse-split-left {
    position: fixed;
    top: 0;
    left: 0;
    width: 50%;
    height: 100%;
    z-index: 5;
    overflow: hidden;
}

/* Imagen de fondo */
.tcse-split-left__bg {
    position: absolute;
    inset: 0;
    background-image: url('/images/login-bg.jpg');
    background-size: cover;
    background-position: center top;
    transform: scale(1.06);
    transition: transform 16s ease;
}
.tcse-split-left:hover .tcse-split-left__bg {
    transform: scale(1.0);
}

/* Overlay: navy + bordeaux */
.tcse-split-left__overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        145deg,
        rgba(10, 22, 48, 0.93) 0%,
        rgba(26, 54, 93, 0.82) 35%,
        rgba(80, 14, 67, 0.78) 70%,
        rgba(107, 28, 94, 0.90) 100%
    );
}

/* Orbs decorativos */
.tcse-orb {
    position: absolute;
    border-radius: 50%;
    pointer-events: none;
    animation: tcse-orb-drift ease-in-out infinite;
}
.tcse-orb--1 {
    width: 350px; height: 350px;
    top: -100px; right: -100px;
    background: radial-gradient(circle, rgba(107,28,94,0.22) 0%, transparent 70%);
    animation-duration: 11s; animation-delay: 0s;
}
.tcse-orb--2 {
    width: 280px; height: 280px;
    bottom: 60px; left: -80px;
    background: radial-gradient(circle, rgba(26,54,93,0.25) 0%, transparent 70%);
    animation-duration: 9s; animation-delay: -3s;
}
.tcse-orb--3 {
    width: 200px; height: 200px;
    top: 40%; left: 30%;
    background: radial-gradient(circle, rgba(66,153,225,0.08) 0%, transparent 70%);
    animation-duration: 14s; animation-delay: -6s;
}

/* Contenido del panel */
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
.tcse-top { flex-shrink: 0; }

.tcse-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.3rem 0.85rem;
    border-radius: 999px;
    border: 1px solid rgba(255,255,255,0.18);
    background: rgba(255,255,255,0.06);
    color: rgba(255,255,255,0.72);
    font-size: 0.68rem;
    font-weight: 600;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    backdrop-filter: blur(10px);
}

.tcse-badge__dot {
    width: 6px; height: 6px;
    border-radius: 50%;
    background: linear-gradient(135deg, #90cdf4, #b794f4);
    flex-shrink: 0;
}

/* Centro */
.tcse-center {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

/* Logo TC */
.tcse-logo-wrap {
    position: relative;
    width: 90px; height: 90px;
    margin-bottom: 1.75rem;
}

.tcse-logo-ring {
    position: absolute;
    border-radius: 50%;
    border-style: solid;
}
.tcse-logo-ring--outer {
    inset: -6px;
    border-width: 1px;
    border-color: rgba(255,255,255,0.12);
    animation: tcse-ring-spin 20s linear infinite;
    border-style: dashed;
}
.tcse-logo-ring--inner {
    inset: -2px;
    border-width: 2px;
    border-color: rgba(255,255,255,0.35);
}

.tcse-logo-circle {
    width: 90px; height: 90px;
    border-radius: 50%;
    background: linear-gradient(145deg, #1A365D, #0d1f3c);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    z-index: 1;
    box-shadow: 0 8px 32px rgba(0,0,0,0.4), inset 0 1px 0 rgba(255,255,255,0.1);
}

.tcse-logo-text {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 2rem;
    font-weight: 800;
    color: #ffffff;
    letter-spacing: -0.04em;
    line-height: 1;
    /* Bordeaux gradient on text */
    background: linear-gradient(135deg, #ffffff 0%, #f0c0e8 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* Título */
.tcse-title {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: clamp(1.65rem, 2.4vw, 2.2rem);
    font-weight: 700;
    color: #ffffff;
    line-height: 1.22;
    margin: 0 0 0.5rem;
    letter-spacing: -0.01em;
}

/* Institución */
.tcse-inst {
    font-size: 0.78rem;
    color: rgba(255,255,255,0.48);
    margin: 0;
    font-weight: 500;
    letter-spacing: 0.04em;
}

/* Divider con gradiente navy→bordeaux */
.tcse-divider {
    width: 3rem;
    height: 2px;
    background: linear-gradient(90deg, #90cdf4, #e9a8e0, transparent);
    margin: 1.75rem 0;
    border-radius: 999px;
}

/* Features */
.tcse-features {
    list-style: none;
    padding: 0; margin: 0;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.tcse-features li {
    display: flex;
    align-items: center;
    gap: 0.9rem;
    color: rgba(255,255,255,0.62);
    font-size: 0.83rem;
    line-height: 1.45;
}

.tcse-feat-icon {
    width: 2.1rem; height: 2.1rem;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.tcse-feat-icon--navy {
    background: rgba(26,54,93,0.35);
    border: 1px solid rgba(66,153,225,0.25);
}
.tcse-feat-icon--bordeaux {
    background: rgba(107,28,94,0.35);
    border: 1px solid rgba(233,168,224,0.25);
}

.tcse-feat-icon svg {
    width: 1rem; height: 1rem;
}
.tcse-feat-icon--navy svg  { color: rgba(147,197,253,0.85); }
.tcse-feat-icon--bordeaux svg { color: rgba(233,168,224,0.85); }

/* Footer */
.tcse-footer { flex-shrink: 0; }
.tcse-footer__line {
    width: 100%; height: 1px;
    background: rgba(255,255,255,0.07);
    margin-bottom: 0.85rem;
}
.tcse-footer p {
    color: rgba(255,255,255,0.25);
    font-size: 0.7rem;
    margin: 0;
    letter-spacing: 0.05em;
}

/* ── Logo circular header (panel derecho) ──────── */
.tcse-rp-header {
    display: flex;
    justify-content: center;
    margin-bottom: 1.25rem;
}

.tcse-rp-logo-circle {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    background-color: #1A365D;
    border: 4px solid #ffffff;
    box-shadow: 0 4px 20px rgba(26,54,93,0.3), 0 0 0 4px rgba(26,54,93,0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    padding: 10px;
    animation: tcse-pop 0.6s cubic-bezier(0.34,1.56,0.64,1) 0.1s both;
}

.tcse-rp-logo-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    filter: brightness(0) invert(1);
}

/* Heading/subheading de Filament → estilo TCSE */
.fi-simple-main .fi-simple-page-heading {
    font-family: 'Inter', sans-serif;
    font-size: 1.4rem;
    font-weight: 700;
    color: #1A365D;
    letter-spacing: -0.025em;
    text-align: center;
}

.fi-simple-main .fi-simple-page-subheading {
    font-size: 0.82rem;
    color: #64748b;
    text-align: center;
}

.tcse-login-panel { display: none !important; }

/* ════════════════════════════════════════════════════
   PANEL DERECHO — Custom right panel
   ════════════════════════════════════════════════════ */

/* Ocultar logo/heading por defecto de Filament (los reemplazamos) */
.fi-simple-main .fi-logo { display: none !important; }
.fi-simple-main .fi-simple-page-heading { display: none !important; }
.fi-simple-main .fi-simple-page-subheading { display: none !important; }

/* Wrapper del logo + título custom */
.tcse-rp-logo-wrap {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    margin-bottom: 1.5rem;
    animation: tcse-fadeup 0.6s ease 0.2s both;
}

/* Círculo del logo */
.tcse-rp-logo-circle {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    background-color: #1A365D;
    border: 4px solid #ffffff;
    box-shadow: 0 4px 20px rgba(26,54,93,0.3), 0 0 0 4px rgba(26,54,93,0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    padding: 10px;
    margin-bottom: 1.1rem;
    animation: tcse-pop 0.6s cubic-bezier(0.34,1.56,0.64,1) 0.1s both;
}

.tcse-rp-logo-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    filter: brightness(0) invert(1);
}

/* Título del panel derecho */
.tcse-rp-title {
    font-family: 'Inter', sans-serif;
    font-size: 1.4rem;
    font-weight: 700;
    color: #1A365D;
    line-height: 1.25;
    margin: 0 0 0.3rem;
    letter-spacing: -0.02em;
}

/* Subtítulo */
.tcse-rp-subtitle {
    font-size: 0.82rem;
    color: #64748b;
    margin: 0;
    font-weight: 400;
}

/* Footer link */
.tcse-rp-footer {
    text-align: center;
    font-size: 0.82rem;
    color: #1A365D;
    font-weight: 600;
    margin-top: 1.25rem;
    cursor: pointer;
}
.tcse-rp-footer:hover { color: #0d1b2e; text-decoration: underline; }

/* ── Botón submit — Azul Marino sólido ─────────────── */
.fi-simple-main button[type="submit"] {
    background-color: #1A365D !important;
    border-color: transparent !important;
    box-shadow: 0 4px 14px -4px rgba(26, 54, 93, 0.45) !important;
    font-weight: 600 !important;
    font-family: 'Inter', sans-serif !important;
    border-radius: 0.65rem !important;
    transition: all 0.2s ease !important;
    color: white !important;
}
.fi-simple-main button[type="submit"]:hover {
    background-color: #132743 !important;
    box-shadow: 0 6px 20px -4px rgba(26, 54, 93, 0.55) !important;
    transform: translateY(-1px) !important;
}
.fi-simple-main button[type="submit"]:active {
    transform: translateY(0) !important;
}

/* Focus ring inputs */
.fi-simple-main .fi-input-field:focus-within {
    box-shadow: 0 0 0 3px rgba(26, 54, 93, 0.12);
    border-color: #1A365D !important;
}

/* Links */
.fi-simple-main .fi-link,
.fi-simple-main .fi-ac-link {
    color: #1A365D;
    font-weight: 500;
}
.fi-simple-main .fi-link:hover,
.fi-simple-main .fi-ac-link:hover { color: #0d1b2e; }

/* ── Dark mode ───────────────────────────────────── */
.dark .fi-simple-main        { background: #0f172a !important; }
.dark .fi-simple-layout      { background: #0a1628 !important; }
.dark .fi-simple-main .fi-section { border-color: rgba(255,255,255,0.07); }
.dark .tcse-rp-title         { color: #93c5fd; }

/* ── Responsive ──────────────────────────────────── */
@media (max-width: 900px) {
    .tcse-split-left { display: none; }
    .fi-simple-main {
        width: 100% !important;
        min-height: 100dvh;
        padding: 2rem 1.5rem !important;
        margin: 0 auto !important;
        box-shadow: none;
    }
    .fi-simple-main-ctn { justify-content: center; }
}
</style>
