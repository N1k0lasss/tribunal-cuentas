@php
    $nextHoliday = $this->getNextHoliday();
    if ($nextHoliday) {
        $days = now()->startOfDay()->diffInDays($nextHoliday->computed_proxima_fecha);
    }
@endphp

@if ($nextHoliday)
    <x-filament-widgets::widget>
        <x-filament::section class="relative overflow-hidden !p-0">
            {{-- Barra de acento lateral --}}
            <div class="absolute inset-y-0 left-0 w-1.5 bg-primary-500 shadow-[0_0_10px_rgba(var(--primary-500),0.3)]"></div>

            <div class="flex flex-col md:flex-row items-center justify-between p-5 gap-4">
                <div class="flex items-center gap-5 w-full">
                    <!-- Textos Informativos -->
                    <div class="flex-1 min-w-0" style="padding-left: 1rem;">
                        <p class="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest leading-none">Próximo Feriado</p>
                        <h3 class="text-xl md:text-2xl font-extrabold text-gray-950 dark:text-white mt-1 truncate">
                            {{ $nextHoliday->nombre }}
                        </h3>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 capitalize mt-0.5">
                            {{ $nextHoliday->computed_proxima_fecha->translatedFormat('l, d \d\e F \d\e Y') }}
                        </p>
                    </div>

                </div>

                <!-- Badge Contador Azul Claro (Píldora) -->
                <div style="display: flex !important; flex-direction: row !important; align-items: center !important; gap: 0.5rem !important; padding: 0.4rem 1rem !important; background-color: #00008B !important; color: white !important; border-radius: 9999px !important; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2) !important; flex-shrink: 0 !important;">
                    <x-heroicon-m-calendar-days style="width: 20px !important; height: 20px !important; flex-shrink: 0 !important; color: white !important;" />
                    <span style="font-weight: 700 !important; font-size: 0.875rem !important; white-space: nowrap !important; color: white !important;">
                        {{ $days }} {{ $days === 1 ? 'día' : 'días' }}
                    </span>
                </div>
            </div>
        </x-filament::section>
    </x-filament-widgets::widget>
@endif
