<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessage extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public \App\Models\ContactMessage $message) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pesan Kontak Baru: ' . $this->message->subject,
            replyTo: [$this->message->email],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.contact-message');
    }
}