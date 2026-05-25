<x-mail::message>
# Incidencia asignada para atencion

Se te asigno una incidencia dentro del flujo institucional. Revisa la informacion y continua con la atencion correspondiente.

<x-mail::table>
| Campo        | Valor |
|--------------|-------|
| **Folio**    | {{ $folio }} |
| **Asunto**   | {{ $titulo }} |
@if($area)
| **Área**     | {{ $area }} |
@endif
</x-mail::table>

<x-mail::button :url="$urlSeguimiento" color="primary">
Ver seguimiento
</x-mail::button>

Atentamente,<br>
{{ config('app.name') }}
</x-mail::message>
