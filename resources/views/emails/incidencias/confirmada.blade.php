@component('mail::message')
# Tu incidencia ha sido registrada

Hemos recibido tu reporte de incidencia correctamente. Aquí están los datos de tu seguimiento:

| Campo  | Valor |
|--------|-------|
| **Folio** | {{ $folio }} |
| **Asunto** | {{ $titulo }} |

@component('mail::panel')
**Token de seguimiento:** `{{ $token }}`

Guarda este token, lo necesitarás para consultar el estado de tu incidencia.
@endcomponent

Puedes dar seguimiento a tu incidencia en cualquier momento desde el siguiente enlace:

@component('mail::button', ['url' => $urlSeguimiento, 'color' => 'primary'])
Ver estado de mi incidencia
@endcomponent

Gracias,<br>
{{ config('app.name') }}
@endcomponent
