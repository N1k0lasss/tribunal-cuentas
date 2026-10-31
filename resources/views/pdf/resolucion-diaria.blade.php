<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resolución N° {{ $resolucion->numero }}</title>
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
            padding: 18px 22px;
        }

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

        .header-logo {
            width: 90px;
        }

        .header-logo img {
            max-height: 74px;
            width: auto;
        }

        .header-text {
            text-align: right;
        }

        .header-text h1 {
            font-size: 18px;
            font-weight: bold;
            color: #1A365D;
            margin-bottom: 3px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .header-text h2 {
            font-size: 12px;
            font-weight: 600;
            color: #6B1C5E;
            margin-bottom: 4px;
        }

        .header-text .meta {
            font-size: 9px;
            color: #64748b;
        }

        .title-bar {
            background-color: #1A365D;
            color: #ffffff;
            padding: 9px 12px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            margin-bottom: 8px;
        }

        .resolucion-bar {
            background-color: #f0fdf4;
            border: 1px solid #86efac;
            border-left: 4px solid #16a34a;
            color: #065f46;
            padding: 8px 12px;
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 0.3px;
            margin-bottom: 14px;
        }

        .section-title {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6B1C5E;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
            margin: 16px 0 8px 0;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        .data-table th {
            background-color: #f1f5f9;
            color: #1A365D;
            font-weight: 600;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 5px 5px;
            text-align: left;
            border: 1px solid #e2e8f0;
        }

        .data-table td {
            padding: 5px 5px;
            border: 1px solid #e2e8f0;
            font-size: 8.5px;
            color: #475569;
            vertical-align: top;
        }

        .data-table tbody tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        .data-table .numero {
            text-align: center;
            width: 26px;
        }

        .data-table .dias {
            text-align: center;
            width: 40px;
        }

        .detalle-box {
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            padding: 8px 10px;
            font-size: 10px;
            color: #475569;
            min-height: 34px;
        }

        .firmas-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        .firmas-table td {
            width: 33.33%;
            vertical-align: top;
            padding: 0 6px;
            border: none;
        }

        .firma-card {
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 10px;
            min-height: 100px;
            text-align: center;
        }

        .firma-card .rol {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #1A365D;
            margin-bottom: 6px;
        }

        .firma-card .linea {
            border-top: 1px solid #94a3b8;
            margin: 46px 12px 4px 12px;
        }

        .firma-card .estado {
            font-size: 8px;
            color: #64748b;
        }

        .footer {
            margin-top: 26px;
            padding-top: 8px;
            border-top: 1px solid #cbd5e1;
            text-align: center;
            font-size: 8px;
            color: #64748b;
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
                <h2>Provincia de Santiago del Estero</h2>
                <div class="meta">Sistema de Gestión de Licencias</div>
            </td>
        </tr>
    </table>

    <div class="title-bar">
        Resolución del día
    </div>

    <div class="resolucion-bar">
        Resolución N° {{ $resolucion->numero }}
        &nbsp;|&nbsp; {{ $resolucion->fecha_emision?->format('d/m/Y') ?? '—' }}
    </div>

    <div class="section-title">Licencias incluidas</div>
    <table class="data-table">
        <thead>
            <tr>
                <th class="numero">N°</th>
                <th>Apellido y Nombre</th>
                <th>DNI</th>
                <th>Área</th>
                <th>Tipo de licencia</th>
                <th>Artículo</th>
                <th>Período</th>
                <th class="dias">Días</th>
            </tr>
        </thead>
        <tbody>
            @forelse($resolucion->solicitudes as $indice => $solicitud)
                <tr>
                    <td class="numero">{{ $indice + 1 }}</td>
                    <td>{{ $solicitud->agente?->apellido }}, {{ $solicitud->agente?->nombre }}</td>
                    <td>{{ $solicitud->agente?->dni ?? '—' }}</td>
                    <td>{{ $solicitud->agente?->area?->nombre ?? '—' }}</td>
                    <td>{{ $solicitud->tipoSolicitud?->nombre ?? '—' }}</td>
                    <td>{{ $solicitud->tipoSolicitud?->articulo_legal ?? '—' }}</td>
                    <td>
                        {{ $solicitud->fecha_inicio?->format('d/m/Y') ?? '—' }}
                        al
                        {{ $solicitud->fecha_fin?->format('d/m/Y') ?? '—' }}
                    </td>
                    <td class="dias">{{ $solicitud->dias_solicitados ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: #64748b;">
                        No hay licencias asociadas a esta resolución.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if(filled($resolucion->detalle))
        <div class="section-title">Detalle</div>
        <div class="detalle-box">
            {{ $resolucion->detalle }}
        </div>
    @endif

    <div class="section-title">Firma</div>
    <table class="firmas-table" style="width: 50%; margin-left: 0;">
        <tr>
            <td style="width: 100%;">
                <div class="firma-card">
                    <div class="rol">Secretaría General</div>
                    <div class="linea"></div>
                    <div class="estado">Firma y aclaración</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Documento generado el {{ now(config('app.business_timezone'))->format('d/m/Y H:i') }} — Resolución N° {{ $resolucion->numero }}<br>
        Tribunal de Cuentas Provincia de Santiago del Estero — Sistema de Gestión de Licencias
    </div>
</body>
</html>
