<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe de Resoluciones — Tribunal de Cuentas</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9.5px;
            color: #334155;
            line-height: 1.4;
            padding: 20px 25px;
        }

        /* ── Header ─────────────────────────────────────────────── */
        .header-table {
            width: 100%;
            margin-bottom: 16px;
            border-bottom: 3px solid #1A365D;
            padding-bottom: 12px;
        }
        .header-table td {
            vertical-align: middle;
            border: none;
            padding: 0;
        }
        .header-logo { width: 80px; }
        .header-logo img { max-height: 60px; width: auto; }
        .header-text { text-align: right; }
        .header-text h1 {
            font-size: 15px;
            font-weight: bold;
            color: #1A365D;
            margin-bottom: 3px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .header-text h2 {
            font-size: 11px;
            font-weight: 600;
            color: #6B1C5E;
            margin-bottom: 6px;
        }
        .header-text .meta {
            font-size: 8px;
            color: #64748b;
        }

        /* ── Info box ────────────────────────────────────────────── */
        .info-box {
            background-color: #f8fafc;
            border-left: 4px solid #1A365D;
            padding: 10px 12px;
            margin-bottom: 14px;
            border-radius: 0 4px 4px 0;
        }
        .info-grid { width: 100%; }
        .info-grid td {
            padding: 2px 5px;
            border: none;
            font-size: 9px;
        }

        /* ── Stats summary ───────────────────────────────────────── */
        .stats-section-title {
            font-size: 10px;
            font-weight: bold;
            color: #1A365D;
            border-bottom: 1px dashed #cbd5e1;
            padding-bottom: 4px;
            margin-top: 14px;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        .stats-summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        .stats-summary-table td {
            border: 1px solid #cbd5e1;
            padding: 8px;
            width: 25%;
            background-color: #f8fafc;
            text-align: center;
        }
        .stats-val {
            font-size: 16px;
            font-weight: bold;
            color: #1A365D;
            margin-bottom: 2px;
        }
        .stats-lbl {
            font-size: 7.5px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* ── Main data table ─────────────────────────────────────── */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .data-table th {
            background-color: #1A365D;
            color: #ffffff;
            font-weight: 600;
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 7px 5px;
            text-align: left;
            border: 1px solid #1A365D;
        }
        .data-table td {
            padding: 6px 5px;
            border: 1px solid #e2e8f0;
            font-size: 8.5px;
            vertical-align: middle;
            color: #475569;
        }
        .data-table tbody tr:nth-child(even) { background-color: #f8fafc; }
        .data-table tbody tr:nth-child(odd)  { background-color: #ffffff; }

        /* ── Badges ─────────────────────────────────────────────── */
        .badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 9999px;
            font-size: 7.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .badge-aprobado  { background: #dcfce7; color: #15803d; }
        .badge-rechazado { background: #fee2e2; color: #b91c1c; }
        .badge-pendiente { background: #fef9c3; color: #a16207; }
        .badge-revision  { background: #dbeafe; color: #1d4ed8; }
        .badge-default   { background: #f1f5f9; color: #64748b; }

        .num-resolucion {
            font-weight: 700;
            color: #1A365D;
        }

        /* ── Helpers ─────────────────────────────────────────────── */
        .text-center { text-align: center; }
        .text-right  { text-align: right; }
        .font-bold   { font-weight: bold; }
        .muted       { color: #94a3b8; }

        /* ── Footer ─────────────────────────────────────────────── */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
            text-align: center;
            font-size: 7.5px;
            color: #94a3b8;
        }
    </style>
</head>
<body>

    {{-- ── Encabezado ─────────────────────────────────────────── --}}
    <table class="header-table">
        <tr>
            <td class="header-logo">
                <img src="{{ public_path('images/Tribunal de cuentas (2).png') }}" alt="Logo Tribunal">
            </td>
            <td class="header-text">
                <h1>Tribunal de Cuentas</h1>
                <h2>Informe de Resoluciones</h2>
                <div class="meta">
                    Período:
                    {{ $fechaDesde ? \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') : 'Sin límite' }}
                    —
                    {{ $fechaHasta ? \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') : 'Sin límite' }}
                    @if($estadoFiltro)
                        &nbsp;|&nbsp; Estado: {{ ucfirst(str_replace('_', ' ', $estadoFiltro)) }}
                    @endif
                    <br>
                    Generado el {{ now()->format('d/m/Y H:i') }}
                </div>
            </td>
        </tr>
    </table>

    {{-- ── Info box ────────────────────────────────────────────── --}}
    <div class="info-box">
        <table class="info-grid">
            <tr>
                <td style="width:18%;"><strong>Período desde:</strong></td>
                <td style="width:32%;">{{ $fechaDesde ? \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') : '—' }}</td>
                <td style="width:18%;"><strong>Período hasta:</strong></td>
                <td style="width:32%;">{{ $fechaHasta ? \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') : '—' }}</td>
            </tr>
            <tr>
                <td><strong>Filtro de estado:</strong></td>
                <td>{{ $estadoFiltro ? ucfirst(str_replace('_', ' ', $estadoFiltro)) : 'Todos' }}</td>
                <td><strong>Total de registros:</strong></td>
                <td><strong>{{ $resoluciones->count() }}</strong></td>
            </tr>
        </table>
    </div>

    {{-- ── Resumen estadístico ─────────────────────────────────── --}}
    <div class="stats-section-title">Resumen Estadístico</div>
    <table class="stats-summary-table">
        <tr>
            <td>
                <div class="stats-val">{{ $resumen['total'] }}</div>
                <div class="stats-lbl">Total Resoluciones</div>
            </td>
            <td>
                <div class="stats-val" style="color:#15803d;">{{ $resumen['aprobadas'] }}</div>
                <div class="stats-lbl">Aprobadas</div>
            </td>
            <td>
                <div class="stats-val" style="color:#b91c1c;">{{ $resumen['rechazadas'] }}</div>
                <div class="stats-lbl">Rechazadas</div>
            </td>
            <td>
                <div class="stats-val" style="color:#a16207;">{{ $resumen['pendientes'] }}</div>
                <div class="stats-lbl">Pendientes / En Revisión</div>
            </td>
        </tr>
    </table>

    {{-- ── Tabla principal ─────────────────────────────────────── --}}
    <div class="stats-section-title">Detalle de Resoluciones</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:4%;" class="text-center">#</th>
                <th style="width:12%;">N° Resolución</th>
                <th style="width:10%;" class="text-center">Fecha Emisión</th>
                <th style="width:18%;">Agente</th>
                <th style="width:14%;">Área</th>
                <th style="width:16%;">Tipo de Licencia</th>
                <th style="width:6%;" class="text-center">Días</th>
                <th style="width:10%;" class="text-center">Estado</th>
                <th style="width:10%;">Detalle</th>
            </tr>
        </thead>
        <tbody>
            @forelse($resoluciones as $index => $resolucion)
                @php
                    $solicitud = $resolucion->solicitud;
                    $agente    = $solicitud?->agente;
                    $estado    = $solicitud?->estado ?? '';
                @endphp
                <tr>
                    <td class="text-center muted">{{ $index + 1 }}</td>
                    <td class="num-resolucion">{{ $resolucion->numero }}</td>
                    <td class="text-center">{{ $resolucion->fecha_emision?->format('d/m/Y') ?? '—' }}</td>
                    <td>
                        @if($agente)
                            <strong>{{ $agente->apellido }}</strong>, {{ $agente->nombre }}
                        @else
                            <span class="muted">Sin agente</span>
                        @endif
                    </td>
                    <td>{{ $agente?->area?->nombre ?? '—' }}</td>
                    <td>{{ $solicitud?->tipoSolicitud?->nombre ?? '—' }}</td>
                    <td class="text-center">{{ $solicitud?->dias_solicitados ?? '—' }}</td>
                    <td class="text-center">
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
                                <span class="badge badge-default">—</span>
                        @endswitch
                    </td>
                    <td style="font-size:8px; color:#64748b;">
                        {{ $resolucion->detalle ? \Illuminate\Support\Str::limit($resolucion->detalle, 40) : '—' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 20px; color: #94a3b8;">
                        No se encontraron resoluciones para el período y filtros indicados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ── Footer ──────────────────────────────────────────────── --}}
    <div class="footer">
        Tribunal de Cuentas — Provincia de Santiago del Estero — Sistema de Gestión de Licencias — {{ now()->format('Y') }}
    </div>
</body>
</html>
