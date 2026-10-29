<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VoucherExpiringNotification extends Notification
{
    use Queueable;

    public function __construct(public string $expiryDate) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Voucher kamu akan expired pada '.$this->expiryDate)
            ->line('Voucher kamu akan expired pada '.$this->expiryDate.'.')
            ->line('Jangan sampai hangus — segera gunakan sebelum masa berlaku habis!')
            ->action('Lihat Voucher', route('profile.wallet'));
    }
}
