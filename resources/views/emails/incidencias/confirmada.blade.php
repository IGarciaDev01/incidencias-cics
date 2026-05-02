@component('mail::message')
# Tu incidencia ha sido registrada

Hemos recibido tu reporte de incidencia correctamente. Aquí están los datos de tu seguimiento:

@component('mail::table')
| | |
| -------- | ------- |
| **Folio** | {{ $folio }} |
| **Asunto** | {{ $titulo }} |
@endcomponent

Puedes dar seguimiento a tu incidencia usando tu **número de empleado** o tu **correo electrónico** junto con el folio.

@component('mail::button', ['url' => $urlSeguimiento, 'color' => 'primary'])
Ver estado de mi incidencia
@endcomponent

Gracias,<br>
{{ config('app.name') }}
@endcomponent