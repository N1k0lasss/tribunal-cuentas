<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Estadísticas de Licencias {{ $stats['anio'] }}</title>
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

        .header {
            width: 100%;
            margin-bottom: 16px;
            border-bottom: 3px solid #1A365D;
            padding-bottom: 10px;
        }

        .header h1 {
            font-size: 17px;
            color: #1A365D;
            margin-bottom: 4px;
            text-transform: uppercase;
        }

        .header p {
            font-size: 10px;
            color: #64748b;
        }

        .filters {
            background: #f1f5f9;
            border-left: 4px solid #1A365D;
            padding: 10px 12px;
            margin-bottom: 16px;
            font-size: 10px;
        }

        .kpis {
            margin-bottom: 16px;
        }

        .kpis table {
            width: 100%;
            border-collapse: collapse;
        }

        .kpis th,
        .kpis td {
            border: 1px solid #e2e8f0;
            padding: 8px;
            text-align: center;
        }

        .kpis th {
            background: #1A365D;
            color: #fff;
            font-size: 10px;
            text-transform: uppercase;
        }

        .kpis td {
            font-size: 14px;
            font-weight: bold;
            color: #1f2937;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #1A365D;
            margin-bottom: 8px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            background: #1A365D;
            color: #fff;
            border: 1px solid #1A365D;
            font-size: 10px;
            text-transform: uppercase;
            padding: 8px;
            text-align: left;
        }

        .data-table td {
            border: 1px solid #e2e8f0;
            padding: 8px;
            font-size: 10px;
        }

        .data-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .text-center {
            text-align: center;
        }

        .footer {
            margin-top: 24px;
            padding-top: 8px;
            border-top: 1px solid #cbd5e1;
            text-align: center;
            font-size: 9px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Tribunal de Cuentas - Estadísticas de Licencias</h1>
        <p>Generado el {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="filters">
        <strong>Filtros aplicados:</strong>
        Año: {{ $stats['anio'] }} |
        Agente: {{ $stats['agente_label'] }}
        @if($stats['estado'])
            | Estado: {{ ucfirst(str_replace('_', ' ', $stats['estado'])) }}
        @endif
    </div>

    <div class="kpis">
        <table>
            <thead>
                <tr>
                    <th>Total Solicitudes</th>
                    <th>Total Días</th>
                    <th>Tipos Utilizados</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $stats['total_solicitudes'] }}</td>
                    <td>{{ $stats['total_dias'] }}</td>
                    <td>{{ $stats['tipos_utilizados'] }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section-title">Detalle por tipo de licencia</div>

    @if($stats['por_tipo']->isEmpty())
        <p style="font-size: 11px; color: #64748b;">No hay datos para los filtros seleccionados.</p>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 46%;">Tipo de Licencia</th>
                    <th style="width: 18%;" class="text-center">Cantidad</th>
                    <th style="width: 18%;" class="text-center">Días Totales</th>
                    <th style="width: 18%;" class="text-center">Promedio Días</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stats['por_tipo'] as $row)
                    <tr>
                        <td>{{ $row['tipo'] }}</td>
                        <td class="text-center">{{ $row['cantidad'] }}</td>
                        <td class="text-center">{{ $row['dias_totales'] }}</td>
                        <td class="text-center">{{ number_format($row['promedio_dias'], 1, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        Tribunal de Cuentas Provincia de Santiago del Estero - Sistema de Gestión de Licencias - {{ now()->format('Y') }}
    </div>
</body>
</html>
