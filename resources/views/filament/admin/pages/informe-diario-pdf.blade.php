<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe Diario de Novedades - {{ $fecha }}</title>
    <style>
        @page {
            margin: 25px 30px 40px 30px;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.4;
            background-color: #ffffff;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border-bottom: 2px solid #1A365D;
            padding-bottom: 10px;
        }
        .header-title {
            text-align: left;
            vertical-align: middle;
            padding-left: 15px;
        }
        .header-title h1 {
            margin: 0;
            font-size: 18px;
            color: #1A365D;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header-title h2 {
            margin: 2px 0 0 0;
            font-size: 12px;
            color: #475569;
            font-weight: normal;
        }
        .header-meta {
            text-align: right;
            vertical-align: middle;
            font-size: 10px;
            color: #64748b;
        }

        .meta-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 15px;
            margin-bottom: 20px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 3px 0;
        }
        .meta-label {
            font-weight: bold;
            color: #334155;
        }

        /* Summary Cards */
        .summary-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px;
            margin-bottom: 20px;
        }
        .summary-card {
            background: #f1f5f9;
            border-radius: 6px;
            padding: 8px 10px;
            text-align: center;
            border: 1px solid #cbd5e1;
        }
        .summary-card.presente { border-top: 3px solid #10b981; }
        .summary-card.licencia { border-top: 3px solid #3b82f6; }
        .summary-card.falta { border-top: 3px solid #f59e0b; }
        .summary-card.ausente { border-top: 3px solid #ef4444; }
        .summary-card.tardanza { border-top: 3px solid #8b5cf6; }

        .summary-num {
            font-size: 16px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 2px;
        }
        .summary-title {
            font-size: 9px;
            text-transform: uppercase;
            color: #475569;
            font-weight: bold;
        }

        /* Detail Table */
        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #1A365D;
            margin-bottom: 8px;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 4px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .data-table th {
            background-color: #1A365D;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 6px 8px;
            font-size: 10px;
            text-transform: uppercase;
        }
        .data-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 10px;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }

        /* Badge styles */
        .badge {
            display: inline-block;
            padding: 3px 7px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
        }
        .badge-presente { background-color: #d1fae5; color: #065f46; }
        .badge-licencia { background-color: #dbeafe; color: #1e40af; }
        .badge-falta_justificada { background-color: #fef3c7; color: #92400e; }
        .badge-ausente_sin_aviso { background-color: #fee2e2; color: #991b1b; }
        .badge-tardanza { background-color: #ede9fe; color: #5b21b6; }

        /* Notes Box */
        .notes-box {
            background-color: #fffbebfb;
            border: 1px solid #fef08a;
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 30px;
        }
        .notes-title {
            font-weight: bold;
            color: #854d0e;
            margin-bottom: 4px;
        }

        /* Signatures */
        .signature-table {
            width: 100%;
            margin-top: 50px;
            border-collapse: collapse;
        }
        .signature-cell {
            width: 45%;
            text-align: center;
            vertical-align: bottom;
        }
        .signature-line {
            border-top: 1px solid #64748b;
            width: 80%;
            margin: 0 auto 5px auto;
        }
        .signature-title {
            font-size: 10px;
            font-weight: bold;
            color: #334155;
        }
        .signature-sub {
            font-size: 9px;
            color: #64748b;
        }

        .footer-page {
            position: fixed;
            bottom: -20px;
            left: 0;
            right: 0;
            font-size: 8px;
            color: #94a3b8;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            padding-top: 5px;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <table class="header-table">
        <tr>
            <td class="header-title">
                <h1>TRIBUNAL DE CUENTAS</h1>
                <h2>Informe Diario de Novedades y Asistencia de Personal</h2>
            </td>
            <td class="header-meta">
                <strong>Fecha del Informe:</strong> {{ $fechaFormatted }}<br>
                <strong>Emisión:</strong> {{ now()->format('d/m/Y H:i') }} hs
            </td>
        </tr>
    </table>

    <!-- Metadata Banner -->
    <div class="meta-box">
        <table class="meta-table">
            <tr>
                <td width="50%">
                    <span class="meta-label">Emisor / Generado por:</span> {{ $usuarioNombre }} (Administración de Personal)
                </td>
                <td width="50%" style="text-align: right;">
                    <span class="meta-label">Destinatario:</span> <strong>{{ $destinatario }}</strong>
                </td>
            </tr>
        </table>
    </div>

    <!-- Counters Grid -->
    <table class="summary-table">
        <tr>
            <td class="summary-card">
                <div class="summary-title">Total Plantilla</div>
                <div class="summary-num">{{ $totalAgentes }}</div>
            </td>
            <td class="summary-card presente">
                <div class="summary-title">Presentes</div>
                <div class="summary-num">{{ $cantidades['presente'] }}</div>
            </td>
            <td class="summary-card licencia">
                <div class="summary-title">Licencias</div>
                <div class="summary-num">{{ $cantidades['licencia'] }}</div>
            </td>
            <td class="summary-card falta">
                <div class="summary-title">Faltas Just.</div>
                <div class="summary-num">{{ $cantidades['falta_justificada'] }}</div>
            </td>
            <td class="summary-card ausente">
                <div class="summary-title">Ausentes s/Aviso</div>
                <div class="summary-num">{{ $cantidades['ausente_sin_aviso'] }}</div>
            </td>
            <td class="summary-card tardanza">
                <div class="summary-title">Tardanzas</div>
                <div class="summary-num">{{ $cantidades['tardanza'] }}</div>
            </td>
        </tr>
    </table>

    <!-- Data Table -->
    <div class="section-title">Detalle General de Novedades de Agentes</div>
    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="25%">Agente / DNI</th>
                <th width="20%">Área / Sector</th>
                <th width="18%">Estado / Novedad</th>
                <th width="12%">Horario / Demora</th>
                <th width="20%">Observaciones / Detalle</th>
            </tr>
        </thead>
        <tbody>
            @forelse($detalles as $index => $det)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $det['agente_nombre'] }}</strong><br>
                        <span style="font-size: 9px; color: #64748b;">DNI: {{ $det['agente_dni'] }}</span>
                    </td>
                    <td>{{ $det['area_nombre'] }}</td>
                    <td>
                        <span class="badge badge-{{ $det['tipo_novedad'] }}">
                            {{ $det['tipo_novedad_label'] }}
                        </span>
                        @if(!empty($det['tipo_licencia_nombre']))
                            <br><span style="font-size: 8px; color: #1e40af;">({{ $det['tipo_licencia_nombre'] }})</span>
                        @endif
                    </td>
                    <td>
                        @if($det['tipo_novedad'] === 'tardanza')
                            @if(!empty($det['hora_llegada']))
                                <strong>{{ $det['hora_llegada'] }} hs</strong>
                            @endif
                            @if(!empty($det['minutos_tardanza']))
                                <br><span style="color: #6b21a8; font-size: 8px;">(+{{ $det['minutos_tardanza'] }} min)</span>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $det['observaciones'] ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #64748b; padding: 15px;">
                        No se registraron detalles para esta fecha.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if(!empty($observacionesGenerales))
        <div class="notes-box">
            <div class="notes-title">Observaciones Generales del Día:</div>
            <div>{{ $observacionesGenerales }}</div>
        </div>
    @endif

    <!-- Signatures -->
    <table class="signature-table">
        <tr>
            <td class="signature-cell">
                <div class="signature-line"></div>
                <div class="signature-title">Emisión / Confección</div>
                <div class="signature-sub">Administrador de Personal</div>
            </td>
            <td width="10%"></td>
            <td class="signature-cell">
                <div class="signature-line"></div>
                <div class="signature-title">Recepción / Conformidad</div>
                <div class="signature-sub">Secretaría General - TCSE</div>
            </td>
        </tr>
    </table>

    <div class="footer-page">
        Documento oficial emitido por el Sistema de Gestión del Tribunal de Cuentas. Reservado exclusivamente para Secretaría General.
    </div>

</body>
</html>
