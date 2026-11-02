<?php

namespace App\Notifications;

use App\Services\Mail\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserGiftNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $link = route('profile.wallet');

        $default = (new MailMessage)
            ->subject('Hadiah dari Mercatoria')
            ->line('Halo '.$notifiable->name.', admin Mercatoria mengirim hadiah untukmu.')
            ->action('Lihat Hadiah', $link);

        return app(EmailTemplateService::class)->override('user_gift_notification', [
            'nama_user' => $notifiable->name,
            'nama_toko' => config('app.name'),
            'link' => $link,
        ], $default);
    }
}
