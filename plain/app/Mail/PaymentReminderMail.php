<?php

namespace App\Mail;

use App\Models\User;
// use App\Models\Order; // Uncomment jika model Order sudah siap
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public User $user;
    // public Order $order; // Uncomment jika ingin melampirkan data order

    /**
     * Create a new message instance.
     */
    public function __construct(User $user /*, Order $order */)
    {
        $this->user = $user;
        // $this->order = $order;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Peringatan: Batas Waktu Pembayaran Hampir Habis - MERCATORIA',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-reminder', // Ini merujuk ke file Blade HTML
        );
    }
}
