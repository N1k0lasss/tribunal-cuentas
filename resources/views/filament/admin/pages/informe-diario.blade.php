<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Encabezado: fecha, destinatario y observaciones generales --}}
        <x-filament::section icon="heroicon-o-clipboard-document-check">
            <x-slot name="heading">Datos del informe</x-slot>
            <x-slot name="description">
                Fecha y destinatario del informe diario. Las observaciones generales se incluyen en la exportación a PDF.
            </x-slot>

            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
                    <label for="fecha" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Fecha del informe
                    </label>
                    <x-filament::input.wrapper>
                        <x-filament::input type="date" id="fecha" wire:model.live="fecha" />
                    </x-filament::input.wrapper>
                </div>

                <div>
                    <label for="destinatario" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Destinatario formal
                    </label>
                    <x-filament::input.wrapper>
                        <x-filament::input
                            type="text"
                            id="destinatario"
                            wire:model.defer="destinatario"
                            placeholder="Secretaría General"
                            @disabled(! $this->esAdmin())
                        />
                    </x-filament::input.wrapper>
                </div>

                <div>
                    <label for="observacionesGenerales" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Observaciones generales del día
                    </label>
                    <textarea
                        id="observacionesGenerales"
                        wire:model.defer="observacionesGenerales"
                        @disabled(! $this->esAdmin())
                        rows="1"
                        placeholder="Notas para Secretaría General..."
                        class="fi-input block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm disabled:opacity-75 disabled:bg-gray-100 dark:disabled:bg-gray-800/80"
                    ></textarea>
                </div>
            </div>

            @if($ultimaSincronizacion)
                <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                    Última actualización: {{ $ultimaSincronizacion }}
                </p>
            @endif
        </x-filament::section>

        {{-- Resumen de novedades --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <x-filament::section>
                <div class="flex items-center gap-4">
                    <div class="rounded-lg bg-primary-100 p-3 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300">
                        <x-heroicon-o-users class="h-6 w-6" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold leading-none text-gray-900 dark:text-white">{{ count($detalles) }}</p>
                        <p class="mt-1.5 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Total plantilla</p>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Agentes registrados en el listado</p>
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="flex items-center gap-4">
                    <div class="rounded-lg bg-success-100 p-3 text-success-700 dark:bg-success-900/30 dark:text-success-300">
                        <x-heroicon-o-check-circle class="h-6 w-6" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold leading-none text-gray-900 dark:text-white">{{ $this->cantidades['presente'] }}</p>
                        <p class="mt-1.5 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Presentes</p>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Sin novedades registradas</p>
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="flex items-center gap-4">
                    <div class="rounded-lg bg-danger-100 p-3 text-danger-700 dark:bg-danger-900/30 dark:text-danger-300">
                        <x-heroicon-o-x-circle class="h-6 w-6" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold leading-none text-gray-900 dark:text-white">{{ $this->cantidades['ausente_sin_aviso'] }}</p>
                        <p class="mt-1.5 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Ausente sin aviso</p>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Falta sin justificación</p>
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="flex items-center gap-4">
                    <div class="rounded-lg bg-warning-100 p-3 text-warning-700 dark:bg-warning-900/30 dark:text-warning-300">
                        <x-heroicon-o-clock class="h-6 w-6" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold leading-none text-gray-900 dark:text-white">{{ $this->cantidades['tardanza'] }}</p>
                        <p class="mt-1.5 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Tardanza</p>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Ingreso con retraso</p>
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="flex items-center gap-4">
                    <div class="rounded-lg bg-info-100 p-3 text-info-700 dark:bg-info-900/30 dark:text-info-300">
                        <x-heroicon-o-document-text class="h-6 w-6" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold leading-none text-gray-900 dark:text-white">{{ $this->cantidades['licencia'] }}</p>
                        <p class="mt-1.5 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Licencia</p>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Con licencia aprobada</p>
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="flex items-center gap-4">
                    <div class="rounded-lg bg-warning-100 p-3 text-warning-700 dark:bg-warning-900/30 dark:text-warning-300">
                        <x-heroicon-o-exclamation-triangle class="h-6 w-6" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold leading-none text-gray-900 dark:text-white">{{ $this->cantidades['falta_justificada'] }}</p>
                        <p class="mt-1.5 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Falta justificada</p>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Justificada / autorizada</p>
                    </div>
                </div>
            </x-filament::section>
        </div>

        {{-- Listado de agentes --}}
        <x-filament::section>
            <div class="flex flex-wrap items-start justify-between gap-2">
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Listado de agentes y novedades del día</h3>
                    <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Asistencia y novedades registradas para la fecha seleccionada.</p>
                </div>
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    Destinado a: <strong>{{ $destinatario }}</strong>
                </span>
            </div>

            <div class="mt-4 overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                    <thead class="bg-gray-50 text-gray-700 dark:bg-gray-800 text-xs font-semibold uppercase tracking-wide dark:text-gray-200">
                        <tr>
                            <th class="px-4 py-3">Agente</th>
                            <th class="px-4 py-3">Área / Sector</th>
                            <th class="px-4 py-3">Tipo de novedad</th>
                            <th class="px-4 py-3">Horario / Tardanza</th>
                            <th class="px-4 py-3">Observaciones / Motivo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($detalles as $index => $det)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/60">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $det['agente_nombre'] }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">DNI: {{ $det['agente_dni'] }}</p>
                                </td>

                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                    {{ $det['area_nombre'] }}
                                </td>

                                <td class="px-4 py-3">
                                    @if($this->esAdmin())
                                        <select
                                            wire:model.live="detalles.{{ $index }}.tipo_novedad"
                                            class="fi-input rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:border-primary-500 focus:ring-primary-500"
                                        >
                                            <option value="presente">Presente</option>
                                            <option value="licencia">Licencia</option>
                                            <option value="falta_justificada">Falta justificada</option>
                                            <option value="ausente_sin_aviso">Ausente sin aviso</option>
                                            <option value="tardanza">Tardanza</option>
                                        </select>
                                    @else
                                        @php
                                            $tipo = $det['tipo_novedad'] ?? 'presente';
                                            $badgeClasses = [
                                                'presente' => 'bg-success-100 text-success-800 dark:bg-success-900/40 dark:text-success-300',
                                                'licencia' => 'bg-info-100 text-info-800 dark:bg-info-900/40 dark:text-info-300',
                                                'falta_justificada' => 'bg-warning-100 text-warning-800 dark:bg-warning-900/40 dark:text-warning-300',
                                                'ausente_sin_aviso' => 'bg-danger-100 text-danger-800 dark:bg-danger-900/40 dark:text-danger-300',
                                                'tardanza' => 'bg-warning-100 text-warning-800 dark:bg-warning-900/40 dark:text-warning-300',
                                            ][$tipo] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300';

                                            $labels = [
                                                'presente' => 'Presente',
                                                'licencia' => 'Licencia',
                                                'falta_justificada' => 'Falta justificada',
                                                'ausente_sin_aviso' => 'Ausente sin aviso',
                                                'tardanza' => 'Tardanza',
                                            ];
                                        @endphp
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badgeClasses }}">
                                            {{ $labels[$tipo] ?? ucfirst($tipo) }}
                                        </span>
                                    @endif

                                    @if(!empty($det['tipo_licencia_nombre']) && $det['tipo_novedad'] === 'licencia')
                                        <p class="mt-1 text-xs font-medium text-info-700 dark:text-info-400">
                                            {{ $det['tipo_licencia_nombre'] }}
                                        </p>
                                    @endif
                                </td>

                                <td class="px-4 py-3">
                                    @if(($detalles[$index]['tipo_novedad'] ?? '') === 'tardanza')
                                        @if($this->esAdmin())
                                            <div class="flex flex-wrap items-center gap-2">
                                                <input
                                                    type="text"
                                                    placeholder="08:30 hs"
                                                    wire:model.defer="detalles.{{ $index }}.hora_llegada"
                                                    class="fi-input w-28 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm"
                                                >
                                                <input
                                                    type="number"
                                                    placeholder="Min (ej. 15)"
                                                    wire:model.defer="detalles.{{ $index }}.minutos_tardanza"
                                                    class="fi-input w-28 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm"
                                                >
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                                {{ $det['hora_llegada'] ? $det['hora_llegada'].' hs' : '' }}
                                                @if(!empty($det['minutos_tardanza']))
                                                    <span class="font-medium">({{ $det['minutos_tardanza'] }} min)</span>
                                                @endif
                                                @if(empty($det['hora_llegada']) && empty($det['minutos_tardanza']))
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-sm text-gray-400">-</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3">
                                    @if($this->esAdmin())
                                        <input
                                            type="text"
                                            placeholder="Observaciones..."
                                            wire:model.defer="detalles.{{ $index }}.observaciones"
                                            class="fi-input w-full min-w-44 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm"
                                        >
                                    @else
                                        <span class="text-sm text-gray-700 dark:text-gray-300">
                                            {{ $det['observaciones'] ?: '-' }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No hay agentes registrados en el sistema.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex flex-wrap justify-end gap-2">
                @if($this->esAdmin())
                    <x-filament::button
                        color="info"
                        icon="heroicon-o-arrow-path"
                        wire:click="sincronizarAgentesYLicencias"
                        :disabled="$this->esFechaFutura()"
                    >
                        Sincronizar licencias
                    </x-filament::button>

                    <x-filament::button
                        color="success"
                        icon="heroicon-o-check"
                        wire:click="guardarInforme"
                        :disabled="$this->esFechaFutura()"
                    >
                        Guardar cambios
                    </x-filament::button>
                @endif

                <x-filament::button
                    color="warning"
                    icon="heroicon-o-arrow-down-tray"
                    wire:click="prepararExportacion"
                >
                    Exportar PDF (Sec. General)
                </x-filament::button>
            </div>
        </x-filament::section>
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
                <span class="text-gray-600 dark:text-gray-400">Total plantilla</span>
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
                <span class="text-gray-600 dark:text-gray-400">Falta justificada</span>
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
