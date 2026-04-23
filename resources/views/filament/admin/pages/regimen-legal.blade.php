<x-filament-panels::page>
    <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 mb-6 flex flex-col sm:flex-row items-center sm:items-start gap-4 p-6">
        <div class="flex-shrink-0 bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400 p-4 rounded-xl ring-1 ring-primary-600/10 dark:ring-primary-500/20">
            <x-heroicon-o-scale class="w-8 h-8" />
        </div>
        <div class="flex-1 w-full">
            <p class="text-xs font-semibold uppercase tracking-widest text-primary-600 dark:text-primary-400 mb-1">Consulta legal</p>
            <h2 class="text-xl font-bold text-gray-950 dark:text-white mb-2">Régimen legal oficial en modo solo lectura</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 max-w-3xl leading-relaxed">
                Esta vista permite consultar las licencias vigentes con su contenido estructurado.
                No habilita modificaciones ni eliminaciones, garantizando la integridad de la norma.
            </p>
            <div class="flex flex-wrap gap-2 items-center">
                <span class="inline-flex items-center rounded-md bg-gray-50 px-2.5 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10 dark:bg-white/5 dark:text-gray-400 dark:ring-white/10">
                    Modo lectura
                </span>
                @if (isset($tipoContratoAgente) && $tipoContratoAgente)
                    <span class="inline-flex items-center rounded-md bg-primary-50 px-2.5 py-1 text-xs font-medium text-primary-700 ring-1 ring-inset ring-primary-700/10 dark:bg-primary-500/10 dark:text-primary-400 dark:ring-primary-500/20">
                        Contrato: {{ $tipoContratoAgente }}
                    </span>
                @endif
                
                @if (isset($pdfUrl) && $pdfUrl)
                    <a href="{{ $pdfUrl }}" target="_blank" rel="noopener noreferrer" class="ml-auto text-sm font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300 transition-colors flex items-center gap-1.5 focus:outline-none focus:underline">
                        <x-heroicon-m-document-text class="w-4 h-4" />
                        Ver documento oficial
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-8">
        <div>
            <label for="buscar" class="block text-sm font-medium leading-6 text-gray-950 dark:text-white mb-1.5">Buscar licencia</label>
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <x-heroicon-m-magnifying-glass class="h-5 w-5 text-gray-400" />
                </div>
                <input
                    id="buscar"
                    type="text"
                    placeholder="Ej: ART 6, Maternidad, Receso..."
                    class="block w-full rounded-xl border-0 py-2 pl-10 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6 dark:bg-white/5 dark:text-white dark:ring-white/20 dark:focus:ring-primary-500 transition-shadow"
                    wire:model.live.debounce.300ms="buscar"
                >
            </div>
        </div>

        <div>
            <label for="categoria" class="block text-sm font-medium leading-6 text-gray-950 dark:text-white mb-1.5">Filtrar por categoría</label>
            <select
                id="categoria"
                class="block w-full rounded-xl border-0 py-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6 dark:bg-gray-900 dark:text-white dark:ring-white/20 dark:focus:ring-primary-500 transition-shadow"
                wire:model.live="categoria"
            >
                <option value="" class="bg-white dark:bg-gray-900">Todas las categorías</option>
                @foreach ($categorias ?? [] as $item)
                    <option value="{{ $item }}" class="bg-white dark:bg-gray-900">{{ $item }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 lg:items-start gap-6">
        @forelse ($regimenes ?? [] as $regimen)
            <article class="flex flex-col rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden hover:shadow-md transition duration-200">
                <div class="flex-1 p-6">
                    <div class="flex items-start justify-between gap-4 mb-5">
                        <div>
                            <p class="text-xs font-bold text-primary-600 dark:text-primary-400 uppercase tracking-widest mb-1.5">
                                {{ $regimen['categoria'] ?? 'Sin categoría' }}
                            </p>
                            <h3 class="text-lg font-bold text-gray-950 dark:text-white leading-tight cursor-default">
                                {{ $regimen['nombre'] ?? 'Licencia sin nombre' }}
                            </h3>
                        </div>
                        <span class="inline-flex flex-shrink-0 items-center justify-center rounded-md bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-800 dark:bg-white/10 dark:text-gray-300 shadow-sm border border-gray-200 dark:border-white/5 whitespace-nowrap">
                            {{ !empty($regimen['articulo_legal']) ? $regimen['articulo_legal'] : 'Sin artículo' }}
                        </span>
                    </div>

                    <div class="mt-4 border-t border-gray-100 dark:border-white/5 pt-4">
                        <dl class="divide-y divide-gray-100 dark:divide-white/5">
                            @if(isset($regimen['contenido']) && is_array($regimen['contenido']))
                                @foreach ($regimen['contenido'] as $linea)
                                    <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $linea['label'] ?? '' }}</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:col-span-2 sm:mt-0 font-medium">{{ $linea['value'] ?? '' }}</dd>
                                    </div>
                                @endforeach
                            @endif
                        </dl>
                    </div>
                </div>

                <div class="bg-gray-50 dark:bg-white/5 px-6 py-3.5 border-t border-gray-100 dark:border-white/5">
                    @if (!empty($regimen['aplica_al_agente']))
                        <div class="flex items-center gap-2 text-sm font-semibold text-success-600 dark:text-success-400">
                            <x-heroicon-m-check-circle class="w-5 h-5 flex-shrink-0" />
                            <span>Aplica a tu modalidad de contratación</span>
                        </div>
                    @else
                        <div class="flex items-center gap-2 text-sm font-semibold text-danger-600 dark:text-danger-400">
                            <x-heroicon-m-x-circle class="w-5 h-5 flex-shrink-0" />
                            <span>No aplica a tu modalidad de contratación</span>
                        </div>
                    @endif
                </div>
            </article>
        @empty
            <div class="col-span-full">
                <div class="flex flex-col items-center justify-center p-12 text-center rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 mt-4">
                    <div class="rounded-full bg-gray-50 dark:bg-white/5 p-4 mb-4 ring-1 ring-gray-950/5 dark:ring-white/10">
                        <x-heroicon-o-document-magnifying-glass class="w-10 h-10 text-gray-400 dark:text-gray-500" />
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">No se encontraron regímenes</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                        No pudimos encontrar ninguna normativa que coincida con tu búsqueda actual. Prueba eliminar los filtros o buscar con otros términos.
                    </p>
                    <button type="button" wire:click="$set('buscar', '')" class="mt-6 inline-flex items-center justify-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 transition-colors">
                        Limpiar búsqueda
                    </button>
                </div>
            </div>
        @endforelse
    </div>
</x-filament-panels::page>
