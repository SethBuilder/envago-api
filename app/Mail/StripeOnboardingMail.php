<?php

namespace App\Mail;

use App\Models\Provider;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StripeOnboardingMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Provider $provider) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Configure seus recebimentos no Envago');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.stripe-onboarding');
    }
}
