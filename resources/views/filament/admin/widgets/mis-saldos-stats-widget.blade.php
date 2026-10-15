<x-filament-widgets::widget class="fi-wi-stats-overview">
    @php
        $saldos        = $this->getDetalleSaldos();
        $totalDias     = $this->getTotalDias();
        $licencias     = $this->getDetalleLicencias();
        $totalLicencias = $this->getTotalLicencias();
        $anio          = date('Y');
    @endphp

    <div
        x-data="{ activeTab: null }"
        @toggle-saldo-detalle.window="activeTab = (activeTab === $event.detail.type) ? null : $event.detail.type"
    >
        {{-- Aquí se renderiza la grilla nativa de 3 tarjetas de Filament --}}
        {{ $this->content }}

        {{-- Panel Desplegable: Días Disponibles --}}
        <div
            x-show="activeTab === 'disponibles'"
            x-collapse
            x-cloak
            class="mt-4 rounded-xl ring-1 ring-success-200 dark:ring-success-800 bg-white dark:bg-gray-900 overflow-hidden shadow-sm"
        >
            <div class="px-5 py-3 bg-success-50 dark:bg-success-900/20 border-b border-success-100 dark:border-success-800 flex items-center justify-between">
                <span class="text-sm font-semibold text-success-700 dark:text-success-400">Desglose de Días Disponibles — {{ $anio }}</span>
                <button @click="activeTab = null" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <x-heroicon-m-x-mark class="w-5 h-5" />
                </button>
            </div>
            
            @if(count($saldos) > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50/50 dark:bg-gray-800/50">
                            <tr>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Licencia</th>
                                <th class="px-4 py-2.5 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Otorgados</th>
                                <th class="px-4 py-2.5 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Usados</th>
                                <th class="px-4 py-2.5 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Disponibles</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($saldos as $saldo)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-800 dark:text-gray-200">{{ $saldo['tipo'] }}</td>
                                    <td class="px-4 py-3 text-sm text-center text-gray-500 dark:text-gray-400">{{ $saldo['otorgados'] }}</td>
                                    <td class="px-4 py-3 text-sm text-center text-gray-500 dark:text-gray-400">{{ $saldo['usados'] }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold
                                            {{ $saldo['disponibles'] > 0 ? 'bg-success-100 text-success-700 dark:bg-success-900/40 dark:text-success-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' }}">
                                            {{ $saldo['disponibles'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                            <tr class="bg-success-50/30 dark:bg-success-900/10 font-bold border-t-2 border-success-100 dark:border-success-800">
                                <td class="px-4 py-3 text-sm text-success-700 dark:text-success-400">Total</td>
                                <td class="px-4 py-3 text-sm text-center text-gray-500 dark:text-gray-400">{{ array_sum(array_column($saldos, 'otorgados')) }}</td>
                                <td class="px-4 py-3 text-sm text-center text-gray-500 dark:text-gray-400">{{ array_sum(array_column($saldos, 'usados')) }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold bg-success-200 text-success-800 dark:bg-success-900/60 dark:text-success-300">
                                        {{ $totalDias }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @else
                <div class="py-6 text-center text-gray-500 dark:text-gray-400 text-sm">Sin saldos registrados.</div>
            @endif
        </div>

        {{-- Panel Desplegable: Licencias Aprobadas --}}
        <div
            x-show="activeTab === 'aprobadas'"
            x-collapse
            x-cloak
            class="mt-4 rounded-xl ring-1 ring-info-200 dark:ring-info-800 bg-white dark:bg-gray-900 overflow-hidden shadow-sm"
        >
            <div class="px-5 py-3 bg-info-50 dark:bg-info-900/20 border-b border-info-100 dark:border-info-800 flex items-center justify-between">
                <span class="text-sm font-semibold text-info-700 dark:text-info-400">Detalle de Licencias Aprobadas — {{ $anio }}</span>
                <button @click="activeTab = null" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <x-heroicon-m-x-mark class="w-5 h-5" />
                </button>
            </div>
            
            @if(count($licencias) > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50/50 dark:bg-gray-800/50">
                            <tr>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Licencia</th>
                                <th class="px-4 py-2.5 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Inicio</th>
                                <th class="px-4 py-2.5 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Fin</th>
                                <th class="px-4 py-2.5 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Días</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($licencias as $licencia)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-800 dark:text-gray-200">{{ $licencia['tipo'] }}</td>
                                    <td class="px-4 py-3 text-sm text-center text-gray-500 dark:text-gray-400">{{ $licencia['fecha_inicio'] }}</td>
                                    <td class="px-4 py-3 text-sm text-center text-gray-500 dark:text-gray-400">{{ $licencia['fecha_fin'] }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold bg-info-100 text-info-700 dark:bg-info-900/40 dark:text-info-300">
                                            {{ $licencia['dias'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="py-6 text-center text-gray-500 dark:text-gray-400 text-sm">Sin licencias aprobadas en {{ $anio }}.</div>
            @endif
        </div>
    </div>
</x-filament-widgets::widget>
