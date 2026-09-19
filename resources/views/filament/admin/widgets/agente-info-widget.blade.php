<x-filament-widgets::widget>
    <x-filament::section class="overflow-hidden !p-0">
        <div class="relative flex flex-col items-center justify-center p-6 md:p-8 bg-white dark:bg-gray-900 w-full">
            {{-- Fondo decorativo sutil (Navy) a la izquierda (Opcional, lo dejamos como detalle) --}}
            <div class="absolute inset-y-0 left-0 w-24 bg-[#1A365D] opacity-10" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 16px 16px; clip-path: polygon(0 0, 100% 0, 0% 100%);"></div>

            {{-- Fila Superior: Información del Agente --}}
            <div class="relative z-10 w-full flex flex-col items-center justify-center">
                {{-- Nombre y Avatar en la misma línea --}}
                <div class="flex items-center justify-center gap-4 pb-3">
                    <div class="flex-shrink-0 -mt-2">
                        <div class="p-1 bg-white dark:bg-gray-800 rounded-full shadow-sm ring-2 ring-[#1A365D]/20 flex items-center justify-center">
                            <div class="w-12 h-12 rounded-full overflow-hidden flex items-center justify-center text-lg font-medium">
                                <x-filament::avatar
                                    class="w-full h-full"
                                    src="{{ filament()->getUserAvatarUrl(auth()->user()) }}"
                                />
                            </div>
                        </div>
                    </div>
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-950 dark:text-white tracking-tight truncate">
                        {{ $this->agente->apellido ?? '...' }}, {{ $this->agente->nombre ?? '...' }}
                    </h2>
                </div>
                
                {{-- Badges --}}
                <div class="mt-4 flex items-center justify-center gap-4 flex-wrap">
                    <x-filament::badge color="info" size="lg" class="px-4 py-1.5 text-sm">
                        Nº de Legajo: {{ $this->agente->legajo ?? 'N/A' }}
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
                <style>
                    #btn-nueva-solicitud {
                        background-color: #1A365D !important;
                        color: white !important;
                        box-shadow: 0 4px 6px -1px rgba(26,54,93,0.3) !important;
                        border: none !important;
                    }
                    #btn-nueva-solicitud * {
                        color: white !important;
                    }
                    #btn-nueva-solicitud:hover {
                        background-color: #132743 !important;
                    }
                </style>
                <x-filament::button
                    id="btn-nueva-solicitud"
                    tag="a"
                    href="{{ \App\Filament\Admin\Resources\Solicituds\SolicitudResource::getUrl('create') }}"
                    icon="heroicon-m-plus"
                    size="xl"
                    class="w-full sm:w-auto min-w-[220px] justify-center"
                >
                    <span class="text-lg md:text-xl font-bold">Nueva Solicitud</span>
                </x-filament::button>

                <x-filament::button
                    tag="a"
                    href="#"
                    icon="heroicon-m-user-circle"
                    color="gray"
                    size="xl"
                    class="w-full sm:w-auto min-w-[220px] justify-center"
                >
                    <span class="text-lg md:text-xl font-bold">Mi Legajo</span>
                </x-filament::button>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
