<?php

namespace App\Notifications;

use App\Services\Mail\EmailTemplateService;
use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends BaseVerifyEmail
{
    use Queueable;

    /**
     * Build the mail representation of the notification (PRD 7.4).
     *
     * @param  MustVerifyEmail  $notifiable
     */
    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        $default = (new MailMessage)
            ->from(Config::get('mail.from.address'), Config::get('mail.from.name'))
            ->subject('Verifikasi Email Mercatoria')
            ->line('Klik tombol di bawah untuk memverifikasi alamat email Anda.')
            ->line('Link verifikasi hanya berlaku selama 24 jam sejak pendaftaran.')
            ->action('Verifikasi Email', $verificationUrl)
            ->line('Jika Anda tidak merasa mendaftar di Mercatoria, abaikan email ini.');

        // PRD 24: template aktif meng-override default.
        return app(EmailTemplateService::class)->override('verify_email', [
            'nama_user' => (string) ($notifiable->name ?? ''),
            'email' => (string) ($notifiable->email ?? ''),
            'no_hp' => (string) ($notifiable->no_hp ?? ''),
            'link' => $verificationUrl,
        ], $default);
    }

    /**
     * Get the verification URL for the given notifiable.
     *
     * @param  MustVerifyEmail  $notifiable
     */
    protected function verificationUrl($notifiable): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addHours(max(1, (int) setting('email_verification_hours', 24))),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}
