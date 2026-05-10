<?php

namespace App\Mail;

use App\Models\Incidencia;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class IncidenciaSolicitudInfoMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Incidencia $incidencia,
        public readonly string $mensaje,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Solicitud de información adicional – {$this->incidencia->folio}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.incidencias.solicitud-info',
            with: [
                'folio' => $this->incidencia->folio,
                'titulo' => $this->incidencia->titulo,
                'mensaje' => $this->mensaje,
                'urlSeguimiento' => route('seguimiento.show', $this->incidencia->folio),
            ],
        );
    }
}
