<x-mail::message>
# Incidencia registrada correctamente

Hemos recibido tu solicitud en el **Sistema de Gestion de Incidencias del CICS UST - IPN**. Conserva estos datos para consultar el avance del tramite.

<x-mail::table>
| | |
| -------- | ------- |
| **Folio** | {{ $folio }} |
| **Asunto** | {{ $titulo }} |
</x-mail::table>

<x-mail::panel>
Para dar seguimiento, ingresa con tu **numero de empleado** o **correo electronico** y el folio mostrado arriba.
</x-mail::panel>

<x-mail::button :url="$urlSeguimiento" color="primary">
Ver estado de mi incidencia
</x-mail::button>

Atentamente,<br>
{{ config('app.name') }}
</x-mail::message>
