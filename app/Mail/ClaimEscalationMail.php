<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClaimEscalationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $providerName,
        public string $providerSlug,
        public string $emailAttempted,
        public string $reason,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Envago — reivindicação bloqueada: {$this->providerName}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.claim-escalation');
    }
}
