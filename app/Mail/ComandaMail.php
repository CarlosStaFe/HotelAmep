<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ComandaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $pasajero,
        public string $punto,
        public string $fecha,
        public array $consumos,
        public string $total,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Detalle de su consumo');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.comanda');
    }
}
