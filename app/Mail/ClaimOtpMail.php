<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClaimOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $otp,
        public string $businessName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Seu código de verificação — Envago');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.claim-otp');
    }
}
