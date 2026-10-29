<?php

namespace App\Notifications;

use App\Services\Mail\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminOtpNotification extends Notification
{
    use Queueable;

    public function __construct(public string $code) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $default = (new MailMessage)
            ->subject('Kode OTP Admin Mercatoria')
            ->line('Kode OTP kamu: '.$this->code)
            ->line('Kode berlaku 5 menit. Jangan bagikan ke siapa pun.');

        // PRD 24: template aktif meng-override default.
        return app(EmailTemplateService::class)->override('admin_otp', [
            'nama_user' => (string) ($notifiable->name ?? ''),
            'email' => (string) ($notifiable->email ?? ''),
            'no_hp' => (string) ($notifiable->no_hp ?? ''),
            'link' => $this->code,
        ], $default);
    }
}
