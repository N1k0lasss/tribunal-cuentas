<x-filament-widgets::widget>
    <x-filament::section class="overflow-hidden !p-0">
        <div class="relative flex flex-col items-center justify-center p-6 md:p-8 bg-white dark:bg-gray-900 w-full">
            {{-- Fondo decorativo sutil (Navy) a la izquierda (Opcional, lo dejamos como detalle) --}}
            <div class="absolute inset-y-0 left-0 w-24 bg-[#1A365D] opacity-10" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 16px 16px; clip-path: polygon(0 0, 100% 0, 0% 100%);"></div>

            {{-- Fila Superior: Información del Agente --}}
            <div class="relative z-10 w-full flex flex-col items-center justify-center">
                {{-- Nombre y Avatar en la misma línea --}}
                <div class="flex items-center justify-center gap-4">
                    <div class="flex-shrink-0">
                        <div class="p-1 bg-white dark:bg-gray-800 rounded-full shadow-sm ring-2 ring-[#1A365D]/20">
                            <x-filament::avatar
                                size="w-16 h-16 md:w-20 md:h-20"
                                src="{{ filament()->getUserAvatarUrl(auth()->user()) }}"
                            />
                        </div>
                    </div>
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-950 dark:text-white tracking-tight truncate">
                        {{ $this->agente->apellido ?? '...' }}, {{ $this->agente->nombre ?? '...' }}
                    </h2>
                </div>
                
                {{-- Badges --}}
                <div class="mt-5 flex items-center justify-center gap-4 flex-wrap">
                    <x-filament::badge color="info" size="lg" class="px-4 py-1.5 text-sm">
                        Nº de Legajo: {{ $this->agente->id ?? 'N/A' }}
                    </x-filament::badge>
                    <x-filament::badge color="secondary" size="lg" class="px-4 py-1.5 text-sm">
                        {{ $this->agente->cargo ?? 'Sin Cargo' }}
                    </x-filament::badge>
                </div>

                {{-- Area --}}
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-3 font-medium flex items-center justify-center gap-1.5">
                    <x-heroicon-m-building-office class="w-4 h-4 text-gray-400" />
                    {{ optional($this->agente->area)->nombre ?? 'Sin Área Asignada' }}
                </p>
            </div>

            {{-- Separador --}}
            <hr class="border-gray-100 dark:border-gray-800 my-6 relative z-10 w-full max-w-4xl">

            {{-- Fila Inferior: Botones Centrados --}}
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 relative z-10 w-full">
                <x-filament::button
                    tag="a"
                    href="{{ \App\Filament\Admin\Resources\Solicituds\SolicitudResource::getUrl('create') }}"
                    icon="heroicon-m-plus"
                    size="lg"
                    style="background-color: #6B1C5E; color: white; border: none; box-shadow: 0 4px 6px -1px rgba(107, 28, 94, 0.3);"
                    class="w-full sm:w-auto min-w-[200px] justify-center"
                >
                    Nueva Solicitud
                </x-filament::button>

                <x-filament::button
                    tag="a"
                    href="#"
                    icon="heroicon-m-user-circle"
                    color="gray"
                    size="lg"
                    class="w-full sm:w-auto min-w-[200px] justify-center"
                >
                    Mi Legajo
                </x-filament::button>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
