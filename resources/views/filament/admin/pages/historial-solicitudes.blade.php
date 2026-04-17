<x-filament-panels::page>
    {{-- Hero Header --}}
    <div class="historial-hero">
        <div class="historial-hero__content">
            <div class="historial-hero__icon-wrapper">
                <x-heroicon-o-clock class="historial-hero__icon" />
            </div>
            <div class="historial-hero__text">
                <p class="historial-hero__subtitle">Registro completo de licencias</p>
                <p class="historial-hero__description">
                    Consulta, filtra y exporta el historial de solicitudes de licencia.
                    Utiliza los filtros para refinar los resultados por año, estado o agente.
                </p>
            </div>
        </div>
        <div class="historial-hero__decoration">
            <div class="historial-hero__glow"></div>
        </div>
    </div>

    {{-- Stats Cards --}}
    @php
        $user = auth()->user();
        $esAgente = $user?->hasRole('agente');
        $anio = $this->tableFilters['anio']['value'] ?? date('Y');
        $baseQuery = \App\Models\Solicitud::query()
            ->when($esAgente, fn($q) => $q->where('agente_id', $user->agente?->id))
            ->whereYear('fecha_solicitud', $anio);
        $total = (clone $baseQuery)->count();
        $aprobadas = (clone $baseQuery)->where('estado', 'aprobado')->count();
        $pendientes = (clone $baseQuery)->where('estado', 'pendiente')->count();
        $rechazadas = (clone $baseQuery)->where('estado', 'rechazado')->count();
    @endphp

    <div class="historial-stats">
        <div class="historial-stat historial-stat--total">
            <div class="historial-stat__icon-wrap historial-stat__icon-wrap--total">
                <x-heroicon-o-document-text class="historial-stat__icon" />
            </div>
            <div class="historial-stat__data">
                <span class="historial-stat__value">{{ $total }}</span>
                <span class="historial-stat__label">Total Solicitudes</span>
            </div>
        </div>
        <div class="historial-stat historial-stat--aprobadas">
            <div class="historial-stat__icon-wrap historial-stat__icon-wrap--aprobadas">
                <x-heroicon-o-check-circle class="historial-stat__icon" />
            </div>
            <div class="historial-stat__data">
                <span class="historial-stat__value">{{ $aprobadas }}</span>
                <span class="historial-stat__label">Aprobadas</span>
            </div>
        </div>
        <div class="historial-stat historial-stat--pendientes">
            <div class="historial-stat__icon-wrap historial-stat__icon-wrap--pendientes">
                <x-heroicon-o-clock class="historial-stat__icon" />
            </div>
            <div class="historial-stat__data">
                <span class="historial-stat__value">{{ $pendientes }}</span>
                <span class="historial-stat__label">Pendientes</span>
            </div>
        </div>
        <div class="historial-stat historial-stat--rechazadas">
            <div class="historial-stat__icon-wrap historial-stat__icon-wrap--rechazadas">
                <x-heroicon-o-x-circle class="historial-stat__icon" />
            </div>
            <div class="historial-stat__data">
                <span class="historial-stat__value">{{ $rechazadas }}</span>
                <span class="historial-stat__label">Rechazadas</span>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="historial-table-wrapper">
        {{ $this->table }}
    </div>
</x-filament-panels::page>
