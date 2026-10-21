<?php

namespace App\Listeners;

use App\Models\EmailLog;
use App\Models\User;
use Illuminate\Mail\Events\MessageSending;

class SuppressCustomerEmailWhenPaused
{
    public static function isPaused(): bool
    {
        return filter_var(
            setting('pause_customer_emails', config('mercatoria.mail_control.pause_customer_emails', false)),
            FILTER_VALIDATE_BOOLEAN,
        );
    }

    /**
     * Admin recipients (OTP, test send) are never suppressed so admins cannot be locked out.
     */
    public static function shouldSuppress(string $recipientEmail): bool
    {
        return self::isPaused()
            && ! User::query()->where('email', $recipientEmail)->where('role', 'admin')->exists();
    }

    public function handle(MessageSending $event): ?bool
    {
        if (! self::isPaused()) {
            return null;
        }

        $suppressed = false;
        foreach ($event->message->getTo() as $address) {
            if (! self::shouldSuppress($address->getAddress())) {
                continue;
            }

            $suppressed = true;
            EmailLog::create([
                'recipient_email' => $address->getAddress(),
                'subject' => (string) $event->message->getSubject(),
                'status' => 'suppressed_maintenance',
            ]);
        }

        return $suppressed ? false : null;
    }
}
