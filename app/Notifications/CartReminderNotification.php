<?php

namespace App\Notifications;

use App\Services\Mail\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CartReminderNotification extends Notification
{
    use Queueable;

    public function __construct(public int $itemCount) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $default = (new MailMessage)
            ->from('info@mercatoria.id', 'Mercatoria')
            ->subject('Pengingat Keranjang Belanja Mercatoria')
            ->line('Ada '.$this->itemCount.' item yang masih menunggu di keranjangmu.')
            ->line('Selesaikan belanjamu sebelum kehabisan!')
            ->action('Lihat Keranjang', route('cart.index'));

        // PRD 24: template aktif meng-override default.
        return app(EmailTemplateService::class)->override('cart_reminder', [
            'nama_user' => (string) ($notifiable->name ?? ''),
            'email' => (string) ($notifiable->email ?? ''),
            'no_hp' => (string) ($notifiable->no_hp ?? ''),
            'link' => route('cart.index'),
        ], $default);
    }
}
