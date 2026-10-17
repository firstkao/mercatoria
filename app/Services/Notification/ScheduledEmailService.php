<?php

namespace App\Services\Notification;

use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class ScheduledEmailService
{
    public function send(User $user, string $templateCode, string $fallbackSubject, array $variables): bool
    {
        $template = EmailTemplate::getByCode($templateCode);
        if (! $template) {
            $this->recordFailure(null, $templateCode, $user, $fallbackSubject, "Active {$templateCode} email template is missing.");

            return false;
        }

        $subjectReplacements = [];
        $bodyReplacements = [];
        foreach ($variables as $key => $value) {
            $token = '{{'.$key.'}}';
            $subjectReplacements[$token] = str_replace(["\r", "\n"], ' ', strip_tags((string) $value));
            $bodyReplacements[$token] = e((string) $value);
        }
        $subject = str_replace(array_keys($subjectReplacements), array_values($subjectReplacements), $template->subject);
        $body = str_replace(array_keys($bodyReplacements), array_values($bodyReplacements), $template->body);

        try {
            Mail::html($body, function ($message) use ($template, $user, $subject): void {
                $message->from($template->from_email, 'Mercatoria')
                    ->to($user->email)
                    ->subject($subject);
            });
        } catch (Throwable $exception) {
            $this->recordFailure($template->id, $templateCode, $user, $subject, Str::limit($exception->getMessage(), 1000));
            report($exception);

            return false;
        }

        EmailLog::create([
            'template_id' => $template->id,
            'recipient_email' => $user->email,
            'subject' => $subject,
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        return true;
    }

    private function recordFailure(?int $templateId, string $templateCode, User $user, string $subject, string $reason): void
    {
        EmailLog::create([
            'template_id' => $templateId,
            'recipient_email' => $user->email,
            'subject' => $subject,
            'status' => 'failed',
            'error_message' => Str::limit($reason, 1000),
        ]);
        Log::error('Scheduled email could not be sent.', [
            'template_id' => $templateId,
            'template_code' => $templateCode,
            'user_id' => $user->id,
            'reason' => $reason,
        ]);
    }
}
