<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $otp,
        public string $purpose,
        public string $userName = 'User'
    ) {}

    public function envelope(): Envelope
    {
        $subject = match ($this->purpose) {
            'registration' => 'Verify Your Account',
            'login' => 'Your Login Code',
            'password_reset' => 'Reset Your Password',
            default => 'Your Verification Code',
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.otp',
            with: [
                'otp' => $this->otp,
                'purpose' => $this->purpose,
                'userName' => $this->userName,
                'expiryMinutes' => 10,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
