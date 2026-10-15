<x-filament-panels::page>
    <style>
        .report-dashboard {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        .metric-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1rem;
        }
        @media (min-width: 768px) {
            .metric-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (min-width: 1024px) {
            .metric-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }
        .metric-card {
            position: relative;
            padding: 1.5rem;
            border-radius: 1rem;
            background: white;
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .dark .metric-card {
            background: #0f172a;
            border-color: #1e293b;
        }
        .metric-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
        }
        .metric-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
        }
        .metric-card--navy::before { background: linear-gradient(90deg, #1A365D, #2B4C7E); }
        .metric-card--purple::before { background: linear-gradient(90deg, #6B1C5E, #8A2C7E); }
        .metric-card--teal::before { background: linear-gradient(90deg, #0d9488, #2dd4bf); }
        .metric-card--amber::before { background: linear-gradient(90deg, #d97706, #b45309); }

        .metric-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        .metric-title {
            font-size: 0.875rem;
            font-weight: 500;
            color: #64748b;
        }
        .dark .metric-title { color: #94a3b8; }
        .metric-icon {
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .metric-card--navy .metric-icon { background: rgba(26, 54, 93, 0.1); color: #1A365D; }
        .metric-card--purple .metric-icon { background: rgba(107, 28, 94, 0.1); color: #6B1C5E; }
        .metric-card--teal .metric-icon { background: rgba(13, 148, 136, 0.1); color: #0d9488; }
        .metric-card--amber .metric-icon { background: rgba(217, 119, 6, 0.1); color: #d97706; }

        .dark .metric-card--navy .metric-icon { background: rgba(26, 54, 93, 0.3); color: #60a5fa; }
        .dark .metric-card--purple .metric-icon { background: rgba(107, 28, 94, 0.3); color: #f472b6; }
        .dark .metric-card--teal .metric-icon { background: rgba(13, 148, 136, 0.3); color: #2dd4bf; }
        .dark .metric-card--amber .metric-icon { background: rgba(217, 119, 6, 0.3); color: #fbbf24; }

        .metric-value {
            font-size: 1.875rem;
            font-weight: 700;
            color: #1e293b;
            line-height: 1.2;
        }
        .dark .metric-value { color: #f8fafc; }
        .metric-desc {
            font-size: 0.75rem;
            color: #94a3b8;
            margin-top: 0.25rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .dark .metric-desc { color: #64748b; }

        /* Sections Grid */
        .sections-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1.5rem;
        }
        @media (min-width: 1024px) {
            .sections-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        .section-panel {
            background: white;
            border-radius: 1rem;
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
        }
        .dark .section-panel {
            background: #0f172a;
            border-color: #1e293b;
        }
        .section-panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 1rem;
            margin-bottom: 1.25rem;
        }
        .dark .section-panel-header { border-color: #1e293b; }
        .section-panel-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1E293B;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .dark .section-panel-title { color: #f8fafc; }

        /* Progress bars list */
        .progress-list {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }
        .progress-item {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .progress-label-row {
            display: flex;
            justify-content: space-between;
            font-size: 0.875rem;
            font-weight: 500;
            color: #334155;
        }
        .dark .progress-label-row { color: #cbd5e1; }
        .progress-label-name {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .progress-label-value {
            font-weight: 600;
            color: #1e293b;
            flex-shrink: 0;
        }
        .dark .progress-label-value { color: #f8fafc; }
        .progress-bar-track {
            height: 0.65rem;
            background: #f1f5f9;
            border-radius: 9999px;
            overflow: hidden;
        }
        .dark .progress-bar-track { background: #1e293b; }
        .progress-bar-fill {
            height: 100%;
            border-radius: 9999px;
            transition: width 0.8s ease-in-out;
        }

        .bg-grad-blue { background: linear-gradient(90deg, #1A365D, #2B4C7E); }
        .bg-grad-purple { background: linear-gradient(90deg, #6B1C5E, #8A2C7E); }
        .bg-grad-teal { background: linear-gradient(90deg, #0d9488, #2dd4bf); }
        .bg-grad-amber { background: linear-gradient(90deg, #d97706, #fbbf24); }

        .status-container {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 1rem;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 0.75rem;
            border: 1px solid #f1f5f9;
        }
        .dark .status-container {
            background: #0b0f19;
            border-color: #1e293b;
        }
        .status-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: #475569;
        }
        .dark .status-item { color: #94a3b8; }
        .status-dot {
            width: 0.75rem;
            height: 0.75rem;
            border-radius: 50%;
        }
        .status-dot--green { background-color: #10b981; }
        .status-dot--yellow { background-color: #f59e0b; }
        .status-dot--red { background-color: #ef4444; }

        /* Filter Panel Styles */
        .filter-panel {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            padding: 1.25rem 1.5rem;
            background: white;
            border-radius: 1rem;
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            margin-bottom: 0.5rem;
            width: 100%;
        }
        .dark .filter-panel {
            background: #0f172a;
            border-color: #1e293b;
        }
        @media (min-width: 640px) {
            .filter-panel {
                flex-direction: row;
                align-items: center;
            }
        }
        .filter-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        .filter-title {
            font-size: 0.95rem;
            font-weight: 600;
            color: #1e293b;
        }
        .dark .filter-title {
            color: #f8fafc;
        }
        .filter-subtitle {
            font-size: 0.75rem;
            color: #64748b;
        }
        .dark .filter-subtitle {
            color: #94a3b8;
        }
        .filter-select-wrapper {
            width: 12rem;
            max-width: 100%;
        }
        .filter-select {
            display: block;
            width: 100%;
            border-radius: 0.75rem;
            border: 1px solid #cbd5e1;
            background-color: white;
            padding: 0.5rem 2rem 0.5rem 1rem;
            font-size: 0.875rem;
            color: #1e293b;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            transition: all 0.2s;
            outline: none;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23475569' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 1rem;
            cursor: pointer;
        }
        .dark .filter-select {
            border-color: #334155;
            background-color: #1e293b;
            color: #f8fafc;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
        }
        .filter-select:focus {
            border-color: #1A365D;
            box-shadow: 0 0 0 3px rgba(26, 54, 93, 0.15);
        }
        .dark .filter-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
        }
    </style>

    <div class="report-dashboard">
        <!-- Selector de Año -->
        <div class="filter-panel">
            <div class="filter-info">
                <h3 class="filter-title">Filtrar período de análisis</h3>
                <span class="filter-subtitle">Selecciona el año para generar las estadísticas e informes anuales correspondientes.</span>
            </div>
            <div class="filter-select-wrapper">
                <select wire:model.live="anio" class="filter-select">
                    @foreach($this->getAnios() as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @php
            $report = $this->getReportData();
        @endphp

        <!-- Panel de Tarjetas de Métricas -->
        <div class="metric-grid">
            <!-- Total Solicitudes -->
            <div class="metric-card metric-card--navy">
                <div>
                    <div class="metric-header">
                        <span class="metric-title">Solicitudes Totales</span>
                        <div class="metric-icon">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                    </div>
                    <div class="metric-value">{{ $report['totalSolicitudes'] }}</div>
                </div>
                <div class="metric-desc">Registradas en el año {{ $this->anio }}</div>
            </div>

            <!-- Total Días -->
            <div class="metric-card metric-card--amber">
                <div>
                    <div class="metric-header">
                        <span class="metric-title">Total de Días</span>
                        <div class="metric-icon">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                    </div>
                    <div class="metric-value">{{ $report['totalDias'] }}</div>
                </div>
                <div class="metric-desc">Días acumulados de licencia</div>
            </div>

            <!-- Licencia Más Popular -->
            <div class="metric-card metric-card--teal">
                <div>
                    <div class="metric-header">
                        <span class="metric-title">Licencia Más Popular</span>
                        <div class="metric-icon">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        </div>
                    </div>
                    <div class="metric-value text-xl truncate font-bold" style="font-size: 1.25rem;">{{ $report['mostRequestedLicense'] }}</div>
                </div>
                <div class="metric-desc">Mayor cantidad de solicitudes</div>
            </div>

            <!-- Área Más Activa -->
            <div class="metric-card metric-card--purple">
                <div>
                    <div class="metric-header">
                        <span class="metric-title">Área con Más Pedidos</span>
                        <div class="metric-icon">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        </div>
                    </div>
                    <div class="metric-value text-xl truncate font-bold" style="font-size: 1.25rem;">{{ $report['mostActiveArea'] }}</div>
                </div>
                <div class="metric-desc">Sector con mayor uso</div>
            </div>
        </div>

        <!-- Distribución del Estado de Solicitudes -->
        <div class="status-container">
            <div class="status-item">
                <span class="status-dot status-dot--green"></span>
                <span><strong>Aprobadas:</strong> {{ $report['totalAprobadas'] }}</span>
            </div>
            <div class="status-item">
                <span class="status-dot status-dot--yellow"></span>
                <span><strong>En Revisión/Pendientes:</strong> {{ $report['totalPendientes'] }}</span>
            </div>
            <div class="status-item">
                <span class="status-dot status-dot--red"></span>
                <span><strong>Rechazadas:</strong> {{ $report['totalRechazadas'] }}</span>
            </div>
        </div>

        <!-- Secciones de Distribución Detallada -->
        <div class="sections-grid">
            <!-- Licencias más solicitadas -->
            <div class="section-panel">
                <div class="section-panel-header">
                    <h4 class="section-panel-title">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2"></path></svg>
                        Tipos de Licencia Más Solicitados
                    </h4>
                    <span class="text-xs text-gray-400 dark:text-gray-500">Ordenados por volumen</span>
                </div>
                <div class="progress-list">
                    @forelse($report['licencias'] as $licencia)
                        <div class="progress-item">
                            <div class="progress-label-row">
                                <span class="progress-label-name" title="{{ $licencia['nombre'] }}">{{ $licencia['nombre'] }}</span>
                                <span class="progress-label-value">
                                    {{ $licencia['cantidad'] }} {{ $licencia['cantidad'] === 1 ? 'solicitud' : 'solicitudes' }} 
                                    <span class="text-gray-400 text-xs font-normal">({{ $licencia['dias'] }} días) — {{ $licencia['porcentaje'] }}%</span>
                                </span>
                            </div>
                            <div class="progress-bar-track">
                                <div class="progress-bar-fill bg-grad-blue" style="width: {{ $licencia['porcentaje'] }}%;"></div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-400 dark:text-gray-500">
                            No hay solicitudes registradas para el año {{ $this->anio }}
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Solicitudes por Área -->
            <div class="section-panel">
                <div class="section-panel-header">
                    <h4 class="section-panel-title">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Solicitudes por Área de Trabajo
                    </h4>
                    <span class="text-xs text-gray-400 dark:text-gray-500">Uso de licencias por área</span>
                </div>
                <div class="progress-list">
                    @forelse($report['areas'] as $area)
                        <div class="progress-item">
                            <div class="progress-label-row">
                                <span class="progress-label-name" title="{{ $area['nombre'] }}">{{ $area['nombre'] }}</span>
                                <span class="progress-label-value">
                                    {{ $area['cantidad'] }} {{ $area['cantidad'] === 1 ? 'solicitud' : 'solicitudes' }}
                                    <span class="text-gray-400 text-xs font-normal">({{ $area['dias'] }} días) — {{ $area['porcentaje'] }}%</span>
                                </span>
                            </div>
                            <div class="progress-bar-track">
                                <div class="progress-bar-fill bg-grad-purple" style="width: {{ $area['porcentaje'] }}%;"></div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-400 dark:text-gray-500">
                            No hay solicitudes registradas para el año {{ $this->anio }}
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
