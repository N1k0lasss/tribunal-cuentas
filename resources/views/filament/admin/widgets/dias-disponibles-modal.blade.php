<div class="space-y-4">

    {{-- Resumen total --}}
    <div class="flex items-center justify-between rounded-xl bg-success-50 dark:bg-success-900/20 px-5 py-3 ring-1 ring-success-200 dark:ring-success-800">
        <span class="text-sm font-semibold text-success-700 dark:text-success-300">Total disponible</span>
        <span class="text-2xl font-extrabold text-success-600 dark:text-success-400">{{ $total }} días</span>
    </div>

    {{-- Tabla de detalle --}}
    @if(count($saldos) > 0)
        <div class="overflow-hidden rounded-xl ring-1 ring-gray-200 dark:ring-gray-700">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Tipo de Licencia
                        </th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Otorgados
                        </th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Usados
                        </th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Disponibles
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-900">
                    @foreach($saldos as $saldo)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                            <td class="px-4 py-3 text-sm font-medium text-gray-800 dark:text-gray-200">
                                {{ $saldo['tipo'] }}
                            </td>
                            <td class="px-4 py-3 text-sm text-center text-gray-600 dark:text-gray-400">
                                {{ $saldo['otorgados'] }}
                            </td>
                            <td class="px-4 py-3 text-sm text-center text-gray-600 dark:text-gray-400">
                                {{ $saldo['usados'] }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center justify-center rounded-full px-3 py-0.5 text-sm font-bold
                                    {{ $saldo['disponibles'] > 0
                                        ? 'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400'
                                        : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400' }}">
                                    {{ $saldo['disponibles'] }} días
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
            <p class="text-sm">No hay saldos registrados para el año {{ date('Y') }}.</p>
        </div>
    @endif
</div>
