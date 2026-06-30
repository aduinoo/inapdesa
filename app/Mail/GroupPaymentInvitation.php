<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GroupPaymentInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $token,
        public array $booking,
        public float $shareAmount,
        public string $initiatorName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->initiatorName . ' invited you to split a homestay payment',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.group_payment_invitation',
        );
    }
}
