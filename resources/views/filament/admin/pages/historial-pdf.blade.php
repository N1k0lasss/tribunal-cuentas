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
            color: #1f2937;
            line-height: 1.4;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #d97706;
        }

        .header h1 {
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 4px;
        }

        .header h2 {
            font-size: 14px;
            font-weight: 600;
            color: #d97706;
            margin-bottom: 8px;
        }

        .header .meta {
            font-size: 10px;
            color: #6b7280;
        }

        .filters-info {
            background-color: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 16px;
            font-size: 10px;
            color: #92400e;
        }

        .filters-info strong {
            color: #78350f;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        thead th {
            background-color: #f59e0b;
            color: #ffffff;
            font-weight: 600;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 8px 6px;
            text-align: left;
            border: 1px solid #d97706;
        }

        tbody tr:nth-child(even) {
            background-color: #fefce8;
        }

        tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }

        tbody td {
            padding: 6px;
            border: 1px solid #e5e7eb;
            font-size: 10px;
            vertical-align: middle;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .badge-pendiente {
            background-color: #fef3c7;
            color: #92400e;
        }

        .badge-en_revision {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .badge-aprobado {
            background-color: #d1fae5;
            color: #065f46;
        }

        .badge-rechazado {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 9px;
            color: #9ca3af;
        }

        .summary {
            margin-top: 12px;
            padding: 10px;
            background-color: #f3f4f6;
            border-radius: 6px;
            font-size: 10px;
        }

        .summary-row {
            display: inline-block;
            margin-right: 20px;
        }

        .summary-label {
            font-weight: 600;
            color: #374151;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Tribunal de Cuentas</h1>
        <h2>Historial de Solicitudes — {{ $anio }}</h2>
        <div class="meta">
            Generado el {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

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
        <p class="text-center" style="padding: 40px; color: #6b7280;">
            No se encontraron solicitudes con los filtros seleccionados.
        </p>
    @else
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 18%;">Agente</th>
                    <th style="width: 20%;">Tipo de Licencia</th>
                    <th style="width: 12%;">Fecha Solicitud</th>
                    <th style="width: 10%;">Desde</th>
                    <th style="width: 10%;">Hasta</th>
                    <th style="width: 7%;">Días</th>
                    <th style="width: 13%;">Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($solicitudes as $index => $solicitud)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $solicitud->agente->apellido }}, {{ $solicitud->agente->nombre }}</td>
                        <td>{{ $solicitud->tipoSolicitud->nombre ?? '-' }}</td>
                        <td class="text-center">{{ $solicitud->fecha_solicitud?->format('d/m/Y') ?? '-' }}</td>
                        <td class="text-center">{{ $solicitud->fecha_inicio?->format('d/m/Y') ?? '-' }}</td>
                        <td class="text-center">{{ $solicitud->fecha_fin?->format('d/m/Y') ?? '-' }}</td>
                        <td class="text-center">{{ $solicitud->dias_solicitados ?? '-' }}</td>
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
        Tribunal de Cuentas — Sistema de Gestión de Licencias — {{ now()->format('Y') }}
    </div>
</body>
</html>
