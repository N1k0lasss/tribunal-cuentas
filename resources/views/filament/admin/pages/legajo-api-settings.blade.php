<x-filament-panels::page>
    @php
        $status = $this->getCredentialStatus();
        $vigenciaRestante = $status['expires_at']
            ? max(0, min(100, (int) round((($status['expires_at']->getTimestamp() - now()->getTimestamp()) / 86400) * 100)))
            : null;
    @endphp

    <div class="mx-auto w-full max-w-6xl space-y-6">
        <section class="relative isolate overflow-hidden rounded-2xl bg-[#1A365D] px-6 py-7 text-white shadow-lg sm:px-8 sm:py-8">
            <div class="absolute -right-12 -top-20 -z-10 h-64 w-64 rounded-full bg-white/[0.04]"></div>
            <div class="absolute -bottom-28 right-36 -z-10 h-56 w-56 rounded-full bg-white/[0.04]"></div>

            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-white/10 ring-1 ring-inset ring-white/15">
                        <x-filament::icon icon="heroicon-o-shield-check" class="h-6 w-6 text-white" />
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-200">Integraciones</p>
                        <h2 class="mt-1 text-2xl font-bold tracking-tight">API de Legajos</h2>
                    </div>
                </div>
                <p class="max-w-lg text-sm leading-6 text-blue-100 sm:text-right">
                    Administrá el acceso seguro a los datos del personal. Legajos renueva el token cada 24 horas.
                </p>
            </div>
        </section>

        <div class="grid items-start gap-6 lg:grid-cols-[0.9fr_1.1fr]">
            <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
                <div class="flex items-start justify-between gap-4 border-b border-gray-100 px-5 py-5 dark:border-white/10 sm:px-6">
                    <div class="flex items-start gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary-50 text-primary-700 dark:bg-primary-400/10 dark:text-primary-300">
                            <x-filament::icon icon="heroicon-o-key" class="h-5 w-5" />
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-950 dark:text-white">Estado de conexión</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Credencial de acceso a Legajos</p>
                        </div>
                    </div>

                    @if ($status['configured'])
                        @if ($status['expired'])
                            <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-danger-50 px-2.5 py-1 text-xs font-semibold text-danger-700 dark:bg-danger-400/10 dark:text-danger-300">
                                <span class="h-1.5 w-1.5 rounded-full bg-danger-500"></span>
                                Vencido
                            </span>
                        @elseif ($status['source'] === 'panel')
                            <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-success-50 px-2.5 py-1 text-xs font-semibold text-success-700 dark:bg-success-400/10 dark:text-success-300">
                                <span class="h-1.5 w-1.5 rounded-full bg-success-500"></span>
                                Configurado
                            </span>
                        @else
                            <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-warning-50 px-2.5 py-1 text-xs font-semibold text-warning-700 dark:bg-warning-400/10 dark:text-warning-300">
                                <span class="h-1.5 w-1.5 rounded-full bg-warning-500"></span>
                                Servidor
                            </span>
                        @endif
                    @else
                        <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-warning-50 px-2.5 py-1 text-xs font-semibold text-warning-700 dark:bg-warning-400/10 dark:text-warning-300">
                            <span class="h-1.5 w-1.5 rounded-full bg-warning-500"></span>
                            Sin configurar
                        </span>
                    @endif
                </div>

                <div class="space-y-5 px-5 py-5 sm:px-6">
                    <dl class="divide-y divide-gray-100 dark:divide-white/10">
                        <div class="flex items-start justify-between gap-4 py-3 first:pt-0">
                            <dt class="text-sm text-gray-500 dark:text-gray-400">Última actualización</dt>
                            <dd class="text-right text-sm font-medium text-gray-900 dark:text-white">
                                @if ($status['source'] === 'panel' && $status['updated_at'])
                                    {{ $status['updated_at']->format('d/m/Y H:i') }}
                                @elseif ($status['source'] === 'env' && $status['configured'])
                                    Configuración del servidor
                                @else
                                    —
                                @endif
                            </dd>
                        </div>
                        <div class="flex items-start justify-between gap-4 py-3">
                            <dt class="text-sm text-gray-500 dark:text-gray-400">Vencimiento estimado</dt>
                            <dd class="text-right text-sm font-medium {{ $status['expired'] ? 'text-danger-700 dark:text-danger-300' : 'text-gray-900 dark:text-white' }}">
                                @if ($status['expires_at'])
                                    {{ $status['expires_at']->format('d/m/Y H:i') }}
                                @else
                                    No disponible
                                @endif
                            </dd>
                        </div>
                    </dl>

                    @if ($vigenciaRestante !== null)
                        <div>
                            <div class="mb-2 flex items-center justify-between gap-3 text-xs">
                                <span class="font-medium text-gray-600 dark:text-gray-300">Vigencia estimada</span>
                                <span class="{{ $status['expired'] ? 'text-danger-600 dark:text-danger-300' : 'text-gray-500 dark:text-gray-400' }}">
                                    {{ $status['expires_at']->diffForHumans() }}
                                </span>
                            </div>
                            <div
                                class="h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-white/10"
                                role="progressbar"
                                aria-label="Vigencia estimada del token"
                                aria-valuemin="0"
                                aria-valuemax="100"
                                aria-valuenow="{{ $vigenciaRestante }}"
                            >
                                <div
                                    class="h-full rounded-full transition-all {{ $status['expired'] ? 'bg-danger-500' : ($vigenciaRestante <= 20 ? 'bg-warning-500' : 'bg-primary-600') }}"
                                    style="width: {{ $vigenciaRestante }}%"
                                ></div>
                            </div>
                        </div>
                    @endif

                    @if ($status['expired'])
                        <div class="flex gap-3 rounded-xl border border-danger-200 bg-danger-50 p-3.5 text-danger-800 dark:border-danger-400/20 dark:bg-danger-400/10 dark:text-danger-200">
                            <x-filament::icon icon="heroicon-o-exclamation-triangle" class="mt-0.5 h-5 w-5 shrink-0" />
                            <p class="text-sm leading-5">
                                El plazo estimado venció. Solicitá un token nuevo a Legajos y actualizalo.
                            </p>
                        </div>
                    @elseif ($status['source'] === 'env' && $status['configured'])
                        <div class="flex gap-3 rounded-xl border border-warning-200 bg-warning-50 p-3.5 text-warning-800 dark:border-warning-400/20 dark:bg-warning-400/10 dark:text-warning-200">
                            <x-filament::icon icon="heroicon-o-information-circle" class="mt-0.5 h-5 w-5 shrink-0" />
                            <p class="text-sm leading-5">
                                El token actual está en el servidor. Al cargar uno aquí podrás ver su fecha de actualización.
                            </p>
                        </div>
                    @elseif (! $status['configured'])
                        <div class="flex gap-3 rounded-xl border border-warning-200 bg-warning-50 p-3.5 text-warning-800 dark:border-warning-400/20 dark:bg-warning-400/10 dark:text-warning-200">
                            <x-filament::icon icon="heroicon-o-information-circle" class="mt-0.5 h-5 w-5 shrink-0" />
                            <p class="text-sm leading-5">
                                Solicitá un token vigente al equipo de Legajos para habilitar las consultas.
                            </p>
                        </div>
                    @endif
                </div>
            </section>

            <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-5 dark:border-white/10 sm:px-6">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary-50 text-primary-700 dark:bg-primary-400/10 dark:text-primary-300">
                            <x-filament::icon icon="heroicon-o-arrow-path" class="h-5 w-5" />
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-950 dark:text-white">
                                {{ $status['configured'] ? 'Actualizar token' : 'Cargar token' }}
                            </h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Pegá aquí la credencial más reciente.
                            </p>
                        </div>
                    </div>
                </div>

                <form wire:submit="guardar" class="space-y-5 px-5 py-5 sm:px-6">
                    <div>
                        <label for="legajo-api-token" class="mb-2 block text-sm font-medium text-gray-950 dark:text-white">
                            Token de acceso
                        </label>
                        <input
                            id="legajo-api-token"
                            type="password"
                            wire:model="token"
                            autocomplete="new-password"
                            placeholder="Pegá aquí el token"
                            required
                            aria-describedby="legajo-api-token-help legajo-api-token-error"
                            class="block w-full rounded-xl border-gray-300 bg-white px-4 py-3 text-sm text-gray-950 shadow-sm transition placeholder:text-gray-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 dark:border-white/10 dark:bg-white/[0.03] dark:text-white dark:placeholder:text-gray-500"
                        >
                        <p id="legajo-api-token-help" class="mt-2 text-xs leading-5 text-gray-500 dark:text-gray-400">
                            Se almacena cifrado y no volverá a mostrarse luego de guardarlo.
                        </p>
                        @error('token')
                            <p id="legajo-api-token-error" class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-4 border-t border-gray-100 pt-4 dark:border-white/10">
                        <p class="flex items-start gap-2 text-xs leading-5 text-gray-500 dark:text-gray-400">
                            <x-filament::icon icon="heroicon-o-clock" class="mt-0.5 h-4 w-4 shrink-0" />
                            La vigencia se estima desde el momento de la carga. Si Legajos lo emitió antes, podría vencer antes.
                        </p>
                        <x-filament::button class="w-full justify-center sm:w-auto sm:self-end" type="submit" icon="heroicon-o-check" wire:loading.attr="disabled" wire:target="guardar">
                            <span wire:loading.remove wire:target="guardar">Guardar token</span>
                            <span wire:loading wire:target="guardar">Guardando...</span>
                        </x-filament::button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</x-filament-panels::page>
