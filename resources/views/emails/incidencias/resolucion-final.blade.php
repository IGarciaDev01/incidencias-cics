@component('mail::message')
# Resolución final – Incidencia {{ $folio }}

Tu incidencia ha recibido una resolución final.

**Estado:** {{ $estado }}

@if($resolucion)
@component('mail::panel')
**Resolución:** {{ $resolucion }}
@endcomponent
@endif

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