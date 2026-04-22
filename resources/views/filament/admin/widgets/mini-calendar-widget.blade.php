<x-filament-widgets::widget>
    <x-filament::section>
        @php
            \Carbon\Carbon::setLocale('es');
            $today = \Carbon\Carbon::now();
        @endphp
        
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-5">
                <!-- Icono circular tipo Avatar -->
                <div class="flex items-center justify-center w-14 h-14 bg-gray-100 dark:bg-gray-800 rounded-full text-gray-500 dark:text-gray-400">
                    <x-heroicon-o-calendar class="text-gray-500 dark:text-gray-400" style="width: 1.75rem; height: 1.75rem;"/>
                </div>
                
                <!-- Textos centrales -->
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Día de Hoy</h3>
                    <p class="text-xl font-medium text-gray-800 dark:text-gray-200 capitalize mt-1">
                        {{ $today->translatedFormat('l, d \d\e F \d\e Y') }}
                    </p>
                </div>
            </div>
            
            <!-- Botón / Tag derecho -->
            <div class="px-4 py-2 bg-[#1e293b] text-white text-sm font-medium rounded-lg flex items-center gap-2 shadow-sm">
                <x-heroicon-o-calendar-days class="text-white" style="width: 1rem; height: 1rem;"/>
                Hoy
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
