@component('mail::message')
# Incidencia rechazada por límite excedido – {{ $folio }}

Tu solicitud de **{{ $tipoIncidencia }}** con fecha **{{ $fechaIncidencia }}** ha sido rechazada automáticamente.

@component('mail::panel')
**Motivo:** {{ $razon }}
@endcomponent

Este rechazo queda registrado en el sistema. Si crees que hay un error, contacta a tu jefe inmediato.

@component('mail::button', ['url' => $urlSeguimiento])
Ver mi incidencia
@endcomponent

Gracias,<br>
{{ config('app.name') }}
@endcomponent