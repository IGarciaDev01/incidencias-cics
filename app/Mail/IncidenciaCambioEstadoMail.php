<?php

namespace App\Mail;

use App\Models\Incidencia;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class IncidenciaCambioEstadoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Incidencia $incidencia) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Actualización en tu incidencia {$this->incidencia->folio}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.incidencias.cambio-estado',
            with: [
                'folio' => $this->incidencia->folio,
                'titulo' => $this->incidencia->titulo,
                'estado' => $this->incidencia->estado->label(),
                'motivoRechazo' => $this->incidencia->motivo_rechazo,
                'urlSeguimiento' => route('seguimiento.show', $this->incidencia->folio),
            ],
        );
    }
}
