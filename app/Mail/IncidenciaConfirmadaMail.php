<?php

namespace App\Mail;

use App\Models\Incidencia;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class IncidenciaConfirmadaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Incidencia $incidencia) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Tu incidencia {$this->incidencia->folio} ha sido registrada",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.incidencias.confirmada',
            with: [
                'folio' => $this->incidencia->folio,
                'titulo' => $this->incidencia->titulo,
                'urlSeguimiento' => route('seguimiento.show', $this->incidencia->folio),
            ],
        );
    }
}
