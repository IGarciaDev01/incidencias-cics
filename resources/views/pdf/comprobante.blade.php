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
        /* Encabezado */
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
        /* Secciones */
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
        /* Badge de estado/prioridad */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-alta   { background: #fee2e2; color: #991b1b; }
        .badge-media  { background: #fef3c7; color: #92400e; }
        .badge-baja   { background: #d1fae5; color: #065f46; }
        .badge-estado { background: #dbeafe; color: #1e40af; }
        /* Descripción */
        .descripcion-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 10px 12px;
            font-size: 11px;
            color: #374151;
            line-height: 1.6;
        }
        /* Instrucciones de seguimiento */
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
        /* Footer */
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

    {{-- Encabezado --}}
    <div class="header">
        <div class="header-top">
            <div>
                <div class="org-name">{{ config('app.name') }}</div>
                <div class="org-sub">Sistema de Gestión de Incidencias</div>
            </div>
            <div class="folio-box">
                <div class="folio-label">Número de folio</div>
                <div class="folio-value">{{ $incidencia->folio }}</div>
            </div>
        </div>
        <div class="doc-title">Comprobante de Registro de Incidencia</div>
    </div>

    {{-- Datos generales --}}
    <div class="section">
        <div class="section-title">Datos Generales</div>
        <div class="grid-2">
            <table>
                <tr>
                    <td>
                        <div class="field-label">Fecha de registro</div>
                        <div class="field-value">{{ $incidencia->created_at->format('d/m/Y H:i') }} hrs</div>
                    </td>
                    <td>
                        <div class="field-label">Estado</div>
                        <div class="field-value">
                            <span class="badge badge-estado">{{ $incidencia->estado->label() }}</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="field-label">Categoría</div>
                        <div class="field-value">{{ $incidencia->categoria?->nombre ?? '—' }}</div>
                    </td>
                    <td>
                        <div class="field-label">Prioridad</div>
                        <div class="field-value">
                            <span class="badge badge-{{ $incidencia->prioridad->value }}">{{ $incidencia->prioridad->label() }}</span>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Título y descripción --}}
    <div class="section">
        <div class="section-title">Descripción de la Incidencia</div>
        <div style="margin-bottom: 8px;">
            <div class="field-label">Título</div>
            <div class="field-value" style="font-size: 13px;">{{ $incidencia->titulo }}</div>
        </div>
        <div>
            <div class="field-label" style="margin-bottom: 6px;">Descripción</div>
            <div class="descripcion-box">{{ $incidencia->descripcion }}</div>
        </div>
    </div>

    {{-- Datos del reportante --}}
    @if (!$incidencia->es_anonima && ($incidencia->reportante_nombre || $incidencia->reportante_email || $incidencia->reportante_telefono))
    <div class="section">
        <div class="section-title">Datos del Reportante</div>
        <div class="grid-2">
            <table>
                @if ($incidencia->reportante_nombre)
                <tr>
                    <td colspan="2">
                        <div class="field-label">Nombre</div>
                        <div class="field-value">{{ $incidencia->reportante_nombre }}</div>
                    </td>
                </tr>
                @endif
                <tr>
                    @if ($incidencia->reportante_email)
                    <td>
                        <div class="field-label">Correo electrónico</div>
                        <div class="field-value">{{ $incidencia->reportante_email }}</div>
                    </td>
                    @endif
                    @if ($incidencia->reportante_telefono)
                    <td>
                        <div class="field-label">Teléfono</div>
                        <div class="field-value">{{ $incidencia->reportante_telefono }}</div>
                    </td>
                    @endif
                </tr>
            </table>
        </div>
    </div>
    @endif

    {{-- Seguimiento --}}
    <div class="section">
        <div class="section-title">Información de Seguimiento</div>
        @if ($incidencia->token_seguimiento)
        <div class="grid-2" style="margin-bottom: 12px;">
            <table>
                <tr>
                    <td colspan="2">
                        <div class="field-label">Token de seguimiento</div>
                        <div class="field-value mono">{{ $incidencia->token_seguimiento }}</div>
                    </td>
                </tr>
            </table>
        </div>
        @endif
        <div class="seguimiento-box">
            <p>
                Para consultar el estado de tu incidencia, visita la sección de <strong>Seguimiento</strong>
                e ingresa el folio <strong>{{ $incidencia->folio }}</strong>
                @if($incidencia->token_seguimiento) junto con tu token de seguimiento @endif.
                @if($incidencia->reportante_email)
                    También recibirás actualizaciones en <strong>{{ $incidencia->reportante_email }}</strong>.
                @endif
            </p>
        </div>
    </div>

    {{-- Footer --}}
    <div class="footer">
        Documento generado el {{ now()->format('d/m/Y H:i') }} hrs &bull;
        {{ config('app.name') }} &bull; Este comprobante es válido como constancia de registro.
    </div>

</div>
</body>
</html>
