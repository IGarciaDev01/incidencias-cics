@component('mail::message')
# Se te ha asignado una incidencia

Se te ha asignado la siguiente incidencia para su atención:

| Campo        | Valor |
|--------------|-------|
| **Folio**    | {{ $folio }} |
| **Asunto**   | {{ $titulo }} |
@if($area)
| **Área**     | {{ $area }} |
@endif

@component('mail::button', ['url' => $urlSeguimiento, 'color' => 'primary'])
Ver seguimiento
@endcomponent

Gracias,<br>
{{ config('app.name') }}
@endcomponent
