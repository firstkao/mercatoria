<?php

namespace App\Services\Notification;

use App\Enums\OrderStatus;
use App\Listeners\SuppressCustomerEmailWhenPaused;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class ScheduledEmailService
{
    public function send(User $user, string $templateCode, string $fallbackSubject, array $variables): bool
    {
        return $this->sendToAddress($user->email, $templateCode, $fallbackSubject, $variables, $user->id);
    }

    /**
     * Email order standar: menambah variabel umum (nama, no order, status, total, link).
     */
    public function sendOrder(Order $order, string $templateCode, string $fallbackSubject, array $variables = []): bool
    {
        $order->loadMissing('user');
        if (! $order->user) {
            return false;
        }

        return $this->send($order->user, $templateCode, $fallbackSubject, array_merge([
            'nama_user' => $order->user->name,
            'email' => $order->user->email,
            'no_order' => $order->order_no,
            'status' => OrderStatus::tryFrom((string) $order->status)?->label() ?? (string) $order->status,
            'total' => 'Rp '.number_format((float) $order->total, 0, ',', '.'),
            'link' => route('orders.show', $order),
            'tanggal' => now()->timezone('Asia/Jakarta')->format('d M Y H:i').' WIB',
            'nama_toko' => 'Mercatoria',
        ], $variables));
    }

    /**
     * Kirim ke semua admin + cs@ (mis. notifikasi bukti bayar masuk).
     */
    public function sendToAdmins(string $templateCode, string $fallbackSubject, array $variables): void
    {
        $recipients = User::query()->where('role', 'admin')->pluck('email')->all();
        $recipients[] = (string) config('mercatoria.store.cs_email');

        foreach (array_unique(array_filter($recipients)) as $email) {
            $this->sendToAddress($email, $templateCode, $fallbackSubject, $variables);
        }
    }

    public function sendToAddress(string $email, string $templateCode, string $fallbackSubject, array $variables, ?int $userId = null): bool
    {
        $template = EmailTemplate::getByCode($templateCode);
        if (! $template) {
            $this->recordFailure(null, $templateCode, $email, $userId, $fallbackSubject, "Active {$templateCode} email template is missing.");

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

        if (SuppressCustomerEmailWhenPaused::shouldSuppress($email)) {
            EmailLog::create([
                'template_id' => $template->id,
                'recipient_email' => $email,
                'subject' => $subject,
                'status' => 'suppressed_maintenance',
            ]);

            return true;
        }

        try {
            Mail::html($body, function ($message) use ($template, $email, $subject): void {
                $message->from($template->from_email, 'Mercatoria')
                    ->to($email)
                    ->subject($subject);
            });
        } catch (Throwable $exception) {
            $this->recordFailure($template->id, $templateCode, $email, $userId, $subject, Str::limit($exception->getMessage(), 1000));
            report($exception);

            return false;
        }

        EmailLog::create([
            'template_id' => $template->id,
            'recipient_email' => $email,
            'subject' => $subject,
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        return true;
    }

    private function recordFailure(?int $templateId, string $templateCode, string $email, ?int $userId, string $subject, string $reason): void
    {
        EmailLog::create([
            'template_id' => $templateId,
            'recipient_email' => $email,
            'subject' => $subject,
            'status' => 'failed',
            'error_message' => Str::limit($reason, 1000),
        ]);
        Log::error('Scheduled email could not be sent.', [
            'template_id' => $templateId,
            'template_code' => $templateCode,
            'user_id' => $userId,
            'reason' => $reason,
        ]);
    }
}
