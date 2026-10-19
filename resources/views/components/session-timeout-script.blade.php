{{--
    Cierre automático por inactividad (lado cliente).

    - Se inyecta solo con sesión iniciada (ver AppServiceProvider, hook BODY_END).
    - El temporizador y el "latido" al servidor solo se reinician con
      interacción REAL (mouse / teclado), no con el polling de Livewire.
    - El tiempo de inactividad se toma de config/session.php (SESSION_LIFETIME).

    Puntos de entrada al cierre:
      1. El modal (a los N minutos) hace el logout en el servidor y navega.
      2. El latido detecta que el servidor ya cerró la sesión (419/401/redirect).
      3. Un request de Livewire que falla con 419/401.

    Los tres desembocan en `irAlLoginExpirado()`, que es el ÚNICO lugar que
    navega al login. Así se evita la carrera en la que el modal alcanzaba a
    mandar su POST con la sesión ya cerrada y aterrizaba en el login "pelado"
    (sin el aviso de inactividad).
--}}
<script id="session-timeout-script">
    (function () {
        // Minutos de inactividad antes de cerrar la sesión.
        const maxInactivity = {{ (int) config('session.inactivity_lifetime', config('session.lifetime', 15)) }} * 60 * 1000;

        const actividadUrl = @json(route('session.actividad'));
        const loginUrl = @json(filament()->getLoginUrl());
        const loginExpiradaUrl = loginUrl + (loginUrl.indexOf('?') === -1 ? '?' : '&') + 'sesion_expirada=1';
        const logoutUrl = @json(filament()->getLogoutUrl());

        let timeoutTimer = null; // Dispara el modal.
        let logoutTimer = null; // Dispara el POST de logout (con el modal ya visible).
        let ultimoLatido = 0; // Momento del último latido enviado al servidor.
        let expirado = false; // El modal ya se mostró.
        let redirigiendo = false; // Ya vamos camino al login: no navegar dos veces.

        function csrfToken() {
            const meta = document.querySelector('meta[name="csrf-token"]');
            return meta ? meta.getAttribute('content') : '';
        }

        // Intervalo mínimo entre latidos: 1 minuto, pero nunca más de un tercio
        // del tiempo de inactividad configurado (por si se baja SESSION_LIFETIME
        // a menos de 3 minutos, el latido sigue llegando a tiempo).
        const latidoMs = Math.min(60000, Math.floor(maxInactivity / 3));

        // ÚNICO punto de salida hacia el login con el aviso de inactividad.
        // Idempotente: la primera llamada gana, las demás no hacen nada.
        function irAlLoginExpirado() {
            if (redirigiendo) return;
            redirigiendo = true;
            expirado = true;
            clearTimeout(timeoutTimer);
            clearTimeout(logoutTimer);
            window.location.href = loginExpiradaUrl;
        }

        // Avisa al servidor que hubo actividad REAL. El polling de Livewire no
        // cuenta: esto solo se llama con eventos de mouse/teclado.
        function enviarLatido(forzar) {
            if (redirigiendo) return;

            const ahora = Date.now();
            if (! forzar && (ahora - ultimoLatido) < latidoMs) return;
            ultimoLatido = ahora;

            fetch(actividadUrl, {
                method: 'POST',
                credentials: 'same-origin',
                keepalive: true,
                headers: {
                    'X-CSRF-TOKEN': csrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            }).then(function (res) {
                // El servidor ya cerró la sesión (419 / 401) o nos redirigió al
                // login: llevamos al usuario al login con el aviso visible.
                if (res.status === 419 || res.status === 401 || res.redirected) {
                    irAlLoginExpirado();
                }
            }).catch(function () { /* Silencioso: no rompemos la UI por un latido. */ });
        }

        function resetTimer() {
            if (redirigiendo || expirado) return;
            clearTimeout(timeoutTimer);
            timeoutTimer = setTimeout(showExpiredModal, maxInactivity);
            enviarLatido(false);
        }

        function showExpiredModal() {
            if (redirigiendo) return;
            expirado = true;
            clearTimeout(timeoutTimer);

            // Crear el cartel (modal)
            const modalHtml = `
                <div id="session-timeout-modal" style="position: fixed; inset: 0; z-index: 9999; display: flex; align-items: center; justify-content: center; background-color: rgba(17, 24, 39, 0.8); backdrop-filter: blur(4px);">
                    <div style="background-color: white; padding: 2rem; border-radius: 1rem; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); max-width: 24rem; width: 100%; text-align: center; font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">
                        <div style="margin-bottom: 1.5rem; display: flex; justify-content: center;">
                            <div style="border-radius: 9999px; background-color: #fee2e2; padding: 0.75rem;">
                                <svg style="width: 2.5rem; height: 2.5rem; color: #dc2626;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                            </div>
                        </div>
                        <h2 style="font-size: 1.5rem; line-height: 2rem; font-weight: 700; color: #111827; margin-bottom: 0.5rem; margin-top: 0;">
                            Tu sesión terminó
                        </h2>
                        <p style="color: #6b7280; margin-bottom: 1rem;">
                            Por inactividad, hemos cerrado tu sesión por seguridad.
                        </p>
                        <p style="color: #2563eb; font-weight: 500; font-size: 0.875rem;">
                            Redirigiendo al login automáticamente...
                        </p>
                    </div>
                </div>
            `;

            // Insertar el cartel al final del body
            document.body.insertAdjacentHTML('beforeend', modalHtml);

            // A los 3s: destruir la sesión en el servidor y navegar SIEMPRE al
            // login con el aviso. Se usa fetch + keepalive (en lugar de un
            // <form>.submit()) para que la petición sobreviva a la navegación y
            // para que el destino no dependa de a dónde redirija el servidor.
            logoutTimer = setTimeout(function () {
                try {
                    const datos = new FormData();
                    datos.append('_token', csrfToken());
                    fetch(logoutUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        keepalive: true,
                        headers: {
                            'X-CSRF-TOKEN': csrfToken(),
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        body: datos,
                    }).catch(function () { /* Silencioso. */ });
                } catch (e) { /* Silencioso: igual navegamos al login. */ }

                irAlLoginExpirado();
            }, 3000);
        }

        const events = [
            'mousemove', 'keydown', 'wheel', 'DOMMouseScroll', 'mousewheel',
            'mousedown', 'touchstart', 'touchmove', 'MSPointerDown', 'MSPointerMove',
            'scroll', 'click'
        ];

        events.forEach(function (event) {
            document.addEventListener(event, resetTimer, { passive: true });
        });

        // Latido inicial: una carga de página cuenta como actividad. Va antes de
        // resetTimer() para no duplicar la petición (el throttle de resetTimer
        // reutiliza este mismo latido).
        enviarLatido(true);
        resetTimer();

        // Interceptar el error 419 (Página expirada) de Livewire para redirigir al
        // login sin el molesto popup del navegador ("This page has expired...").
        //
        // OJO: los scripts de Filament/Livewire son bloqueantes (sin defer) y van
        // en el <head>, así que Livewire YA está inicializado cuando corre este
        // script y el evento `livewire:init` ya se disparó. Por eso registramos el
        // hook de inmediato y, además, por las dudas, escuchamos `livewire:init`.
        (function () {
            let registrado = false;

            function interceptar419() {
                if (registrado || typeof Livewire === 'undefined' || typeof Livewire.hook !== 'function') {
                    return;
                }
                registrado = true;

                Livewire.hook('request', ({ fail }) => {
                    fail(({ status, preventDefault }) => {
                        if (status === 419 || status === 401) {
                            preventDefault();
                            irAlLoginExpirado();
                        }
                    });
                });
            }

            document.addEventListener('livewire:init', interceptar419);
            interceptar419();
        })();
    })();
</script>
