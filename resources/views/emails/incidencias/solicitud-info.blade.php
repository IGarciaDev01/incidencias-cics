@component('mail::message')
# Solicitud de información adicional

El equipo encargado de tu incidencia **{{ $folio }}** necesita información adicional para continuar con su atención.

@component('mail::panel')
{{ $mensaje }}
@endcomponent

Por favor, accede al portal de seguimiento para responder o adjuntar la información solicitada.

@component('mail::button', ['url' => $urlSeguimiento])
Responder en el portal
@endcomponent

Gracias,<br>
{{ config('app.name') }}
@endcomponent
