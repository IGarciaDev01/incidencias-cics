<?php

namespace App\Mail;

use App\Models\Incidencia;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class IncidenciaAsignadaMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Incidencia $incidencia,
        public readonly User $coordinador,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Se te ha asignado la incidencia {$this->incidencia->folio}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.incidencias.asignada',
            with: [
                'folio' => $this->incidencia->folio,
                'titulo' => $this->incidencia->titulo,
                'area' => $this->incidencia->area?->nombre,
                'urlSeguimiento' => route('seguimiento.show', $this->incidencia->folio),
            ],
        );
    }
}
