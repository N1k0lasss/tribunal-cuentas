<div class="flex min-h-screen bg-gray-50">
    <div class="m-auto w-full max-w-md p-6">
        
        <div class="flex flex-col items-center mb-10">
            <div class="w-20 h-20 bg-[#1e3a5f] rounded-full flex items-center justify-center mb-4 shadow-sm">
                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-slate-800 text-center">Sistema de Gestión de Licencias</h2>
            <p class="text-slate-500 font-medium">Tribunal de Cuentas de Santiago del Estero</p>
        </div>

        <div class="space-y-6">
            <x-filament-panels::form wire:submit="authenticate">
                {{ $this->form }}

                <x-filament-panels::form.actions
                    :actions="$this->getCachedFormActions()"
                    :full-width="true"
                    class="mt-6"
                />
            </x-filament-panels::form>
        </div>

        <div class="mt-10 text-center">
            <a href="#" class="text-[#1e3a5f] font-bold hover:underline">
                ¿No tiene cuenta? Regístrese aquí
            </a>
            
            <div class="mt-8 text-xs text-slate-400">
                <p>Usuarios de prueba:</p>
                <p>Admin: admin@tribunalcuentas.gob.ar / admin123</p>
            </div>
        </div>
    </div>
</div>