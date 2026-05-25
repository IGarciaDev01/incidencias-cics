<x-mail::message>
# Actualizacion de incidencia

Se registro un cambio en el seguimiento de tu incidencia dentro del sistema institucional.

<x-mail::table>
| | |
| -------- | ------- |
| **Folio** | {{ $folio }} |
| **Nuevo estado** | {{ $estado }} |
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
