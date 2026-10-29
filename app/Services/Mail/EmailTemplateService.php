<?php

namespace App\Services\Mail;

use App\Models\EmailTemplate;
use Illuminate\Notifications\Messages\MailMessage;

class EmailTemplateService
{
    /**
     * Placeholder standar PRD 24.4.
     *
     * @var array<int, string>
     */
    private const VARIABLES = ['nama_user', 'email', 'no_hp', 'no_order', 'total', 'status', 'state', 'link', 'tanggal', 'nama_toko'];

    /**
     * Render template aktif: ganti {{var}} dengan nilai. Null bila template tidak ada/nonaktif.
     *
     * @param  array<string, mixed>  $variables
     * @return array{subject: string, body: string}|null
     */
    public function render(string $code, array $variables): ?array
    {
        $template = EmailTemplate::where('code', $code)->where('is_active', true)->first();

        if (! $template) {
            return null;
        }

        $variables = array_merge([
            'tanggal' => now()->format('d/m/Y'),
            'nama_toko' => (string) config('app.name'),
        ], $variables);

        return [
            'subject' => $this->replace($template->subject, $variables),
            'body' => $this->replace($template->body, $variables),
        ];
    }

    /**
     * PRD 24: saat notifikasi dikirim, cek email_templates dulu — template aktif meng-override default.
     *
     * @param  array<string, mixed>  $variables
     */
    public function override(string $code, array $variables, MailMessage $default): MailMessage
    {
        $rendered = $this->render($code, $variables);

        $message = $rendered
            ? (new MailMessage)
                ->subject($rendered['subject'])
                ->line($rendered['body'])
            : $default;

        if ($rendered && ! empty($default->from)) {
            $message->from($default->from[0], $default->from[1] ?? null);
        }

        // Penanda template untuk email_logs (kill-switch & audit).
        $message->withSymfonyMessage(function ($email) use ($code): void {
            $email->getHeaders()->addTextHeader('X-Mercatoria-Template', $code);
        });

        return $message;
    }

    /**
     * @param  array<string, mixed>  $variables
     */
    private function replace(string $text, array $variables): string
    {
        foreach (self::VARIABLES as $var) {
            $text = str_replace('{{'.$var.'}}', (string) ($variables[$var] ?? ''), $text);
        }

        return $text;
    }
}
