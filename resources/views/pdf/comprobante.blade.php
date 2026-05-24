@php
    $ipnLogo = public_path('ipn.png');
    $cicsLogo = public_path('logocicsstf.png');
    $estadoColor = match ($incidencia->estado->color()) {
        'green' => ['bg' => '#dcfce7', 'text' => '#166534', 'border' => '#86efac'],
        'red' => ['bg' => '#fee2e2', 'text' => '#991b1b', 'border' => '#fca5a5'],
        'orange' => ['bg' => '#ffedd5', 'text' => '#9a3412', 'border' => '#fdba74'],
        'purple' => ['bg' => '#f3e8ff', 'text' => '#6b21a8', 'border' => '#d8b4fe'],
        'blue' => ['bg' => '#dbeafe', 'text' => '#1e40af', 'border' => '#93c5fd'],
        default => ['bg' => '#fef9c3', 'text' => '#854d0e', 'border' => '#fde047'],
    };
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Comprobante de Incidencia {{ $incidencia->folio }}</title>
    <style>
        @page { margin: 24px 28px; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            color: #1f2937;
            background: #ffffff;
        }
        .page {
            border: 1px solid #d1d5db;
            position: relative;
        }
        .top-band {
            height: 9px;
            background: #6d1231;
        }
        .gold-band {
            height: 4px;
            background: #c9a227;
        }
        .header {
            padding: 18px 24px 14px;
            border-bottom: 1px solid #e5e7eb;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .logo-cell {
            width: 82px;
            vertical-align: middle;
        }
        .logo-ipn {
            width: 58px;
            height: auto;
        }
        .logo-cics {
            width: 72px;
            height: auto;
        }
        .institution {
            text-align: center;
            vertical-align: middle;
            padding: 0 10px;
        }
        .institution-name {
            font-size: 15px;
            font-weight: bold;
            letter-spacing: 0.03em;
            color: #6d1231;
            text-transform: uppercase;
        }
        .institution-subtitle {
            margin-top: 3px;
            font-size: 10px;
            color: #374151;
            text-transform: uppercase;
        }
        .unit-name {
            margin-top: 5px;
            font-size: 11px;
            font-weight: bold;
            color: #111827;
        }
        .document-title {
            margin-top: 12px;
            padding: 7px 10px;
            border-top: 1px solid #e5e7eb;
            border-bottom: 1px solid #e5e7eb;
            background: #f9fafb;
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            color: #111827;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }
        .content {
            padding: 18px 24px 16px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .folio-panel {
            width: 42%;
            padding: 13px 14px;
            border: 1px solid #d6c17a;
            background: #fffdf4;
            vertical-align: top;
        }
        .status-panel {
            padding: 13px 14px;
            border: 1px solid #e5e7eb;
            background: #ffffff;
            vertical-align: top;
        }
        .small-label {
            font-size: 8px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 4px;
        }
        .folio-value {
            font-family: Courier New, monospace;
            font-size: 24px;
            font-weight: bold;
            color: #6d1231;
            line-height: 1.1;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border: 1px solid {{ $estadoColor['border'] }};
            border-radius: 14px;
            background: {{ $estadoColor['bg'] }};
            color: {{ $estadoColor['text'] }};
            font-size: 10px;
            font-weight: bold;
        }
        .summary-note {
            margin-top: 8px;
            font-size: 9px;
            color: #4b5563;
            line-height: 1.5;
        }
        .section {
            margin-bottom: 14px;
        }
        .section-title {
            padding: 6px 9px;
            background: #6d1231;
            color: #ffffff;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }
        .section-body {
            border: 1px solid #e5e7eb;
            border-top: none;
            padding: 10px 11px 6px;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
        }
        .details-table td {
            width: 50%;
            vertical-align: top;
            padding: 0 12px 9px 0;
        }
        .field-label {
            font-size: 8px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 3px;
        }
        .field-value {
            font-size: 11px;
            color: #111827;
            font-weight: bold;
            line-height: 1.35;
        }
        .field-value.regular {
            font-weight: normal;
        }
        .field-value.mono {
            font-family: Courier New, monospace;
            font-size: 11px;
        }
        .description-box {
            min-height: 48px;
            padding: 10px 11px;
            border: 1px solid #e5e7eb;
            background: #f9fafb;
            font-size: 10.5px;
            color: #374151;
            line-height: 1.55;
        }
        .tracking-box {
            border: 1px solid #d6c17a;
            background: #fffdf4;
            padding: 11px 13px;
            color: #374151;
            line-height: 1.5;
        }
        .tracking-box strong {
            color: #6d1231;
        }
        .generated-box {
            margin-top: 10px;
            text-align: right;
            font-size: 9px;
            color: #6b7280;
            line-height: 1.45;
        }
        .footer {
            margin: 14px 24px 0;
            padding-top: 9px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 8px;
            color: #6b7280;
            line-height: 1.45;
        }
    </style>
</head>
<body>
<div class="page">
    <div class="top-band"></div>
    <div class="gold-band"></div>

    <div class="header">
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    @if (file_exists($ipnLogo))
                        <img class="logo-ipn" src="{{ $ipnLogo }}" alt="IPN">
                    @endif
                </td>
                <td class="institution">
                    <div class="institution-name">Instituto Politécnico Nacional</div>
                    <div class="institution-subtitle">La Técnica al Servicio de la Patria</div>
                    <div class="unit-name">Centro Interdisciplinario de Ciencias de la Salud - Unidad Santo Tomás</div>
                </td>
                <td class="logo-cell" style="text-align: right;">
                    @if (file_exists($cicsLogo))
                        <img class="logo-cics" src="{{ $cicsLogo }}" alt="CICS UST">
                    @endif
                </td>
            </tr>
        </table>
        <div class="document-title">Comprobante de Registro de Incidencia</div>
    </div>

    <div class="content">
        <table class="summary-table">
            <tr>
                <td class="folio-panel">
                    <div class="small-label">Folio institucional</div>
                    <div class="folio-value">{{ $incidencia->folio }}</div>
                    <div class="summary-note">Conserva este folio para el seguimiento de tu trámite.</div>
                </td>
                <td style="width: 12px;"></td>
                <td class="status-panel">
                    <div class="small-label">Estatus actual</div>
                    <span class="status-badge">{{ $incidencia->estado->label() }}</span>
                    <div class="summary-note">
                        Registro recibido por el sistema institucional de incidencias académicas y administrativas.
                    </div>
                </td>
            </tr>
        </table>

        <div class="section">
            <div class="section-title">Datos de la incidencia</div>
            <div class="section-body">
                <table class="details-table">
                    <tr>
                        <td>
                            <div class="field-label">Fecha de registro</div>
                            <div class="field-value">{{ $incidencia->created_at->timezone('America/Mexico_City')->format('d/m/Y H:i') }} hrs</div>
                        </td>
                        <td>
                            <div class="field-label">Fecha de incidencia</div>
                            <div class="field-value">{{ $incidencia->fecha_incidencia->format('d/m/Y') }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="field-label">Área de adscripción</div>
                            <div class="field-value">{{ $incidencia->area?->nombre ?? 'Sin área registrada' }}</div>
                        </td>
                        <td>
                            <div class="field-label">Tipo de incidencia</div>
                            <div class="field-value">{{ $incidencia->tipo_incidencia->label() }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="field-label">Tipo de solicitante</div>
                            <div class="field-value">{{ $incidencia->tipo_solicitante->label() }}</div>
                        </td>
                        <td>
                            <div class="field-label">Hora de incidencia</div>
                            <div class="field-value">{{ $incidencia->hora_incidencia ?: 'No especificada' }}</div>
                        </td>
                    </tr>
                    @if ($incidencia->tipo_incidencia === \App\Enums\TipoIncidencia::Retardo && $incidencia->minutos_retardo)
                        <tr>
                            <td>
                                <div class="field-label">Minutos de retardo</div>
                                <div class="field-value">{{ $incidencia->minutos_retardo }} minutos</div>
                            </td>
                            <td></td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Datos del empleado</div>
            <div class="section-body">
                <table class="details-table">
                    <tr>
                        <td>
                            <div class="field-label">Número de empleado</div>
                            <div class="field-value mono">{{ $incidencia->numero_empleado }}</div>
                        </td>
                        <td>
                            <div class="field-label">Nombre completo</div>
                            <div class="field-value">{{ $incidencia->reportante_nombre }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div class="field-label">Correo electrónico</div>
                            <div class="field-value regular">{{ $incidencia->email_reportante ?: 'No registrado' }}</div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Descripción del trámite</div>
            <div class="description-box">
                {{ $incidencia->descripcion ?: 'Sin descripción adicional registrada.' }}
            </div>
        </div>

        @if ($incidencia->motivo_rechazo)
            <div class="section">
                <div class="section-title">Observación de resolución</div>
                <div class="description-box">{{ $incidencia->motivo_rechazo }}</div>
            </div>
        @endif

        <div class="section">
            <div class="section-title">Seguimiento</div>
            <div class="tracking-box">
                Consulta el estado de esta incidencia desde la sección de <strong>Seguimiento</strong> usando el folio
                <strong>{{ $incidencia->folio }}</strong> y tu número de empleado.
                @if ($incidencia->email_reportante)
                    Las actualizaciones relevantes también se enviarán a <strong>{{ $incidencia->email_reportante }}</strong>.
                @endif
            </div>
        </div>

        <div class="generated-box">
            Documento generado el {{ now()->timezone('America/Mexico_City')->format('d/m/Y H:i') }} hrs<br>
            Sistema de Incidencias CICS UST<br>
            {{ config('app.name') }}
        </div>
    </div>

    <div class="footer">
        Este comprobante acredita el registro de la solicitud en el sistema; no sustituye la resolución final de la incidencia.
        Instituto Politécnico Nacional - Centro Interdisciplinario de Ciencias de la Salud, Unidad Santo Tomás.
    </div>
</div>
</body>
</html>
