<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class LoginOtpMail extends Mailable
{
    public function __construct(public string $name, public string $otp, public int $minutes = 5) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your Vyora login OTP');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.login_otp');
    }
}
