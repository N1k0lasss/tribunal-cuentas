<div class="flex min-h-screen bg-gray-50">
    <div class="m-auto w-full max-w-md p-6">
        
        <div class="flex flex-col items-center mb-10">
            <div style="background-color: #1A365D;" class="w-24 h-24 border-4 border-white rounded-full flex items-center justify-center shadow-lg mb-4 overflow-hidden p-3">
                <img 
                    src="https://lh5.googleusercontent.com/proxy/ix6C3trZYbM3XE6OgO2BqOUHQJt-tA849Bvz2vVwFn4l2LwIhWRBmpYx9ufIocivTUBtFaEyDZnktm9__INzzV-3VhjlM-BnaPCoUEtWjvd-GZbRHuJnC-MoXQYxEEpBGSsP6dgzLJWXG6o" 
                    alt="Logo del Tribunal de Cuentas de Santiago del Estero"
                    class="object-contain w-full h-full"
                >
            </div>
            
            <h1 class="text-2xl font-bold text-slate-800 text-center leading-tight">
                Sistema de Gestión de Licencias
            </h1>
            <p class="text-slate-500 font-medium text-center">
                Tribunal de Cuentas de Santiago del Estero
            </p>
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
            <a href="#" class="text-[#1A365D] font-bold hover:underline text-sm">
                ¿No tiene cuenta? Regístrese aquí
            </a>
        </div>

    </div>
</div>