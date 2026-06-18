<script>
    document.addEventListener('DOMContentLoaded', function() {
        let timeoutTimer;
        // 1 minuto en milisegundos
        const maxInactivity = 5 * 60 * 1000;
        let isExpired = false;

        function resetTimer() {
            if (isExpired) return; // Si ya expiró, no hacemos nada más
            clearTimeout(timeoutTimer);
            timeoutTimer = setTimeout(showExpiredModal, maxInactivity);
        }

        function showExpiredModal() {
            isExpired = true;
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
            
            // Enviar petición POST a /admin/logout para destruir la sesión obligatoriamente
            setTimeout(function() {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ filament()->getLogoutUrl() }}';
                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = '{{ csrf_token() }}';
                form.appendChild(csrf);
                document.body.appendChild(form);
                form.submit();
            }, 3000);
        }

        const events = [
            'mousemove', 'keydown', 'wheel', 'DOMMouseScroll', 'mousewheel', 
            'mousedown', 'touchstart', 'touchmove', 'MSPointerDown', 'MSPointerMove'
        ];
        
        events.forEach(function(event) {
            document.addEventListener(event, resetTimer, false);
        });

        // Iniciar el temporizador al cargar
        resetTimer();
    });

    // Interceptar el error 419 (Página expirada) de Livewire para evitar el molesto popup del navegador
    document.addEventListener('livewire:init', () => {
        Livewire.hook('request', ({ fail }) => {
            fail(({ status, preventDefault }) => {
                if (status === 419) {
                    preventDefault();
                    window.location.href = '{{ filament()->getLoginUrl() }}';
                }
            });
        });
    });
</script>
