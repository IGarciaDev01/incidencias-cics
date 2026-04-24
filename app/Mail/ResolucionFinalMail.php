<?php

namespace App\Mail;

use App\Models\Incidencia;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResolucionFinalMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Incidencia $incidencia) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Resolución final – incidencia {$this->incidencia->folio}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.incidencias.resolucion-final',
            with: [
                'folio' => $this->incidencia->folio,
                'titulo' => $this->incidencia->titulo,
                'estado' => $this->incidencia->estado->label(),
                'motivoRechazo' => $this->incidencia->motivo_rechazo,
                'resolucion' => $this->incidencia->resolucion,
                'urlSeguimiento' => route('seguimiento.show', $this->incidencia->folio),
            ],
        );
    }
}
