<x-filament-panels::page>
    @php
        $filters = $this->getFiltersFromRequest();
        $resumen = $this->getResumen();
        $areas = $this->getAreas();
        $rowsArea = $this->getRowsPorArea();
        $rowsAgentes = $this->getRowsPorAgentes();
    @endphp

    <div style="display: flex; flex-direction: column; gap: 1.25rem;">
        <x-filament::section icon="heroicon-o-user-group">
            <x-slot name="heading">Panel Operativo de Licencias</x-slot>
            <x-slot name="description">Visualiza agentes inactivos por área o total diario con un formato legible para jefatura y secretaría.</x-slot>

            <form method="GET" action="{{ url('admin/calendario') }}" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 0.9rem; align-items: end; margin-top: 0.8rem;">
                <div>
                    <label style="display:block; margin-bottom:0.35rem; font-size:0.82rem; color:#475569;">Desde</label>
                    <x-filament::input.wrapper>
                        <x-filament::input type="date" name="desde" value="{{ $filters['desde']->toDateString() }}" />
                    </x-filament::input.wrapper>
                </div>

                <div>
                    <label style="display:block; margin-bottom:0.35rem; font-size:0.82rem; color:#475569;">Hasta</label>
                    <x-filament::input.wrapper>
                        <x-filament::input type="date" name="hasta" value="{{ $filters['hasta']->toDateString() }}" />
                    </x-filament::input.wrapper>
                </div>

                <div>
                    <label style="display:block; margin-bottom:0.35rem; font-size:0.82rem; color:#475569;">Vista</label>
                    <x-filament::input.wrapper>
                        <x-filament::input.select name="vista">
                            <option value="area" {{ $filters['vista'] === 'area' ? 'selected' : '' }}>Por área</option>
                            <option value="agentes" {{ $filters['vista'] === 'agentes' ? 'selected' : '' }}>Por agentes</option>
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>

                <div>
                    <label style="display:block; margin-bottom:0.35rem; font-size:0.82rem; color:#475569;">Área (opcional)</label>
                    <x-filament::input.wrapper>
                        <x-filament::input.select name="area_id">
                            <option value="">Todas</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id }}" {{ (string) $filters['area_id'] === (string) $area->id ? 'selected' : '' }}>{{ $area->nombre }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>

                <div>
                    <x-filament::button type="submit" icon="heroicon-m-funnel">Aplicar</x-filament::button>
                </div>
            </form>
        </x-filament::section>

        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
            <x-filament::section>
                <p style="font-size:0.75rem; color:#64748b; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.4rem;">Ausentes Hoy</p>
                <p style="font-size:1.9rem; line-height:1; font-weight:700; color:#0f766e;">{{ $resumen['ausentes_hoy'] }}</p>
            </x-filament::section>

            <x-filament::section>
                <p style="font-size:0.75rem; color:#64748b; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.4rem;">Pico del Período</p>
                <p style="font-size:1.9rem; line-height:1; font-weight:700; color:#1d4ed8;">{{ $resumen['pico_periodo'] }}</p>
            </x-filament::section>

            <x-filament::section>
                <p style="font-size:0.75rem; color:#64748b; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.4rem;">Sector Más Afectado</p>
                <p style="font-size:1.1rem; font-weight:700; color:#7c2d12;">{{ $resumen['sector_mas_afectado'] }}</p>
                <p style="font-size:0.85rem; color:#9a3412;">{{ $resumen['agentes_sector_mas_afectado'] }} agente(s)</p>
            </x-filament::section>
        </div>

        <x-filament::section>
            <x-slot name="heading">
                {{ $filters['vista'] === 'area' ? 'Licencias por área y fecha' : 'Licencias por agentes y fecha' }}
            </x-slot>
            <x-slot name="description">
                Rango consultado: {{ $filters['desde']->format('d/m/Y') }} al {{ $filters['hasta']->format('d/m/Y') }}.
            </x-slot>

            @if($filters['vista'] === 'area')
                @if($rowsArea === [])
                    <p style="padding:1.2rem; color:#64748b;">No hay agentes en licencia para el filtro seleccionado.</p>
                @else
                    <div style="overflow-x:auto;">
                        <table style="width:100%; border-collapse:collapse; font-size:0.9rem;">
                            <thead>
                                <tr style="border-bottom:1px solid #e2e8f0;">
                                    <th style="text-align:left; padding:0.7rem;">Fecha</th>
                                    <th style="text-align:left; padding:0.7rem;">Área</th>
                                    <th style="text-align:center; padding:0.7rem;">Cantidad inactivos</th>
                                    <th style="text-align:left; padding:0.7rem;">Detalle de agentes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rowsArea as $row)
                                    <tr style="border-bottom:1px solid #f1f5f9;">
                                        <td style="padding:0.7rem; white-space:nowrap;">{{ $row['fecha'] }}</td>
                                        <td style="padding:0.7rem;">{{ $row['area'] }}</td>
                                        <td style="padding:0.7rem; text-align:center; font-weight:700;">{{ $row['cantidad'] }}</td>
                                        <td style="padding:0.7rem;">
                                            <details>
                                                <summary style="cursor:pointer; color:#1d4ed8;">Ver agentes</summary>
                                                <div style="margin-top:0.45rem; display:flex; flex-wrap:wrap; gap:0.35rem;">
                                                    @foreach($row['agentes'] as $agente)
                                                        <span style="background:#eff6ff; color:#1e3a8a; border:1px solid #bfdbfe; border-radius:999px; padding:0.2rem 0.55rem; font-size:0.78rem;">{{ $agente }}</span>
                                                    @endforeach
                                                </div>
                                            </details>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @else
                @if($rowsAgentes === [])
                    <p style="padding:1.2rem; color:#64748b;">No hay agentes en licencia para el filtro seleccionado.</p>
                @else
                    <div style="overflow-x:auto;">
                        <table style="width:100%; border-collapse:collapse; font-size:0.9rem;">
                            <thead>
                                <tr style="border-bottom:1px solid #e2e8f0;">
                                    <th style="text-align:left; padding:0.7rem;">Fecha</th>
                                    <th style="text-align:center; padding:0.7rem;">Total inactivos</th>
                                    <th style="text-align:left; padding:0.7rem;">Agentes en licencia</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rowsAgentes as $row)
                                    <tr style="border-bottom:1px solid #f1f5f9;">
                                        <td style="padding:0.7rem; white-space:nowrap;">{{ $row['fecha'] }}</td>
                                        <td style="padding:0.7rem; text-align:center; font-weight:700;">{{ $row['total'] }}</td>
                                        <td style="padding:0.7rem;">
                                            @if($row['agentes'] === [])
                                                <span style="color:#94a3b8;">Sin ausencias</span>
                                            @else
                                                <details>
                                                    <summary style="cursor:pointer; color:#0f766e;">Ver listado</summary>
                                                    <div style="margin-top:0.45rem; display:flex; flex-wrap:wrap; gap:0.35rem;">
                                                        @foreach($row['agentes'] as $agente)
                                                            <span style="background:#ecfeff; color:#155e75; border:1px solid #a5f3fc; border-radius:999px; padding:0.2rem 0.55rem; font-size:0.78rem;">{{ $agente }}</span>
                                                        @endforeach
                                                    </div>
                                                </details>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>
