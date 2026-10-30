<x-filament-panels::page>
    <style>
        .diario-metric-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1rem;
        }
        @media (min-width: 640px) {
            .diario-metric-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (min-width: 1024px) {
            .diario-metric-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }
        .diario-metric-card {
            position: relative;
            padding: 1.25rem 1.5rem;
            border-radius: 1rem;
            background: white;
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px -1px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 110px;
        }
        .dark .diario-metric-card {
            background: #0f172a;
            border-color: #1e293b;
        }
        .diario-metric-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
        }
        .diario-metric-card--navy::before { background: linear-gradient(90deg, #1A365D, #2B4C7E); }
        .diario-metric-card--emerald::before { background: linear-gradient(90deg, #059669, #10b981); }
        .diario-metric-card--rose::before { background: linear-gradient(90deg, #e11d48, #f43f5e); }
        .diario-metric-card--purple::before { background: linear-gradient(90deg, #6B1C5E, #8A2C7E); }
        .diario-metric-card--blue::before { background: linear-gradient(90deg, #2563eb, #3b82f6); }
        .diario-metric-card--amber::before { background: linear-gradient(90deg, #d97706, #f59e0b); }
        .diario-metric-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.75rem;
        }
        .diario-metric-title {
            font-size: 0.875rem;
            font-weight: 500;
            color: #64748b;
        }
        .dark .diario-metric-title { color: #94a3b8; }
        .diario-metric-icon {
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .diario-metric-card--navy .diario-metric-icon { background: rgba(26, 54, 93, 0.1); color: #1A365D; }
        .diario-metric-card--emerald .diario-metric-icon { background: rgba(5, 150, 105, 0.1); color: #059669; }
        .diario-metric-card--rose .diario-metric-icon { background: rgba(225, 29, 72, 0.1); color: #e11d48; }
        .diario-metric-card--purple .diario-metric-icon { background: rgba(107, 28, 94, 0.1); color: #6B1C5E; }
        .diario-metric-card--blue .diario-metric-icon { background: rgba(37, 99, 235, 0.1); color: #2563eb; }
        .diario-metric-card--amber .diario-metric-icon { background: rgba(217, 119, 6, 0.1); color: #d97706; }
        .dark .diario-metric-card--navy .diario-metric-icon { background: rgba(96, 165, 250, 0.15); color: #60a5fa; }
        .dark .diario-metric-card--emerald .diario-metric-icon { background: rgba(16, 185, 129, 0.15); color: #34d399; }
        .dark .diario-metric-card--rose .diario-metric-icon { background: rgba(244, 63, 94, 0.15); color: #fb7185; }
        .dark .diario-metric-card--purple .diario-metric-icon { background: rgba(244, 114, 182, 0.15); color: #f472b6; }
        .dark .diario-metric-card--blue .diario-metric-icon { background: rgba(59, 130, 246, 0.15); color: #60a5fa; }
        .dark .diario-metric-card--amber .diario-metric-icon { background: rgba(251, 191, 36, 0.15); color: #fbbf24; }
        .diario-metric-value {
            font-size: 1.875rem;
            font-weight: 700;
            color: #1e293b;
            line-height: 1;
        }
        .dark .diario-metric-value { color: #f8fafc; }
        .diario-metric-desc {
            font-size: 0.75rem;
            color: #94a3b8;
            margin-top: 0.25rem;
        }
        .dark .diario-metric-desc { color: #64748b; }
    </style>
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

                <div class="md:col-span-1">
                    <label for="observacionesGenerales" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        📝 Observaciones Generales del Día
                    </label>
                    <textarea 
                        id="observacionesGenerales" 
                        wire:model.defer="observacionesGenerales" 
                        @disabled(! $this->esAdmin())
                        rows="3"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm disabled:opacity-75 disabled:bg-gray-100 dark:disabled:bg-gray-800/80"
                        placeholder="Notas para Secretaría General..."
                    ></textarea>
                </div>

            </div>
        </div>

        <!-- Summary Cards Grid -->
        <div class="diario-metric-grid">
            
            <div class="diario-metric-card diario-metric-card--navy">
                <div>
                    <div class="diario-metric-header">
                        <span class="diario-metric-title">Total Plantilla</span>
                        <div class="diario-metric-icon">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                    </div>
                    <div class="diario-metric-value">{{ count($detalles) }}</div>
                </div>
                <div class="diario-metric-desc">Agentes registrados en el listado</div>
            </div>

            <div class="diario-metric-card diario-metric-card--emerald">
                <div>
                    <div class="diario-metric-header">
                        <span class="diario-metric-title">Presentes</span>
                        <div class="diario-metric-icon">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                    </div>
                    <div class="diario-metric-value">{{ $this->cantidades['presente'] }}</div>
                </div>
                <div class="diario-metric-desc">Sin novedades registradas</div>
            </div>

            <div class="diario-metric-card diario-metric-card--rose">
                <div>
                    <div class="diario-metric-header">
                        <span class="diario-metric-title">Ausente sin aviso</span>
                        <div class="diario-metric-icon">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                    </div>
                    <div class="diario-metric-value">{{ $this->cantidades['ausente_sin_aviso'] }}</div>
                </div>
                <div class="diario-metric-desc">Falta sin justificación</div>
            </div>

            <div class="diario-metric-card diario-metric-card--purple">
                <div>
                    <div class="diario-metric-header">
                        <span class="diario-metric-title">Tardanza</span>
                        <div class="diario-metric-icon">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                    </div>
                    <div class="diario-metric-value">{{ $this->cantidades['tardanza'] }}</div>
                </div>
                <div class="diario-metric-desc">Ingreso con retraso</div>
            </div>

            <div class="diario-metric-card diario-metric-card--blue">
                <div>
                    <div class="diario-metric-header">
                        <span class="diario-metric-title">Licencia</span>
                        <div class="diario-metric-icon">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                    </div>
                    <div class="diario-metric-value">{{ $this->cantidades['licencia'] }}</div>
                </div>
                <div class="diario-metric-desc">Con licencia aprobada</div>
            </div>

            <div class="diario-metric-card diario-metric-card--amber">
                <div>
                    <div class="diario-metric-header">
                        <span class="diario-metric-title">Falta justificada</span>
                        <div class="diario-metric-icon">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                    </div>
                    <div class="diario-metric-value">{{ $this->cantidades['falta_justificada'] }}</div>
                </div>
                <div class="diario-metric-desc">Justificada / autorizada</div>
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

            <div class="p-4 bg-gray-50 dark:bg-gray-800/60 border-t border-gray-200 dark:border-gray-700 flex flex-wrap justify-end gap-3">
                @if($this->esAdmin())
                    <x-filament::button 
                        color="info" 
                        icon="heroicon-o-arrow-path" 
                        wire:click="sincronizarAgentesYLicencias"
                        class="bg-sky-500 hover:bg-sky-600 focus-visible:ring-sky-500"
                        :disabled="$this->esFechaFutura()"
                    >
                        Sincronizar Licencias
                    </x-filament::button>

                    <x-filament::button 
                        color="success" 
                        icon="heroicon-o-check" 
                        wire:click="guardarInforme"
                        class="bg-emerald-500 hover:bg-emerald-600 focus-visible:ring-emerald-500"
                        :disabled="$this->esFechaFutura()"
                    >
                        Guardar Cambios
                    </x-filament::button>
                @endif

                <x-filament::button 
                    color="warning" 
                    icon="heroicon-o-arrow-down-tray" 
                    wire:click="prepararExportacion"
                    class="shadow-sm"
                >
                    Exportar PDF (Sec. General)
                </x-filament::button>
            </div>
        </div>

    </div>

    <x-filament::modal id="confirmar-exportar-pdf" width="md" :visible="$mostrarModalExportar" wire:model.live="mostrarModalExportar">
        <x-slot name="heading">Confirmar exportación a PDF</x-slot>
        <x-slot name="description">Se generará el informe con los datos actuales para la fecha seleccionada.</x-slot>

        <div class="space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Fecha</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ \Illuminate\Support\Carbon::parse($fecha)->format('d/m/Y') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Total Plantilla</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ count($detalles) }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Presentes</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ $resumenExportacion['presente'] ?? 0 }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Licencias</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ $resumenExportacion['licencia'] ?? 0 }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Falta Justificada</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ $resumenExportacion['falta_justificada'] ?? 0 }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Ausente sin aviso</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ $resumenExportacion['ausente_sin_aviso'] ?? 0 }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Tardanza</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ $resumenExportacion['tardanza'] ?? 0 }}</span>
            </div>
        </div>

        <x-slot name="footerActions">
            <x-filament::button color="gray" wire:click="$set('mostrarModalExportar', false)">Cancelar</x-filament::button>
            <x-filament::button color="warning" icon="heroicon-o-arrow-down-tray" wire:click="confirmarExportarPdf">Descargar PDF</x-filament::button>
        </x-slot>
    </x-filament::modal>
</x-filament-panels::page>
