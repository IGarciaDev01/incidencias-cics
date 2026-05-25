<x-mail::message>
# Incidencia rechazada por limite excedido

Tu solicitud fue validada conforme a las reglas institucionales y se rechazo automaticamente por exceder el limite permitido.

<x-mail::table>
| | |
| -------- | ------- |
| **Folio** | {{ $folio }} |
| **Tipo de incidencia** | {{ $tipoIncidencia }} |
| **Fecha de incidencia** | {{ $fechaIncidencia }} |
</x-mail::table>

<x-mail::panel>
**Motivo:** {{ $razon }}
</x-mail::panel>

Este movimiento queda registrado en el sistema. Si consideras que existe un error, contacta a tu jefe inmediato o al area correspondiente.

<x-mail::button :url="$urlSeguimiento" color="red">
Ver mi incidencia
</x-mail::button>

Atentamente,<br>
{{ config('app.name') }}
</x-mail::message>
