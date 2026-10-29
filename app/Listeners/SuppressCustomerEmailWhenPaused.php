<?php

namespace App\Listeners;

use App\Models\EmailLog;
use App\Models\User;
use Illuminate\Mail\Events\MessageSending;

class SuppressCustomerEmailWhenPaused
{
    /**
     * Signature pesan yang di-suppress pada request berjalan,
     * agar LogSentNotification tidak mencatatnya ganda sebagai 'sent'.
     *
     * @var array<string, bool>
     */
    public static array $suppressed = [];

    public static function signature(string $recipients, string $subject): string
    {
        return md5($recipients.'|'.$subject);
    }

    /**
     * PRD 24.5: kill-switch email customer (pause_customer_emails).
     * Saat aktif, semua email ke customer dihentikan sebelum SMTP dan dicatat
     * ke email_logs sebagai 'suppressed_maintenance'.
     * Kecualian: notif admin (akun admin / cs@) tetap dikirim.
     */
    public function handle(MessageSending $event): bool
    {
        $isPaused = (bool) setting(
            'pause_customer_emails',
            config('mercatoria.mail_control.pause_customer_emails', false)
        );

        if (! $isPaused) {
            return true;
        }

        $recipients = collect($event->message->getTo())
            ->map(fn ($addr) => (string) $addr->getAddress())
            ->values();

        $onlyAdminRecipients = $recipients->every(
            fn (string $addr) => strcasecmp($addr, 'cs@mercatoria.id') === 0
                || User::where('email', $addr)->where('role', User::ROLE_ADMIN)->exists()
        );

        if ($onlyAdminRecipients) {
            return true;
        }

        $subject = (string) $event->message->getSubject();
        $templateCode = $event->message->getHeaders()->get('X-Mercatoria-Template')?->getBodyAsString() ?? 'unknown';

        self::$suppressed[self::signature($recipients->implode(', '), $subject)] = true;

        EmailLog::create([
            'recipient_email' => substr($recipients->implode(', '), 0, 255),
            'subject' => substr($subject, 0, 255),
            'template_code' => $templateCode,
            'status' => 'suppressed_maintenance',
            'sent_at' => now(),
        ]);

        return false;
    }
}
