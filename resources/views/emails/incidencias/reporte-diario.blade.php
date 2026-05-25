<x-mail::message>
# Reporte diario de incidencias

Se adjunta el paquete diario de incidencias generadas el **{{ $fecha }}** en el Sistema de Gestion de Incidencias del CICS UST - IPN.

<x-mail::table>
| | |
| -------- | ------- |
| **Fecha del reporte** | {{ $fecha }} |
| **Incidencias generadas** | {{ $totalIncidencias }} |
| **Archivo adjunto** | {{ $zipName }} |
</x-mail::table>

<x-mail::panel>
El archivo ZIP incluye el CSV resumen, el comprobante PDF de cada incidencia y los adjuntos cargados por el solicitante.
</x-mail::panel>

Atentamente,<br>
{{ config('app.name') }}
</x-mail::message>
