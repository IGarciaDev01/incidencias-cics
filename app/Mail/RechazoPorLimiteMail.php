<?php

namespace App\Mail;

use App\Models\Incidencia;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RechazoPorLimiteMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Incidencia $incidencia,
        public readonly string $razon,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Incidencia {$this->incidencia->folio} rechazada por límite excedido",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.incidencias.rechazo-limite',
            with: [
                'folio' => $this->incidencia->folio,
                'tipoIncidencia' => $this->incidencia->tipo_incidencia->label(),
                'fechaIncidencia' => $this->incidencia->fecha_incidencia->format('d/m/Y'),
                'razon' => $this->razon,
                'urlSeguimiento' => route('seguimiento.show', $this->incidencia->folio),
            ],
        );
    }
}
