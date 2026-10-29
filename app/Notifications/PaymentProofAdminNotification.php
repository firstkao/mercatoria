<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Mail\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentProofAdminNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Order $order,
        public Payment $payment,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $default = (new MailMessage)
            ->from('cs@mercatoria.id', 'Mercatoria CS')
            ->subject('Bukti bayar masuk — '.$this->order->no_order)
            ->line('No order: '.$this->order->no_order)
            ->line('Customer: '.$this->order->user->name.' ('.$this->order->user->email.')')
            ->line('Total: '.format_rp((float) $this->payment->amount))
            ->line('Tipe pembayaran: '.$this->payment->type.' — Metode: '.$this->payment->method)
            ->action('Lihat & Verifikasi Bukti', route('office.orders.show', $this->order));

        // PRD 24: template aktif meng-override default.
        return app(EmailTemplateService::class)->override('admin_payment_notification', [
            'nama_user' => (string) $this->order->user->name,
            'email' => (string) $this->order->user->email,
            'no_hp' => (string) ($this->order->user->no_hp ?? ''),
            'no_order' => $this->order->no_order,
            'total' => format_rp((float) $this->payment->amount),
            'status' => $this->payment->status,
            'link' => route('office.orders.show', $this->order),
        ], $default);
    }
}
