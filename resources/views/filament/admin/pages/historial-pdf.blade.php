<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Solicitudes {{ $anio }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            color: #334155;
            line-height: 1.4;
            padding: 20px;
        }

        .header-table {
            width: 100%;
            margin-bottom: 20px;
            border-bottom: 3px solid #1A365D;
            padding-bottom: 15px;
        }

        .header-table td {
            vertical-align: middle;
            border: none;
            padding: 0;
        }

        .header-logo {
            width: 90px;
        }

        .header-logo img {
            max-height: 80px;
            width: auto;
        }

        .header-text {
            text-align: right;
        }

        .header-text h1 {
            font-size: 18px;
            font-weight: bold;
            color: #1A365D;
            margin-bottom: 4px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .header-text h2 {
            font-size: 13px;
            font-weight: 600;
            color: #6B1C5E;
            margin-bottom: 8px;
        }

        .header-text .meta {
            font-size: 10px;
            color: #64748b;
        }

        .filters-info {
            background-color: #f1f5f9;
            border-left: 4px solid #1A365D;
            padding: 10px 14px;
            margin-bottom: 20px;
            font-size: 10px;
            color: #334155;
            border-radius: 0 4px 4px 0;
        }

        .filters-info strong {
            color: #1A365D;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .data-table th {
            background-color: #1A365D;
            color: #ffffff;
            font-weight: 600;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 10px 8px;
            text-align: left;
            border: 1px solid #1A365D;
        }

        .data-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .data-table tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }

        .data-table td {
            padding: 8px;
            border: 1px solid #e2e8f0;
            font-size: 10px;
            vertical-align: middle;
            color: #475569;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            text-align: center;
        }

        .badge-pendiente {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }

        .badge-en_revision {
            background-color: #e0e7ff;
            color: #3730a3;
            border: 1px solid #c7d2fe;
        }

        .badge-aprobado {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .badge-rechazado {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #cbd5e1;
            text-align: center;
            font-size: 9px;
            color: #64748b;
        }

        .summary {
            margin-top: 15px;
            padding: 12px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 10px;
        }

        .summary-row {
            display: inline-block;
            margin-right: 24px;
        }

        .summary-label {
            font-weight: bold;
            color: #1A365D;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.5px;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td class="header-logo">
                <img src="{{ public_path('images/Tribunal de cuentas (2).png') }}" alt="Logo Tribunal">
            </td>
            <td class="header-text">
                <h1>Tribunal de Cuentas</h1>
                <h2>Historial de Solicitudes — {{ $anio }}</h2>
                <div class="meta">
                    Generado el {{ now()->format('d/m/Y H:i') }}
                </div>
            </td>
        </tr>
    </table>

    <div class="filters-info">
        <strong>Filtros aplicados:</strong>
        Año: {{ $anio }}
        @if($agente)
            | Agente: {{ $agente->apellido }}, {{ $agente->nombre }}
        @endif
        @if($estadoFiltro)
            | Estado: {{ ucfirst(str_replace('_', ' ', $estadoFiltro)) }}
        @endif
        | Total de registros: {{ $solicitudes->count() }}
    </div>

    @if($solicitudes->isEmpty())
        <p class="text-center" style="padding: 40px; color: #64748b; font-size: 12px;">
            <em>No se encontraron solicitudes con los filtros seleccionados.</em>
        </p>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 18%;">Agente</th>
                    <th style="width: 20%;">Tipo de Licencia</th>
                    <th style="width: 12%; text-align: center;">Fecha Solicitud</th>
                    <th style="width: 10%; text-align: center;">Desde</th>
                    <th style="width: 10%; text-align: center;">Hasta</th>
                    <th style="width: 7%; text-align: center;">Días</th>
                    <th style="width: 13%; text-align: center;">Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($solicitudes as $index => $solicitud)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td><strong>{{ $solicitud->agente->apellido }}</strong>, {{ $solicitud->agente->nombre }}</td>
                        <td>{{ $solicitud->tipoSolicitud->nombre ?? '-' }}</td>
                        <td class="text-center">{{ $solicitud->fecha_solicitud?->format('d/m/Y') ?? '-' }}</td>
                        <td class="text-center">{{ $solicitud->fecha_inicio?->format('d/m/Y') ?? '-' }}</td>
                        <td class="text-center">{{ $solicitud->fecha_fin?->format('d/m/Y') ?? '-' }}</td>
                        <td class="text-center font-bold">{{ $solicitud->dias_solicitados ?? '-' }}</td>
                        <td class="text-center">
                            <span class="badge badge-{{ $solicitud->estado }}">
                                {{ str_replace('_', ' ', ucfirst($solicitud->estado)) }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary">
            <span class="summary-row">
                <span class="summary-label">Total:</span> {{ $solicitudes->count() }}
            </span>
            <span class="summary-row">
                <span class="summary-label">Aprobadas:</span> {{ $solicitudes->where('estado', 'aprobado')->count() }}
            </span>
            <span class="summary-row">
                <span class="summary-label">Pendientes:</span> {{ $solicitudes->whereIn('estado', ['pendiente', 'en_revision'])->count() }}
            </span>
            <span class="summary-row">
                <span class="summary-label">Rechazadas:</span> {{ $solicitudes->where('estado', 'rechazado')->count() }}
            </span>
            <span class="summary-row">
                <span class="summary-label">Total Días:</span> {{ $solicitudes->sum('dias_solicitados') }}
            </span>
        </div>
    @endif

    <div class="footer">
        Tribunal de Cuentas Provincia de Santiago del Estero — Sistema de Gestión de Licencias — {{ now()->format('Y') }}
    </div>
</body>
</html>
