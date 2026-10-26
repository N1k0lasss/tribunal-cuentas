<x-filament-panels::page>
<style>
    .informe-wrapper {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    /* ── Filter Card ─────────────────────────────────────────── */
    .filter-card {
        background: white;
        border-radius: 1rem;
        border: 1px solid rgba(226, 232, 240, 0.8);
        box-shadow: 0 4px 6px -1px rgba(0,0,0,.05);
        padding: 1.5rem;
    }
    .dark .filter-card {
        background: #0f172a;
        border-color: #1e293b;
    }
    .filter-card-title {
        font-size: 1rem;
        font-weight: 600;
        color: #1A365D;
        display: flex;
        align-items: center;
        gap: .5rem;
        margin-bottom: 1.25rem;
        padding-bottom: .75rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .dark .filter-card-title { color: #93c5fd; border-color: #1e293b; }
    .filter-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 1rem;
    }
    @media (min-width: 768px) { .filter-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    .filter-field label {
        display: block;
        font-size: .8rem;
        font-weight: 500;
        color: #64748b;
        margin-bottom: .4rem;
        text-transform: uppercase;
        letter-spacing: .05em;
    }
    .dark .filter-field label { color: #94a3b8; }
    .filter-field input,
    .filter-field select {
        width: 100%;
        padding: .55rem .85rem;
        border-radius: .5rem;
        border: 1px solid #cbd5e1;
        background: #f8fafc;
        font-size: .875rem;
        color: #1e293b;
        outline: none;
        transition: border-color .2s, box-shadow .2s;
    }
    .dark .filter-field input,
    .dark .filter-field select {
        background: #1e293b;
        border-color: #334155;
        color: #f1f5f9;
    }
    .filter-field input:focus,
    .filter-field select:focus {
        border-color: #1A365D;
        box-shadow: 0 0 0 3px rgba(26,54,93,.12);
    }
    .filter-btn {
        margin-top: 1.25rem;
        display: flex;
        justify-content: flex-end;
        gap: .75rem;
    }

    /* ── Metric cards ───────────────────────────────────────── */
    .metric-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0,1fr));
        gap: 1rem;
    }
    @media (min-width: 640px) { .metric-grid { grid-template-columns: repeat(2, minmax(0,1fr)); } }
    @media (min-width: 1024px) { .metric-grid { grid-template-columns: repeat(4, minmax(0,1fr)); } }
    .metric-card {
        position: relative;
        padding: 1.4rem 1.5rem;
        border-radius: 1rem;
        background: white;
        border: 1px solid rgba(226,232,240,.8);
        box-shadow: 0 4px 6px -1px rgba(0,0,0,.05);
        overflow: hidden;
        transition: transform .3s, box-shadow .3s;
    }
    .dark .metric-card { background: #0f172a; border-color: #1e293b; }
    .metric-card:hover { transform: translateY(-4px); box-shadow: 0 10px 20px -4px rgba(0,0,0,.12); }
    .metric-card::before {
        content: '';
        position: absolute;
        inset: 0 0 auto 0;
        height: 4px;
    }
    .mc-navy::before  { background: linear-gradient(90deg,#1A365D,#2B4C7E); }
    .mc-green::before { background: linear-gradient(90deg,#059669,#34d399); }
    .mc-red::before   { background: linear-gradient(90deg,#dc2626,#f87171); }
    .mc-amber::before { background: linear-gradient(90deg,#d97706,#fbbf24); }
    .metric-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: .75rem;
    }
    .metric-title { font-size: .8rem; font-weight: 500; color: #64748b; }
    .dark .metric-title { color: #94a3b8; }
    .metric-icon {
        width: 2.25rem;
        height: 2.25rem;
        border-radius: .5rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .mc-navy  .metric-icon { background: rgba(26,54,93,.1);  color: #1A365D; }
    .mc-green .metric-icon { background: rgba(5,150,105,.1); color: #059669; }
    .mc-red   .metric-icon { background: rgba(220,38,38,.1); color: #dc2626; }
    .mc-amber .metric-icon { background: rgba(217,119,6,.1); color: #d97706; }
    .dark .mc-navy  .metric-icon { background: rgba(26,54,93,.3); color: #60a5fa; }
    .dark .mc-green .metric-icon { background: rgba(5,150,105,.3); color: #34d399; }
    .dark .mc-red   .metric-icon { background: rgba(220,38,38,.3); color: #f87171; }
    .dark .mc-amber .metric-icon { background: rgba(217,119,6,.3); color: #fbbf24; }
    .metric-value { font-size: 2rem; font-weight: 700; color: #1e293b; line-height: 1.1; }
    .dark .metric-value { color: #f8fafc; }
    .metric-desc { font-size: .75rem; color: #94a3b8; margin-top: .2rem; }

    /* ── Table section ──────────────────────────────────────── */
    .table-section {
        background: white;
        border-radius: 1rem;
        border: 1px solid rgba(226,232,240,.8);
        box-shadow: 0 4px 6px -1px rgba(0,0,0,.05);
        overflow: hidden;
    }
    .dark .table-section { background: #0f172a; border-color: #1e293b; }
    .table-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .dark .table-header { border-color: #1e293b; }
    .table-title {
        font-size: 1rem;
        font-weight: 600;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: .5rem;
    }
    .dark .table-title { color: #f8fafc; }
    .table-count {
        font-size: .75rem;
        background: #EFF6FF;
        color: #1A365D;
        padding: .2rem .65rem;
        border-radius: 9999px;
        font-weight: 600;
    }
    .dark .table-count { background: #1e3a5f; color: #93c5fd; }
    .res-table {
        width: 100%;
        border-collapse: collapse;
    }
    .res-table th {
        padding: .75rem 1rem;
        text-align: left;
        font-size: .75rem;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: .05em;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }
    .dark .res-table th { background: #0f172a; color: #475569; border-color: #1e293b; }
    .res-table td {
        padding: .85rem 1rem;
        font-size: .875rem;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .dark .res-table td { color: #cbd5e1; border-color: #1e293b; }
    .res-table tbody tr:hover { background: #fafbff; }
    .dark .res-table tbody tr:hover { background: #0c1628; }

    .badge {
        display: inline-flex;
        align-items: center;
        padding: .2rem .6rem;
        border-radius: 9999px;
        font-size: .7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .badge-aprobado  { background: #dcfce7; color: #15803d; }
    .badge-rechazado { background: #fee2e2; color: #b91c1c; }
    .badge-pendiente { background: #fef9c3; color: #a16207; }
    .badge-revision  { background: #dbeafe; color: #1d4ed8; }
    .dark .badge-aprobado  { background: #14532d; color: #4ade80; }
    .dark .badge-rechazado { background: #450a0a; color: #f87171; }
    .dark .badge-pendiente { background: #422006; color: #fbbf24; }
    .dark .badge-revision  { background: #1e3a5f; color: #93c5fd; }

    .res-numero {
        font-weight: 700;
        color: #1A365D;
        font-size: .9rem;
    }
    .dark .res-numero { color: #60a5fa; }

    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: #94a3b8;
    }
    .empty-state svg { margin: 0 auto 1rem; opacity: .3; }

    /* Livewire wire:loading spinner */
    .loading-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(255,255,255,.5);
        z-index: 50;
        align-items: center;
        justify-content: center;
    }
    .dark .loading-overlay { background: rgba(15,23,42,.5); }
</style>

<div class="informe-wrapper" wire:key="informe-resoluciones">

    {{-- ── Filtros ─────────────────────────────────────────────── --}}
    <div class="filter-card">
        <div class="filter-card-title">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
            </svg>
            Filtros de búsqueda
        </div>
        <div class="filter-grid">
            <div class="filter-field">
                <label>Fecha desde</label>
                <input type="date" wire:model.live="fechaDesde" id="filtro_fecha_desde">
            </div>
            <div class="filter-field">
                <label>Fecha hasta</label>
                <input type="date" wire:model.live="fechaHasta" id="filtro_fecha_hasta">
            </div>
            <div class="filter-field">
                <label>Estado de solicitud</label>
                <select wire:model.live="estadoFiltro" id="filtro_estado">
                    <option value="">— Todos los estados —</option>
                    <option value="aprobado">Aprobado</option>
                    <option value="rechazado">Rechazado</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="en_revision">En Revisión</option>
                </select>
            </div>
        </div>
    </div>

    {{-- ── Métricas ────────────────────────────────────────────── --}}
    @php
        $resumen = $this->getResumenData();
    @endphp
    <div class="metric-grid">
        <div class="metric-card mc-navy">
            <div class="metric-header">
                <span class="metric-title">Total Resoluciones</span>
                <span class="metric-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </span>
            </div>
            <div class="metric-value">{{ $resumen['total'] }}</div>
            <div class="metric-desc">En el período seleccionado</div>
        </div>

        <div class="metric-card mc-green">
            <div class="metric-header">
                <span class="metric-title">Aprobadas</span>
                <span class="metric-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
            </div>
            <div class="metric-value">{{ $resumen['aprobadas'] }}</div>
            <div class="metric-desc">Solicitudes aprobadas</div>
        </div>

        <div class="metric-card mc-red">
            <div class="metric-header">
                <span class="metric-title">Rechazadas</span>
                <span class="metric-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
            </div>
            <div class="metric-value">{{ $resumen['rechazadas'] }}</div>
            <div class="metric-desc">Solicitudes rechazadas</div>
        </div>

        <div class="metric-card mc-amber">
            <div class="metric-header">
                <span class="metric-title">Pendientes / En revisión</span>
                <span class="metric-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
            </div>
            <div class="metric-value">{{ $resumen['pendientes'] }}</div>
            <div class="metric-desc">Aún en proceso</div>
        </div>
    </div>

    {{-- ── Tabla de resoluciones ───────────────────────────────── --}}
    @php
        $resoluciones = $this->getResoluciones();
    @endphp
    <div class="table-section">
        <div class="table-header">
            <div class="table-title">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
                Listado de Resoluciones
            </div>
            <span class="table-count">{{ $resoluciones->count() }} {{ $resoluciones->count() === 1 ? 'registro' : 'registros' }}</span>
        </div>

        <div style="overflow-x: auto;">
            <table class="res-table">
                <thead>
                    <tr>
                        <th>N° Resolución</th>
                        <th>Fecha Emisión</th>
                        <th>Agente</th>
                        <th>Área</th>
                        <th>Tipo de Licencia</th>
                        <th>Días</th>
                        <th>Estado</th>
                        <th>Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($resoluciones as $resolucion)
                        @php
                            $solicitud = $resolucion->solicitud;
                            $agente    = $solicitud?->agente;
                            $estado    = $solicitud?->estado ?? 'sin_solicitud';
                        @endphp
                        <tr>
                            <td>
                                <span class="res-numero">{{ $resolucion->numero }}</span>
                            </td>
                            <td>{{ $resolucion->fecha_emision?->format('d/m/Y') ?? '—' }}</td>
                            <td>
                                @if($agente)
                                    {{ $agente->apellido }}, {{ $agente->nombre }}
                                @else
                                    <span style="color:#94a3b8;">Sin agente</span>
                                @endif
                            </td>
                            <td>{{ $agente?->area?->nombre ?? '—' }}</td>
                            <td>{{ $solicitud?->tipoSolicitud?->nombre ?? '—' }}</td>
                            <td style="text-align:center;">
                                {{ $solicitud?->dias_solicitados ?? '—' }}
                            </td>
                            <td>
                                @switch($estado)
                                    @case('aprobado')
                                        <span class="badge badge-aprobado">Aprobado</span>
                                        @break
                                    @case('rechazado')
                                        <span class="badge badge-rechazado">Rechazado</span>
                                        @break
                                    @case('en_revision')
                                        <span class="badge badge-revision">En Revisión</span>
                                        @break
                                    @case('pendiente')
                                        <span class="badge badge-pendiente">Pendiente</span>
                                        @break
                                    @default
                                        <span class="badge" style="background:#f1f5f9;color:#64748b;">—</span>
                                @endswitch
                            </td>
                            <td style="max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; color:#94a3b8; font-size:.8rem;">
                                {{ $resolucion->detalle ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p style="font-weight:600; color:#475569; margin-bottom:.25rem;">No se encontraron resoluciones</p>
                                    <p style="font-size:.8rem;">Ajustá los filtros para ampliar la búsqueda.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
</x-filament-panels::page>
