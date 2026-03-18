<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center gap-4">
            <x-filament::avatar
                size="w-16 h-16"
                src="{{ filament()->getUserAvatarUrl(auth()->user()) }}"
            />
            <div>
                <h2 class="text-xl font-bold tracking-tight text-gray-950 dark:text-white">
                    {{ $this->agente->apellido ?? '...' }}, {{ $this->agente->nombre ?? '...' }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Nº de Legajo: <span class="font-medium text-gray-950 dark:text-white">{{ $this->agente->id ?? 'No asignado' }}</span>
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    {{ $this->agente->cargo ?? 'Sin Cargo' }} | {{ optional($this->agente->area)->nombre ?? 'Sin Área' }}
                </p>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
