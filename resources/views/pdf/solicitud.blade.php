<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitud N° {{ $solicitud->id }}</title>
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
            line-height: 1.45;
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
            padding: 8px 12px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            margin-bottom: 14px;
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
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            padding: 6px 8px;
            text-align: left;
            border: 1px solid #e2e8f0;
            width: 22%;
        }

        .data-table td {
            padding: 6px 8px;
            border: 1px solid #e2e8f0;
            font-size: 10px;
            color: #475569;
        }

        .motivo-box {
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
            min-height: 110px;
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

        .firma-card.firmada {
            border-color: #86efac;
            background-color: #f0fdf4;
        }

        .firma-card.firmada .estado {
            color: #065f46;
            font-weight: bold;
        }

        .firma-datos {
            font-size: 9px;
            color: #065f46;
            margin-top: 30px;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .badge-pendiente { background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .badge-en_revision { background-color: #e0e7ff; color: #3730a3; border: 1px solid #c7d2fe; }
        .badge-aprobado { background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .badge-rechazado { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

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
    @php
        $etapasLabel = [
            'jefatura' => 'Jefatura',
            'secretaria' => 'Secretaría General',
            'direccion' => 'Presidencia',
        ];

        $aprobacionesPorEtapa = $solicitud->aprobaciones->keyBy('etapa');
        $estadoLegible = str_replace('_', ' ', ucfirst($solicitud->estado));
    @endphp

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
        Solicitud de Licencia N° {{ str_pad((string) $solicitud->id, 6, '0', STR_PAD_LEFT) }}
    </div>

    @php
        // El número puede venir del query string (aún sin persistir) o de la
        // resolución ya guardada en la base.
        $numeroResolucionMostrar = filled($numeroResolucion ?? null)
            ? $numeroResolucion
            : $solicitud->resolucion?->numero;
        $fechaResolucionMostrar = $solicitud->resolucion?->fecha_emision;
    @endphp
    @if(filled($numeroResolucionMostrar))
        <div class="resolucion-bar">
            Resolución N° {{ $numeroResolucionMostrar }}@if($fechaResolucionMostrar) &nbsp;|&nbsp; {{ $fechaResolucionMostrar->format('d/m/Y') }}@endif
        </div>
    @endif

    <table class="data-table">
        <tr>
            <th>Fecha de solicitud</th>
            <td>{{ $solicitud->fecha_solicitud?->format('d/m/Y') ?? '—' }}</td>
            <th>Estado</th>
            <td>
                <span class="badge badge-{{ $solicitud->estado }}">{{ $estadoLegible }}</span>
            </td>
        </tr>
    </table>

    <div class="section-title">Datos del Agente</div>
    <table class="data-table">
        <tr>
            <th>Apellido y Nombre</th>
            <td>{{ $solicitud->agente?->apellido }}, {{ $solicitud->agente?->nombre }}</td>
            <th>DNI</th>
            <td>{{ $solicitud->agente?->dni ?? '—' }}</td>
        </tr>
        <tr>
            <th>Cargo</th>
            <td>{{ $solicitud->agente?->cargo ?? '—' }}</td>
            <th>Situación de revista</th>
            <td>{{ $solicitud->agente?->situacion_revista ?? '—' }}</td>
        </tr>
        <tr>
            <th>Área</th>
            <td>{{ $solicitud->agente?->area?->nombre ?? '—' }}</td>
            <th>Fecha de ingreso</th>
            <td>{{ $solicitud->agente?->fecha_ingreso?->format('d/m/Y') ?? '—' }}</td>
        </tr>
    </table>

    <div class="section-title">Datos de la Solicitud</div>
    <table class="data-table">
        <tr>
            <th>Tipo de licencia</th>
            <td>{{ $solicitud->tipoSolicitud?->nombre ?? '—' }}</td>
            <th>Categoría</th>
            <td>{{ $solicitud->tipoSolicitud?->categoria ?? '—' }}</td>
        </tr>
        <tr>
            <th>Artículo legal</th>
            <td>{{ $solicitud->tipoSolicitud?->articulo_legal ?? '—' }}</td>
            <th>Días solicitados</th>
            <td>{{ $solicitud->dias_solicitados ?? '—' }}</td>
        </tr>
        <tr>
            <th>Desde</th>
            <td>{{ $solicitud->fecha_inicio?->format('d/m/Y') ?? '—' }}</td>
            <th>Hasta</th>
            <td>{{ $solicitud->fecha_fin?->format('d/m/Y') ?? '—' }}</td>
        </tr>
        @if($solicitud->hora_inicio || $solicitud->hora_fin)
            <tr>
                <th>Hora desde</th>
                <td>{{ $solicitud->hora_inicio ?? '—' }}</td>
                <th>Hora hasta</th>
                <td>{{ $solicitud->hora_fin ?? '—' }}</td>
            </tr>
        @endif
    </table>

    <div class="section-title">Motivo / Observaciones</div>
    <div class="motivo-box">
        {{ $solicitud->motivo ?: 'Sin observaciones.' }}
    </div>

    @if($solicitud->documentos->where('tipo_documento', '!=', \App\Models\Documento::TIPO_SOLICITUD_FIRMADA)->isNotEmpty())
        <div class="section-title">Documentos adjuntos</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 45%;">Documento</th>
                    <th style="width: 25%;">Tipo</th>
                    <th style="width: 30%;">Descripción</th>
                </tr>
            </thead>
            <tbody>
                @foreach($solicitud->documentos->where('tipo_documento', '!=', \App\Models\Documento::TIPO_SOLICITUD_FIRMADA) as $documento)
                    <tr>
                        <td>{{ $documento->nombre_archivo }}</td>
                        <td>{{ $documento->tipo_documento }}</td>
                        <td>{{ $documento->descripcion ?: '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="section-title">Firmas</div>
    <table class="firmas-table">
        <tr>
            @foreach($solicitud->flujoEtapas() as $etapa)
                @php
                    $aprobacion = $aprobacionesPorEtapa->get($etapa);
                    $firmada = $aprobacion && $aprobacion->estado === 'aprobado';
                @endphp
                <td>
                    <div class="firma-card {{ $firmada ? 'firmada' : '' }}">
                        <div class="rol">{{ $etapasLabel[$etapa] ?? $etapa }}</div>
                        @if($firmada)
                            <div class="firma-datos">
                                {{ $aprobacion->aprobador?->apellido }}, {{ $aprobacion->aprobador?->nombre }}<br>
                                {{ $aprobacion->fecha?->format('d/m/Y') ?? '' }}
                            </div>
                            <div class="estado">Firmado digitalmente</div>
                        @else
                            <div class="linea"></div>
                            <div class="estado">Firma y aclaración</div>
                        @endif
                    </div>
                </td>
            @endforeach
        </tr>
    </table>

    <div class="footer">
        Documento generado el {{ now()->format('d/m/Y H:i') }} — Solicitud N° {{ str_pad((string) $solicitud->id, 6, '0', STR_PAD_LEFT) }}<br>
        Tribunal de Cuentas Provincia de Santiago del Estero — Sistema de Gestión de Licencias
    </div>
</body>
</html>
