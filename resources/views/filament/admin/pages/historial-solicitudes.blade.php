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

    {{-- Enlace a Estadísticas (mantener disponible solo para roles) --}}
    @if($this->canViewLicenciasStats())
        <div style="margin-bottom: 1rem;">
            <a href="{{ url('admin/estadisticas-licencias') }}?{{ http_build_query(request()->query()) }}" class="filament-button filament-button-secondary">Ver estadísticas</a>
        </div>
    @endif

    {{-- Table --}}
    <div class="historial-table-wrapper">
        {{ $this->table }}
    </div>
</x-filament-panels::page>
