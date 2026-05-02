<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Comprobante de Incidencia {{ $incidencia->folio }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            color: #1f2937;
            background: #fff;
        }
        .page {
            padding: 40px 48px;
        }
        .header {
            border-bottom: 2px solid #2563eb;
            padding-bottom: 16px;
            margin-bottom: 24px;
        }
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .org-name {
            font-size: 18px;
            font-weight: bold;
            color: #1e40af;
        }
        .org-sub {
            font-size: 11px;
            color: #6b7280;
            margin-top: 2px;
        }
        .folio-box {
            text-align: right;
        }
        .folio-label {
            font-size: 10px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .folio-value {
            font-size: 22px;
            font-weight: bold;
            font-family: Courier New, monospace;
            color: #1e40af;
        }
        .doc-title {
            font-size: 14px;
            font-weight: bold;
            color: #374151;
            margin-top: 12px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #2563eb;
            border-bottom: 1px solid #dbeafe;
            padding-bottom: 4px;
            margin-bottom: 10px;
        }
        .grid-2 {
            width: 100%;
        }
        .grid-2 table {
            width: 100%;
            border-collapse: collapse;
        }
        .grid-2 td {
            width: 50%;
            vertical-align: top;
            padding-bottom: 8px;
            padding-right: 16px;
        }
        .field-label {
            font-size: 10px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 2px;
        }
        .field-value {
            font-size: 12px;
            color: #111827;
            font-weight: 500;
        }
        .field-value.mono {
            font-family: Courier New, monospace;
            font-size: 11px;
            word-break: break-all;
        }
        .badge-estado {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 10px;
            font-weight: bold;
            background: #dbeafe;
            color: #1e40af;
        }
        .descripcion-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 10px 12px;
            font-size: 11px;
            color: #374151;
            line-height: 1.6;
        }
        .seguimiento-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            padding: 12px 14px;
        }
        .seguimiento-box p {
            font-size: 11px;
            color: #1e40af;
            line-height: 1.6;
        }
        .footer {
            margin-top: 32px;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
            text-align: center;
            font-size: 9px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
<div class="page">

    <div class="header">
        <div class="header-top">
            <div>
                <div class="org-name">CICS UST - IPN</div>
                <div class="org-sub">Centro Intersdisciplinario de Ciencias de la Salud</div>
            </div>
            <div class="folio-box">
                <div class="folio-label">Número de folio</div>
                <div class="folio-value">{{ $incidencia->folio }}</div>
            </div>
        </div>
        <div class="doc-title">Comprobante de Registro de Incidencia</div>
    </div>

    <div class="section">
        <div class="section-title">Datos Generales</div>
        <div class="grid-2">
            <table>
                <tr>
                    <td>
                        <div class="field-label">Fecha de registro</div>
                        <div class="field-value">{{ $incidencia->created_at->timezone('America/Mexico_City')->format('d/m/Y H:i') }} hrs</div>
                    </td>
                    <td>
                        <div class="field-label">Estado</div>
                        <div class="field-value">
                            <span class="badge-estado">{{ $incidencia->estado->label() }}</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="field-label">Área</div>
                        <div class="field-value">{{ $incidencia->area?->nombre ?? '—' }}</div>
                    </td>
                    <td>
                        <div class="field-label">Fecha de incidencia</div>
                        <div class="field-value">{{ $incidencia->fecha_incidencia->format('d/m/Y') }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="field-label">Tipo de solicitud</div>
                        <div class="field-value">{{ $incidencia->tipo_solicitante->label() }}</div>
                    </td>
                    <td>
                        <div class="field-label">Tipo de incidencia</div>
                        <div class="field-value">{{ $incidencia->tipo_incidencia->label() }}</div>
                    </td>
                </tr>
                @if ($incidencia->tipo_incidencia === \App\Enums\TipoIncidencia::Retardo && $incidencia->minutos_retardo)
                <tr>
                    <td colspan="2">
                        <div class="field-label">Minutos de retardo</div>
                        <div class="field-value">{{ $incidencia->minutos_retardo }} minutos</div>
                    </td>
                </tr>
                @endif
            </table>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Datos del Empleado</div>
        <div class="grid-2">
            <table>
                <tr>
                    <td>
                        <div class="field-label">Número de empleado</div>
                        <div class="field-value mono">{{ $incidencia->numero_empleado }}</div>
                    </td>
                    <td>
                        <div class="field-label">Nombre</div>
                        <div class="field-value">{{ $incidencia->reportante_nombre }}</div>
                    </td>
                </tr>
                @if ($incidencia->email_reportante)
                <tr>
                    <td colspan="2">
                        <div class="field-label">Correo electrónico</div>
                        <div class="field-value">{{ $incidencia->email_reportante }}</div>
                    </td>
                </tr>
                @endif
            </table>
        </div>
    </div>

    @if ($incidencia->descripcion)
    <div class="section">
        <div class="section-title">Descripción</div>
        <div class="descripcion-box">{{ $incidencia->descripcion }}</div>
    </div>
    @endif

    <div class="section">
        <div class="section-title">Información de Seguimiento</div>
        <div class="seguimiento-box">
            <p>
                Para consultar el estado de tu incidencia, visita la sección de <strong>Seguimiento</strong>
                e ingresa el folio <strong>{{ $incidencia->folio }}</strong> junto con tu número de empleado.
                @if($incidencia->email_reportante)
                    También recibirás actualizaciones en <strong>{{ $incidencia->email_reportante }}</strong>.
                @endif
            </p>
        </div>
    </div>

    <div class="footer">
        Documento generado el {{ now()->timezone('America/Mexico_City')->format('d/m/Y H:i') }} hrs &bull;
        {{ config('app.name') }} &bull; Este comprobante es válido como constancia de registro.
    </div>

</div>
</body>
</html>
