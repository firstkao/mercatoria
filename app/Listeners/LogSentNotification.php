<?php

namespace App\Listeners;

use App\Models\EmailLog;
use Illuminate\Notifications\Events\NotificationSent;

class LogSentNotification
{
    /**
     * ER 35: catat setiap email terkirim (to, template, status) di email_logs.
     */
    public function handle(NotificationSent $event): void
    {
        if ($event->channel !== 'mail') {
            return;
        }

        EmailLog::create([
            'recipient_email' => substr((string) $event->notifiable->routeNotificationFor('mail'), 0, 255),
            'subject' => substr((string) optional($event->notification->toMail($event->notifiable))->subject, 0, 255),
            'template_code' => class_basename($event->notification),
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }
}
