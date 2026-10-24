<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $title;
    public $messageBody;
    public $userName;

    public function __construct(string $title, string $messageBody, string $userName)
    {
        $this->title = $title;
        $this->messageBody = $messageBody;
        $this->userName = $userName;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->title . ' - MyTradeX',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.account-notification',
        );
    }
}