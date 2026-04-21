<?php

namespace App\Mail;

use App\Models\Incidencia;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class IncidenciaAlertaSLAMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Incidencia $incidencia) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "⚠ ALERTA SLA: La incidencia {$this->incidencia->folio} está próxima a vencer",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.incidencias.alerta-sla',
            with: [
                'folio'       => $this->incidencia->folio,
                'titulo'      => $this->incidencia->titulo,
                'prioridad'   => $this->incidencia->prioridad->label(),
                'fechaLimite' => $this->incidencia->fecha_limite?->format('d/m/Y H:i'),
                'estaVencida' => $this->incidencia->estaVencida(),
            ],
        );
    }
}
