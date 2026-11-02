<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\EmailTemplate;
use App\Services\Mail\EmailTemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class EmailTemplateController extends Controller
{
    public function index(): View
    {
        return view('admin.email-templates.index', [
            'templates' => EmailTemplate::orderBy('code')->get(),
        ]);
    }

    public function preview(EmailTemplate $emailTemplate, EmailTemplateService $templates): Response
    {
        $sample = $templates->renderSample($emailTemplate);

        return response(
            '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Preview</title></head><body style="font-family:sans-serif;max-width:640px;margin:2rem auto">'
            .'<p style="color:#666;font-size:12px">Preview dengan data contoh</p>'
            .'<h2>'.e($sample['subject']).'</h2><div>'.nl2br(e($sample['body'])).'</div></body></html>'
        );
    }

    public function testSend(Request $request, EmailTemplate $emailTemplate, EmailTemplateService $templates): RedirectResponse
    {
        $sample = $templates->renderSample($emailTemplate);
        $admin = $request->user();

        Mail::raw($sample['body'], fn ($message) => $message->to($admin->email)->subject('[TEST] '.$sample['subject']));
        AdminLog::record($admin, 'test_send', 'EmailTemplate', $emailTemplate->id, null, ['to' => $admin->email], 'Test send template email');

        return back()->with('status', 'Email test dikirim ke '.$admin->email.'.');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateTemplate($request);
        $data['is_active'] = $request->boolean('is_active');

        $template = EmailTemplate::create($data);
        AdminLog::record($request->user(), 'create', 'EmailTemplate', $template->id, null, $data, 'Tambah template email');

        return redirect()->route('office.email-templates.index')->with('status', 'Template email dibuat.');
    }

    public function update(Request $request, EmailTemplate $emailTemplate): RedirectResponse
    {
        $data = $this->validateTemplate($request, $emailTemplate);
        $data['is_active'] = $request->boolean('is_active');

        [$old, $new] = AdminLog::diff($emailTemplate, array_keys($data), $data);
        $emailTemplate->update($data);
        AdminLog::record($request->user(), 'update', 'EmailTemplate', $emailTemplate->id, $old, $new, 'Ubah template email');

        return redirect()->route('office.email-templates.index')->with('status', 'Template email diperbarui.');
    }

    public function destroy(Request $request, EmailTemplate $emailTemplate): RedirectResponse
    {
        AdminLog::record($request->user(), 'delete', 'EmailTemplate', $emailTemplate->id, $emailTemplate->only(['code', 'subject']), null, 'Hapus template email');
        $emailTemplate->delete();

        return redirect()->route('office.email-templates.index')->with('status', 'Template email dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateTemplate(Request $request, ?EmailTemplate $template = null): array
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:64', $template ? 'unique:email_templates,code,'.$template->id : 'unique:email_templates,code'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);

        // XSS: sanitasi konten HTML email saat simpan.
        $data['body'] = clean_html($data['body']);

        return $data;
    }
}
