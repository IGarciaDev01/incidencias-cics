<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReporteDiarioIncidenciasMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $fecha,
        public readonly int $totalIncidencias,
        public readonly string $zipPath,
        public readonly string $zipName,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Reporte diario de incidencias - {$this->fecha}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.incidencias.reporte-diario',
            with: [
                'fecha' => $this->fecha,
                'totalIncidencias' => $this->totalIncidencias,
                'zipName' => $this->zipName,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->zipPath)
                ->as($this->zipName)
                ->withMime('application/zip'),
        ];
    }
}
