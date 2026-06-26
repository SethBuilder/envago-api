<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountActivationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $activationUrl;

    public function __construct(public User $user)
    {
        $this->activationUrl = route('auth.activate', $user->activation_token);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Ative sua conta no Envago');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.activation');
    }
}
