<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">

            {{-- Lado izquierdo: ícono + total --}}
            <div class="flex items-center gap-4">
                <div class="flex-shrink-0 rounded-full p-3 bg-success-50 dark:bg-success-900/20">
                    <x-heroicon-o-calendar-days class="w-8 h-8 text-success-600 dark:text-success-400" />
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Total Días Disponibles — {{ date('Y') }}
                    </p>
                    <p class="text-4xl font-extrabold text-success-600 dark:text-success-400 leading-none mt-1">
                        {{ $this->getTotalDiasDisponibles() }}
                        <span class="text-lg font-semibold text-gray-400 dark:text-gray-500 ml-1">días</span>
                    </p>
                </div>
            </div>

            {{-- Lado derecho: botón --}}
            <div>
                {{ ($this->verDetalleAction)() }}
            </div>
        </div>
    </x-filament::section>

    <x-filament-actions::modals />
</x-filament-widgets::widget>
