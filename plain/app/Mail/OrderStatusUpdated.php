<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class OrderStatusUpdated extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        $statusLabel = str_replace('_', ' ', Str::title($this->order->status));
        return new Envelope(subject: 'Update Pesanan #' . $this->order->order_number . ' - ' . $statusLabel);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.order_status');
    }
}
