<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Anual de Licencias {{ $anio }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            color: #334155;
            line-height: 1.4;
            padding: 25px;
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
            max-height: 70px;
            width: auto;
        }

        .header-text {
            text-align: right;
        }

        .header-text h1 {
            font-size: 16px;
            font-weight: bold;
            color: #1A365D;
            margin-bottom: 4px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .header-text h2 {
            font-size: 12px;
            font-weight: 600;
            color: #6B1C5E;
            margin-bottom: 8px;
        }

        .header-text .meta {
            font-size: 9px;
            color: #64748b;
        }

        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #1A365D;
            border-bottom: 1px dashed #cbd5e1;
            padding-bottom: 5px;
            margin-top: 20px;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Info box */
        .info-box {
            background-color: #f8fafc;
            border-left: 4px solid #1A365D;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 0 4px 4px 0;
        }

        .info-grid {
            width: 100%;
        }

        .info-grid td {
            padding: 3px 5px;
            border: none;
            font-size: 9.5px;
        }

        /* Tables */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .data-table th {
            background-color: #1A365D;
            color: #ffffff;
            font-weight: 600;
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 8px 6px;
            text-align: left;
            border: 1px solid #1A365D;
        }

        .data-table td {
            padding: 7px 6px;
            border: 1px solid #e2e8f0;
            font-size: 9px;
            vertical-align: middle;
            color: #475569;
        }

        .data-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .data-table tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }

        /* Totals / Stats Grid */
        .stats-summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .stats-summary-table td {
            border: 1px solid #cbd5e1;
            padding: 10px;
            width: 25%;
            background-color: #f8fafc;
            text-align: center;
        }

        .stats-val {
            font-size: 14px;
            font-weight: bold;
            color: #1A365D;
            margin-bottom: 3px;
        }

        .stats-lbl {
            font-size: 8px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            text-align: center;
            font-size: 8px;
            color: #94a3b8;
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
                <h2>Informe Estadístico Anual de Licencias</h2>
                <div class="meta">
                    Período Analizado: Año Fiscal {{ $anio }} <br>
                    Generado el {{ now()->format('d/m/Y H:i') }}
                </div>
            </td>
        </tr>
    </table>

    <div class="info-box">
        <table class="info-grid">
            <tr>
                <td style="width: 15%;"><strong>Año de Gestión:</strong></td>
                <td style="width: 35%;">{{ $anio }}</td>
                <td style="width: 25%;"><strong>Licencia más solicitada:</strong></td>
                <td style="width: 25%;">{{ $data['mostRequestedLicense'] }}</td>
            </tr>
            <tr>
                <td><strong>Tipo de Reporte:</strong></td>
                <td>Consolidado Anual</td>
                <td><strong>Área con mayor demanda:</strong></td>
                <td>{{ $data['mostActiveArea'] }}</td>
            </tr>
        </table>
    </div>

    <div class="section-title">Resumen de Indicadores Clave</div>
    <table class="stats-summary-table">
        <tr>
            <td>
                <div class="stats-val">{{ $data['totalSolicitudes'] }}</div>
                <div class="stats-lbl">Total Solicitudes</div>
            </td>
            <td>
                <div class="stats-val">{{ $data['totalDias'] }}</div>
                <div class="stats-lbl">Días Solicitados</div>
            </td>
            <td>
                <div class="stats-val">{{ $data['totalAprobadas'] }}</div>
                <div class="stats-lbl">Solicitudes Aprobadas</div>
            </td>
            <td>
                <div class="stats-val">
                    @if($data['totalSolicitudes'] > 0)
                        {{ round(($data['totalAprobadas'] / $data['totalSolicitudes']) * 100, 1) }}%
                    @else
                        0%
                    @endif
                </div>
                <div class="stats-lbl">Tasa de Aprobación</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Distribución por Tipo de Licencia</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">#</th>
                <th style="width: 50%;">Tipo de Licencia / Concepto</th>
                <th style="width: 15%;" class="text-center">Solicitudes</th>
                <th style="width: 15%;" class="text-center">Días Totales</th>
                <th style="width: 15%;" class="text-center">Porcentaje (%)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['licencias'] as $index => $licencia)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="font-bold">{{ $licencia['nombre'] }}</td>
                    <td class="text-center">{{ $licencia['cantidad'] }}</td>
                    <td class="text-center">{{ $licencia['dias'] }}</td>
                    <td class="text-center">{{ $licencia['porcentaje'] }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="padding: 20px;">No se registraron solicitudes de licencias en el período.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">Distribución de Solicitudes por Área de Trabajo</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">#</th>
                <th style="width: 50%;">Área / Sector</th>
                <th style="width: 15%;" class="text-center">Solicitudes</th>
                <th style="width: 15%;" class="text-center">Días Totales</th>
                <th style="width: 15%;" class="text-center">Porcentaje (%)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['areas'] as $index => $area)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="font-bold">{{ $area['nombre'] }}</td>
                    <td class="text-center">{{ $area['cantidad'] }}</td>
                    <td class="text-center">{{ $area['dias'] }}</td>
                    <td class="text-center">{{ $area['porcentaje'] }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="padding: 20px;">No se registraron solicitudes de licencias por áreas en el período.</td>
                </tr>
            @endforelse
        </tbody>
    </table>



    <div class="footer">
        Tribunal de Cuentas Provincia de Santiago del Estero — Sistema de Gestión de Licencias — {{ now()->format('Y') }}
    </div>
</body>
</html>
