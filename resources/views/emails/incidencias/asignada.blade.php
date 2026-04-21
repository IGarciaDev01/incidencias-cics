@component('mail::message')
# Se te ha asignado una incidencia

Se te ha asignado la siguiente incidencia para su atención:

| Campo        | Valor |
|--------------|-------|
| **Folio**    | {{ $folio }} |
| **Asunto**   | {{ $titulo }} |
| **Prioridad** | {{ $prioridad }} |
@if($fechaLimite)
| **Fecha límite** | {{ $fechaLimite }} |
@endif

@component('mail::button', ['url' => $urlPanel, 'color' => 'primary'])
Ver en el panel
@endcomponent

Gracias,<br>
{{ config('app.name') }}
@endcomponent
