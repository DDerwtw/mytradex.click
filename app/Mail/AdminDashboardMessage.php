<?php

namespace App\Mail;

use App\Models\AdminUserMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class AdminDashboardMessage extends Mailable
{
    use Queueable;

    public function __construct(public AdminUserMessage $notice) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'MyTradeX update: '.$this->notice->title);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.admin-dashboard-message');
    }

    public function attachments(): array
    {
        return [];
    }
}
