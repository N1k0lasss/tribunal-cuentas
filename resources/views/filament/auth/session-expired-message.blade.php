{{--
    Mensaje que se muestra en el login cuando la sesión se cerró automáticamente
    por inactividad (hook AUTH_LOGIN_FORM_AFTER, ver AppServiceProvider).
--}}
@if (! empty($mensaje))
    <div style="margin-top: 1rem; padding: 0.75rem 1rem; border-radius: 0.75rem; background-color: #fef3c7; border: 1px solid #f59e0b; color: #92400e; font-size: 0.875rem; line-height: 1.4; text-align: center;">
        {{ $mensaje }}
    </div>
@endif
