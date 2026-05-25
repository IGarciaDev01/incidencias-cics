<x-mail::message>
# Solicitud de informacion adicional

El equipo encargado de tu incidencia necesita informacion adicional para continuar con la revision.

<x-mail::table>
| | |
| -------- | ------- |
| **Folio** | {{ $folio }} |
| **Asunto** | {{ $titulo }} |
</x-mail::table>

<x-mail::panel>
{{ $mensaje }}
</x-mail::panel>

Por favor, accede al portal de seguimiento para responder o adjuntar la informacion solicitada.

<x-mail::button :url="$urlSeguimiento" color="primary">
Responder en el portal
</x-mail::button>

Atentamente,<br>
{{ config('app.name') }}
</x-mail::message>
