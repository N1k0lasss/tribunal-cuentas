<div class="space-y-4">

    {{-- Resumen total --}}
    <div class="flex items-center justify-between rounded-xl bg-info-50 dark:bg-info-900/20 px-5 py-3 ring-1 ring-info-200 dark:ring-info-800">
        <span class="text-sm font-semibold text-info-700 dark:text-info-300">Total aprobadas en {{ date('Y') }}</span>
        <span class="text-2xl font-extrabold text-info-600 dark:text-info-400">{{ $total }} licencias</span>
    </div>

    {{-- Tabla de detalle --}}
    @if(count($licencias) > 0)
        <div class="overflow-hidden rounded-xl ring-1 ring-gray-200 dark:ring-gray-700">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Tipo de Licencia
                        </th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Fecha Inicio
                        </th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Fecha Fin
                        </th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Días
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-900">
                    @foreach($licencias as $licencia)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                            <td class="px-4 py-3 text-sm font-medium text-gray-800 dark:text-gray-200">
                                {{ $licencia['tipo'] }}
                            </td>
                            <td class="px-4 py-3 text-sm text-center text-gray-600 dark:text-gray-400">
                                {{ $licencia['fecha_inicio'] }}
                            </td>
                            <td class="px-4 py-3 text-sm text-center text-gray-600 dark:text-gray-400">
                                {{ $licencia['fecha_fin'] }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center justify-center rounded-full px-3 py-0.5 text-sm font-bold bg-info-100 text-info-700 dark:bg-info-900/30 dark:text-info-400">
                                    {{ $licencia['dias'] }} día{{ $licencia['dias'] !== 1 ? 's' : '' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="flex flex-col items-center justify-center py-8 text-gray-400 dark:text-gray-500">
            <x-heroicon-o-inbox class="w-10 h-10 mb-2" />
            <p class="text-sm">No hay licencias aprobadas registradas para el año {{ date('Y') }}.</p>
        </div>
    @endif
</div>
