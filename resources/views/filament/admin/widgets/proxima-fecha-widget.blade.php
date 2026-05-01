@php
    $nextHoliday = $this->getNextHoliday();
    if ($nextHoliday) {
        $days = now()->startOfDay()->diffInDays($nextHoliday->computed_proxima_fecha);
    }
@endphp

@if ($nextHoliday)
    <x-filament-widgets::widget>
        <x-filament::section class="relative overflow-hidden !p-0 h-full flex flex-col justify-center">
            {{-- Barra de acento lateral Navy --}}
            <div class="absolute inset-y-0 left-0 w-1.5 bg-[#1A365D] shadow-[0_0_10px_rgba(26,54,93,0.3)]"></div>

            <div class="flex flex-col md:flex-row items-center justify-between p-6 gap-6">
                <div class="flex items-center gap-5 w-full">
                    <!-- Textos Informativos -->
                    <div class="flex-1 min-w-0" style="padding-left: 1rem;">
                        <p class="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest leading-none mb-1">Próximo Feriado</p>
                        <h3 class="text-xl md:text-2xl font-bold text-gray-950 dark:text-white truncate" style="color: #1A365D;">
                            {{ $nextHoliday->nombre }}
                        </h3>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 capitalize mt-1 flex items-center gap-1.5">
                            <x-heroicon-o-calendar class="w-4 h-4" />
                            {{ $nextHoliday->computed_proxima_fecha->translatedFormat('l, d \d\e F \d\e Y') }}
                        </p>
                    </div>
                </div>

                <!-- Badge Contador Bordeaux -->
                <div class="flex items-center gap-2 px-4 py-2 rounded-full shadow-md flex-shrink-0" style="background-color: #6B1C5E; color: white;">
                    <x-heroicon-m-clock class="w-5 h-5 flex-shrink-0 text-white" />
                    <span class="font-bold text-sm whitespace-nowrap text-white">
                        Faltan {{ $days }} {{ $days === 1 ? 'día' : 'días' }}
                    </span>
                </div>
            </div>
        </x-filament::section>
    </x-filament-widgets::widget>
@else
    <x-filament-widgets::widget>
        <x-filament::section class="relative overflow-hidden flex flex-col items-center justify-center p-6 bg-gray-50/50 dark:bg-gray-900/50">
            <x-heroicon-o-calendar-days class="w-8 h-8 text-gray-400 mb-2" />
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                No hay feriados próximos programados en este momento.
            </p>
        </x-filament::section>
    </x-filament-widgets::widget>
@endif
