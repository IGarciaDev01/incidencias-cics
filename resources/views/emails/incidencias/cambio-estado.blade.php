@component('mail::message')
# Actualización en tu incidencia {{ $folio }}

El estado de tu incidencia ha sido actualizado.

**Nuevo estado:** {{ $estado }}

@if($motivoRechazo)
@component('mail::panel')
**Motivo del rechazo:** {{ $motivoRechazo }}
@endcomponent
@endif

@component('mail::button', ['url' => $urlSeguimiento])
Ver mi incidencia
@endcomponent

Gracias,<br>
{{ config('app.name') }}
@endcomponent
