@component('mail::message')
# ⚠ Alerta de SLA – Incidencia {{ $folio }}

@if($estaVencida)
La incidencia **{{ $folio }}** ha **superado su fecha límite** de resolución.
@else
La incidencia **{{ $folio }}** está próxima a superar su fecha límite de resolución.
@endif

| Campo         | Valor |
|---------------|-------|
| **Folio**     | {{ $folio }} |
| **Asunto**    | {{ $titulo }} |
| **Prioridad** | {{ $prioridad }} |
| **Fecha límite** | {{ $fechaLimite }} |

Es necesario tomar acción inmediata para evitar incumplimiento del SLA.

Gracias,<br>
{{ config('app.name') }}
@endcomponent
