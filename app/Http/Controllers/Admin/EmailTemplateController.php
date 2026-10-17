<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Services\Content\ContentHtmlSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class EmailTemplateController extends Controller
{
    public function index(): View
    {
        $templates = EmailTemplate::query()->withCount('logs')->orderBy('name')->paginate(25);

        return view('admin.content.email-templates.index', compact('templates'));
    }

    public function create(): View
    {
        return view('admin.content.email-templates.form', ['template' => new EmailTemplate]);
    }

    public function store(Request $request, ContentHtmlSanitizer $sanitizer): RedirectResponse
    {
        $validated = $this->validatedData($request);
        $validated['body'] = $sanitizer->sanitize($validated['body']);

        DB::transaction(function () use ($validated): void {
            $template = EmailTemplate::create($validated);
            AdminLog::record('email_template_created', EmailTemplate::class, $template->id, new: $validated);
        });

        return redirect()->route('office.email-templates.index')->with('success', 'Template email berhasil dibuat.');
    }

    public function edit(EmailTemplate $emailTemplate): View
    {
        return view('admin.content.email-templates.form', ['template' => $emailTemplate]);
    }

    public function update(Request $request, EmailTemplate $emailTemplate, ContentHtmlSanitizer $sanitizer): RedirectResponse
    {
        $validated = $this->validatedData($request, $emailTemplate);
        $validated['body'] = $sanitizer->sanitize($validated['body']);
        $old = $emailTemplate->only(array_keys($validated));

        DB::transaction(function () use ($emailTemplate, $validated, $old): void {
            $emailTemplate->update($validated);
            AdminLog::record('email_template_updated', EmailTemplate::class, $emailTemplate->id, old: $old, new: $validated);
        });

        return redirect()->route('office.email-templates.index')->with('success', 'Template email berhasil diperbarui.');
    }

    public function destroy(EmailTemplate $emailTemplate): RedirectResponse
    {
        DB::transaction(function () use ($emailTemplate): void {
            AdminLog::record('email_template_deleted', EmailTemplate::class, $emailTemplate->id, old: $emailTemplate->only(['code', 'name']));
            $emailTemplate->delete();
        });

        return redirect()->route('office.email-templates.index')->with('success', 'Template email berhasil dihapus.');
    }

    public function preview(EmailTemplate $emailTemplate): View
    {
        return view('admin.content.email-templates.preview', [
            'template' => $emailTemplate,
            'subject' => $this->renderTemplate($emailTemplate->subject),
            'body' => $this->renderTemplate($emailTemplate->body),
        ]);
    }

    public function testSend(Request $request, EmailTemplate $emailTemplate): RedirectResponse
    {
        abort_unless($emailTemplate->is_active, 404);

        $validated = $request->validate([
            'recipient_email' => ['required', 'email', 'max:255'],
        ]);
        $subject = '[TEST] '.$this->renderTemplate($emailTemplate->subject);
        $body = $this->renderTemplate($emailTemplate->body);

        try {
            Mail::html($body, function ($message) use ($emailTemplate, $validated, $subject): void {
                $message->from($emailTemplate->from_email, 'Mercatoria')
                    ->to($validated['recipient_email'])
                    ->subject($subject);
            });
        } catch (Throwable $exception) {
            EmailLog::create([
                'template_id' => $emailTemplate->id,
                'recipient_email' => $validated['recipient_email'],
                'subject' => $subject,
                'status' => 'failed',
                'error_message' => Str::limit($exception->getMessage(), 1000),
            ]);
            report($exception);

            return back()->withErrors([
                'recipient_email' => 'Email tes gagal dikirim. Periksa konfigurasi SMTP dan log aplikasi.',
            ]);
        }

        EmailLog::create([
            'template_id' => $emailTemplate->id,
            'recipient_email' => $validated['recipient_email'],
            'subject' => $subject,
            'status' => 'sent',
            'sent_at' => now(),
        ]);
        AdminLog::record(
            'email_template_test_sent',
            EmailTemplate::class,
            $emailTemplate->id,
            new: ['recipient_email' => $validated['recipient_email']],
        );

        return back()->with('success', 'Email tes dikirim dan dicatat pada log pengiriman.');
    }

    private function validatedData(Request $request, ?EmailTemplate $template = null): array
    {
        $request->merge([
            'code' => strtolower(trim((string) $request->input('code'))),
            'from_email' => strtolower(trim((string) $request->input('from_email'))),
        ]);
        $validated = $request->validate([
            'code' => ['required', 'alpha_dash:ascii', 'max:64', Rule::unique('email_templates', 'code')->ignore($template?->id)],
            'name' => ['required', 'string', 'max:255'],
            'from_email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:100000'],
            'variables_text' => ['nullable', 'string', 'max:3000'],
        ]);

        $validated['variables'] = array_values(array_unique(array_filter(array_map(
            'trim',
            preg_split('/\R/', $validated['variables_text'] ?? '') ?: [],
        ))));
        unset($validated['variables_text']);
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }

    private function renderTemplate(string $content): string
    {
        $variables = [
            'nama_user' => 'Nama Pengguna',
            'email' => 'user@example.com',
            'no_hp' => '628123456789',
            'no_order' => 'MER-TEST-0001',
            'total' => 'Rp100.000',
            'status' => 'Menunggu Pembayaran',
            'state' => 'Customer',
            'link' => route('home'),
            'tanggal' => now()->format('d-m-Y'),
            'nama_toko' => 'Mercatoria',
        ];

        foreach ($variables as $key => $value) {
            $content = str_replace('{{'.$key.'}}', e($value), $content);
        }

        return $content;
    }
}
