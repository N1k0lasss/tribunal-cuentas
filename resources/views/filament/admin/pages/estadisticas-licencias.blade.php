<x-filament-panels::page>
    @php
        $filtersData = $this->getFilters();
        $anios = $filtersData['anios'];
        $agentes = $filtersData['agentes'];

        $anioSelected = request()->query('anio', now()->year);
        $estadoSelected = request()->query('estado', '');
        $agenteSelected = request()->query('agente_id', '');

        $stats = $this->getLicenciasStatsFromRequest();
    @endphp

    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
        
        <!-- Header & Filters -->
        <x-filament::section icon="heroicon-o-chart-bar">
            <x-slot name="heading">
                Estadísticas de Licencias
            </x-slot>
            <x-slot name="description">
                Analizá la cantidad y los días por tipo de licencia. Utilizá los filtros a continuación para segmentar la información por año, estado o agente específico.
            </x-slot>

            <form method="GET" action="{{ url('admin/estadisticas-licencias') }}" style="margin-top: 1.5rem; display: flex; flex-wrap: wrap; gap: 1.5rem; align-items: flex-end;">
                
                <div style="flex: 1; min-width: 150px;">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.875rem; font-weight: 500; color: var(--text-color);">Año</label>
                    <x-filament::input.wrapper>
                        <x-filament::input.select name="anio">
                            @foreach($anios as $key => $label)
                                <option value="{{ $key }}" {{ (string)$key === (string)$anioSelected ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>

                <div style="flex: 1; min-width: 150px;">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.875rem; font-weight: 500; color: var(--text-color);">Estado</label>
                    <x-filament::input.wrapper>
                        <x-filament::input.select name="estado">
                            <option value="" {{ $estadoSelected === '' ? 'selected' : '' }}>Todos los estados</option>
                            <option value="pendiente" {{ $estadoSelected === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                            <option value="en_revision" {{ $estadoSelected === 'en_revision' ? 'selected' : '' }}>En Revisión</option>
                            <option value="aprobado" {{ $estadoSelected === 'aprobado' ? 'selected' : '' }}>Aprobado</option>
                            <option value="rechazado" {{ $estadoSelected === 'rechazado' ? 'selected' : '' }}>Rechazado</option>
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>

                <div style="flex: 1; min-width: 250px;">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.875rem; font-weight: 500; color: var(--text-color);">Agente</label>
                    <x-filament::input.wrapper>
                        <x-filament::input.select name="agente_id">
                            <option value="">Todos los agentes</option>
                            @foreach($agentes as $ag)
                                <option value="{{ $ag->id }}" {{ (string)$ag->id === (string)$agenteSelected ? 'selected' : '' }}>{{ $ag->apellido }}, {{ $ag->nombre }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>

                <div>
                    <x-filament::button type="submit" icon="heroicon-m-funnel">
                        Aplicar Filtros
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>

        <!-- Stats Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
            <!-- Stat 1 -->
            <x-filament::section>
                <div style="display: flex; align-items: center; gap: 1.25rem;">
                    <div style="background: rgba(59, 130, 246, 0.1); padding: 1rem; border-radius: 0.75rem; color: #3b82f6; display: flex; align-items: center; justify-content: center;">
                        <x-heroicon-o-document-text style="width: 2.5rem; height: 2.5rem;" />
                    </div>
                    <div>
                        <div style="font-size: 2.25rem; font-weight: 700; line-height: 1; color: var(--text-color);">{{ $stats['total_solicitudes'] }}</div>
                        <div style="font-size: 0.875rem; font-weight: 500; color: #6b7280; margin-top: 0.5rem; text-transform: uppercase; letter-spacing: 0.05em;">Total Solicitudes</div>
                    </div>
                </div>
            </x-filament::section>

            <!-- Stat 2 -->
            <x-filament::section>
                <div style="display: flex; align-items: center; gap: 1.25rem;">
                    <div style="background: rgba(16, 185, 129, 0.1); padding: 1rem; border-radius: 0.75rem; color: #10b981; display: flex; align-items: center; justify-content: center;">
                        <x-heroicon-o-calendar-days style="width: 2.5rem; height: 2.5rem;" />
                    </div>
                    <div>
                        <div style="font-size: 2.25rem; font-weight: 700; line-height: 1; color: var(--text-color);">{{ $stats['total_dias'] }}</div>
                        <div style="font-size: 0.875rem; font-weight: 500; color: #6b7280; margin-top: 0.5rem; text-transform: uppercase; letter-spacing: 0.05em;">Días Solicitados</div>
                    </div>
                </div>
            </x-filament::section>

            <!-- Stat 3 -->
            <x-filament::section>
                <div style="display: flex; align-items: center; gap: 1.25rem;">
                    <div style="background: rgba(245, 158, 11, 0.1); padding: 1rem; border-radius: 0.75rem; color: #f59e0b; display: flex; align-items: center; justify-content: center;">
                        <x-heroicon-o-squares-2x2 style="width: 2.5rem; height: 2.5rem;" />
                    </div>
                    <div>
                        <div style="font-size: 2.25rem; font-weight: 700; line-height: 1; color: var(--text-color);">{{ $stats['tipos_utilizados'] }}</div>
                        <div style="font-size: 0.875rem; font-weight: 500; color: #6b7280; margin-top: 0.5rem; text-transform: uppercase; letter-spacing: 0.05em;">Tipos Utilizados</div>
                    </div>
                </div>
            </x-filament::section>
        </div>

        <!-- Table Section -->
        <x-filament::section>
            <x-slot name="heading">
                Detalle por tipo
            </x-slot>

            @if($stats['por_tipo']->isEmpty())
                <div style="text-align: center; padding: 3rem 1rem; color: #6b7280;">
                    <div style="background: rgba(107, 114, 128, 0.1); border-radius: 9999px; width: 4rem; height: 4rem; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                        <x-heroicon-o-inbox style="width: 2rem; height: 2rem; opacity: 0.7;" />
                    </div>
                    <p style="margin-top: 1rem; font-weight: 500;">No se encontraron datos para los filtros seleccionados.</p>
                </div>
            @else
                <div style="overflow-x: auto; margin-top: 1rem;">
                    <table style="width: 100%; text-align: left; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 1px solid rgba(107, 114, 128, 0.2);">
                                <th style="padding: 1rem; font-size: 0.875rem; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Tipo de Licencia</th>
                                <th style="padding: 1rem; font-size: 0.875rem; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; text-align: center;">Cantidad</th>
                                <th style="padding: 1rem; font-size: 0.875rem; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; text-align: center;">Días Totales</th>
                                <th style="padding: 1rem; font-size: 0.875rem; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; text-align: center;">Promedio Días</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stats['por_tipo'] as $tipo)
                                <tr style="border-bottom: 1px solid rgba(107, 114, 128, 0.1); transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='rgba(107, 114, 128, 0.05)'" onmouseout="this.style.backgroundColor='transparent'">
                                    <td style="padding: 1rem; font-size: 0.875rem; font-weight: 500; color: var(--text-color);">
                                        {{ $tipo['tipo'] }}
                                    </td>
                                    <td style="padding: 1rem; font-size: 0.875rem; text-align: center;">
                                        <span style="background: rgba(107, 114, 128, 0.1); padding: 0.25rem 0.75rem; border-radius: 9999px; font-weight: 600; color: var(--text-color);">
                                            {{ $tipo['cantidad'] }}
                                        </span>
                                    </td>
                                    <td style="padding: 1rem; font-size: 0.875rem; text-align: center; font-weight: 600; color: var(--text-color);">
                                        {{ $tipo['dias_totales'] }}
                                    </td>
                                    <td style="padding: 1rem; font-size: 0.875rem; text-align: center; font-weight: 600; color: var(--text-color);">
                                        {{ number_format($tipo['promedio_dias'], 1, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>

        <!-- Action Button -->
        <div style="display: flex; justify-content: flex-end;">
            <x-filament::button tag="a" href="{{ url('admin/historial-solicitudes') }}?{{ http_build_query(request()->query()) }}" color="gray" icon="heroicon-m-arrow-left">
                Volver al Historial
            </x-filament::button>
        </div>
    </div>
</x-filament-panels::page>
