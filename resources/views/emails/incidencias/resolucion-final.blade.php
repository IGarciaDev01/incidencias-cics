<x-mail::message>
# Resolucion final de incidencia

Tu incidencia fue revisada y cuenta con una resolucion final registrada en el sistema.

<x-mail::table>
| | |
| -------- | ------- |
| **Folio** | {{ $folio }} |
| **Asunto** | {{ $titulo }} |
| **Estado final** | {{ $estado }} |
</x-mail::table>

@if($motivoRechazo)
<x-mail::panel>
**Motivo del rechazo:** {{ $motivoRechazo }}
</x-mail::panel>
@endif

<x-mail::button :url="$urlSeguimiento" color="primary">
Ver mi incidencia
</x-mail::button>

Atentamente,<br>
{{ config('app.name') }}
</x-mail::message>
