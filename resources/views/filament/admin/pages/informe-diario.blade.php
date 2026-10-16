<x-filament-panels::page>
    <div class="space-y-6">
        
        <!-- Header Controls Card -->
        <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
                
                <div>
                    <label for="fecha" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        📅 Fecha del Informe Diario
                    </label>
                    <input 
                        type="date" 
                        id="fecha" 
                        wire:model.live="fecha" 
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                    >
                </div>

                <div>
                    <label for="destinatario" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        🏛️ Destinatario Formal
                    </label>
                    <input 
                        type="text" 
                        id="destinatario" 
                        wire:model.defer="destinatario" 
                        @disabled(! $this->esAdmin())
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm disabled:opacity-75 disabled:bg-gray-100 dark:disabled:bg-gray-800/80"
                        placeholder="Secretaría General"
                    >
                </div>

                <div>
                    <label for="observacionesGenerales" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        📝 Observaciones Generales del Día
                    </label>
                    <input 
                        type="text" 
                        id="observacionesGenerales" 
                        wire:model.defer="observacionesGenerales" 
                        @disabled(! $this->esAdmin())
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm disabled:opacity-75 disabled:bg-gray-100 dark:disabled:bg-gray-800/80"
                        placeholder="Notas para Secretaría General..."
                    >
                </div>

            </div>
        </div>

        <!-- Summary Cards Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            
            <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 text-center">
                <div class="text-xs uppercase font-bold text-gray-500 dark:text-gray-400">Total Plantilla</div>
                <div class="text-2xl font-bold text-gray-800 dark:text-white mt-1">{{ count($detalles) }}</div>
            </div>

            <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 rounded-xl border border-emerald-200 dark:border-emerald-800 text-center">
                <div class="text-xs uppercase font-bold text-emerald-700 dark:text-emerald-400">Presentes</div>
                <div class="text-2xl font-bold text-emerald-800 dark:text-emerald-300 mt-1">{{ $this->cantidades['presente'] }}</div>
            </div>

            <div class="p-4 bg-blue-50 dark:bg-blue-950/40 rounded-xl border border-blue-200 dark:border-blue-800 text-center">
                <div class="text-xs uppercase font-bold text-blue-700 dark:text-blue-400">Licencias</div>
                <div class="text-2xl font-bold text-blue-800 dark:text-blue-300 mt-1">{{ $this->cantidades['licencia'] }}</div>
            </div>

            <div class="p-4 bg-amber-50 dark:bg-amber-950/40 rounded-xl border border-amber-200 dark:border-amber-800 text-center">
                <div class="text-xs uppercase font-bold text-amber-700 dark:text-amber-400">Faltas Justificadas</div>
                <div class="text-2xl font-bold text-amber-800 dark:text-amber-300 mt-1">{{ $this->cantidades['falta_justificada'] }}</div>
            </div>

            <div class="p-4 bg-rose-50 dark:bg-rose-950/40 rounded-xl border border-rose-200 dark:border-rose-800 text-center">
                <div class="text-xs uppercase font-bold text-rose-700 dark:text-rose-400">Ausentes s/Aviso</div>
                <div class="text-2xl font-bold text-rose-800 dark:text-rose-300 mt-1">{{ $this->cantidades['ausente_sin_aviso'] }}</div>
            </div>

            <div class="p-4 bg-purple-50 dark:bg-purple-950/40 rounded-xl border border-purple-200 dark:border-purple-800 text-center">
                <div class="text-xs uppercase font-bold text-purple-700 dark:text-purple-400">Tardanzas</div>
                <div class="text-2xl font-bold text-purple-800 dark:text-purple-300 mt-1">{{ $this->cantidades['tardanza'] }}</div>
            </div>

        </div>

        <!-- Agent Details Table -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-4 bg-gray-50 dark:bg-gray-800/60 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h3 class="font-semibold text-gray-800 dark:text-white text-base">
                    📋 Listado de Agentes y Novedades del Día
                </h3>
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    Destinado a: <strong>Secretaría General</strong>
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                    <thead class="bg-gray-100 dark:bg-gray-700/50 text-gray-700 dark:text-gray-200 uppercase text-xs font-semibold">
                        <tr>
                            <th class="px-4 py-3">Agente</th>
                            <th class="px-4 py-3">Área / Sector</th>
                            <th class="px-4 py-3">Tipo de Novedad</th>
                            <th class="px-4 py-3">Horario / Tardanza</th>
                            <th class="px-4 py-3">Observaciones / Motivo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($detalles as $index => $det)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-700/40 transition-colors">
                                
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900 dark:text-white">
                                        {{ $det['agente_nombre'] }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        DNI: {{ $det['agente_dni'] }}
                                    </div>
                                </td>

                                <td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-400">
                                    {{ $det['area_nombre'] }}
                                </td>

                                <td class="px-4 py-3">
                                    @if($this->esAdmin())
                                        <select 
                                            wire:model.live="detalles.{{ $index }}.tipo_novedad"
                                            class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-xs py-1.5 focus:ring-primary-500 focus:border-primary-500"
                                        >
                                            <option value="presente">✅ Presente</option>
                                            <option value="licencia">🔷 Licencia</option>
                                            <option value="falta_justificada">⚠️ Falta Justificada</option>
                                            <option value="ausente_sin_aviso">❌ Ausente sin aviso</option>
                                            <option value="tardanza">⏱️ Tardanza</option>
                                        </select>
                                    @else
                                        @php
                                            $tipo = $det['tipo_novedad'] ?? 'presente';
                                            $badgeClasses = [
                                                'presente' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300',
                                                'licencia' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300',
                                                'falta_justificada' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300',
                                                'ausente_sin_aviso' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/50 dark:text-rose-300',
                                                'tardanza' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-300',
                                            ][$tipo] ?? 'bg-gray-100 text-gray-800';

                                            $labels = [
                                                'presente' => '✅ Presente',
                                                'licencia' => '🔷 Licencia',
                                                'falta_justificada' => '⚠️ Falta Justificada',
                                                'ausente_sin_aviso' => '❌ Ausente sin aviso',
                                                'tardanza' => '⏱️ Tardanza',
                                            ];
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeClasses }}">
                                            {{ $labels[$tipo] ?? ucfirst($tipo) }}
                                        </span>
                                    @endif

                                    @if(!empty($det['tipo_licencia_nombre']) && $det['tipo_novedad'] === 'licencia')
                                        <div class="text-[11px] text-blue-600 dark:text-blue-400 font-medium mt-1">
                                            {{ $det['tipo_licencia_nombre'] }}
                                        </div>
                                    @endif
                                </td>

                                <td class="px-4 py-3">
                                    @if(($detalles[$index]['tipo_novedad'] ?? '') === 'tardanza')
                                        @if($this->esAdmin())
                                            <div class="flex items-center space-x-2">
                                                <input 
                                                    type="text" 
                                                    placeholder="08:30 hs" 
                                                    wire:model.defer="detalles.{{ $index }}.hora_llegada"
                                                    class="w-24 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-xs py-1 px-2"
                                                >
                                                <input 
                                                    type="number" 
                                                    placeholder="Min (ej. 15)" 
                                                    wire:model.defer="detalles.{{ $index }}.minutos_tardanza"
                                                    class="w-24 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-xs py-1 px-2"
                                                >
                                            </div>
                                        @else
                                            <div class="text-xs text-gray-700 dark:text-gray-300">
                                                {{ $det['hora_llegada'] ? $det['hora_llegada'].' hs' : '' }}
                                                @if(!empty($det['minutos_tardanza']))
                                                    <span class="font-medium text-purple-600 dark:text-purple-400">({{ $det['minutos_tardanza'] }} min)</span>
                                                @endif
                                                @if(empty($det['hora_llegada']) && empty($det['minutos_tardanza']))
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-gray-400 text-xs">-</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3">
                                    @if($this->esAdmin())
                                        <input 
                                            type="text" 
                                            placeholder="Observaciones..." 
                                            wire:model.defer="detalles.{{ $index }}.observaciones"
                                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-xs py-1 px-2"
                                        >
                                    @else
                                        <span class="text-xs text-gray-700 dark:text-gray-300">
                                            {{ $det['observaciones'] ?: '-' }}
                                        </span>
                                    @endif
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-6 text-gray-500 dark:text-gray-400">
                                    No hay agentes registrados en el sistema.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 bg-gray-50 dark:bg-gray-800/60 border-t border-gray-200 dark:border-gray-700 flex justify-end space-x-3">
                @if($this->esAdmin())
                    <x-filament::button 
                        color="info" 
                        icon="heroicon-o-arrow-path" 
                        wire:click="sincronizarAgentesYLicencias"
                    >
                        Sincronizar Licencias
                    </x-filament::button>

                    <x-filament::button 
                        color="success" 
                        icon="heroicon-o-check" 
                        wire:click="guardarInforme"
                    >
                        Guardar Informe Diario
                    </x-filament::button>
                @endif

                <x-filament::button 
                    color="primary" 
                    icon="heroicon-o-arrow-down-tray" 
                    wire:click="exportarPdf"
                >
                    Exportar PDF (Sec. General)
                </x-filament::button>
            </div>
        </div>

    </div>
</x-filament-panels::page>
