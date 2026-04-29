<x-filament-panels::page.simple>

    {{-- Logo circular institucional --}}
    <x-slot name="logo">
        <div class="tcse-rp-logo-wrap">
            <div class="tcse-rp-logo-circle">
                <img
                    src="{{ asset('images/tcse-logo.png') }}"
                    alt="Tribunal de Cuentas de Santiago del Estero"
                    class="tcse-rp-logo-img"
                >
            </div>
            <h1 class="tcse-rp-title">Sistema de Gestión de Licencias</h1>
            <p class="tcse-rp-subtitle">Tribunal de Cuentas de Santiago del Estero</p>
        </div>
    </x-slot>

    {{-- Formulario de autenticación --}}
    @if (filament()->hasLogin())
        <x-filament-panels::form wire:submit="authenticate">
            {{ $this->form }}

            <x-filament-panels::form.actions
                :actions="$this->getCachedFormActions()"
                :full-width="$this->hasFullWidthFormActions()"
            />
        </x-filament-panels::form>
    @endif

    {{-- Footer --}}
    <p class="tcse-rp-footer">
        ¿No tiene cuenta? Comuníquese con el administrador.
    </p>

</x-filament-panels::page.simple>
