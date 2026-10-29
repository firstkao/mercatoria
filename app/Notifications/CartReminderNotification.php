<?php

namespace App\Notifications;

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
        return (new MailMessage)
            ->from('info@mercatoria.id', 'Mercatoria')
            ->subject('Pengingat Keranjang Belanja Mercatoria')
            ->line('Ada '.$this->itemCount.' item yang masih menunggu di keranjangmu.')
            ->line('Selesaikan belanjamu sebelum kehabisan!')
            ->action('Lihat Keranjang', route('cart.index'));
    }
}
