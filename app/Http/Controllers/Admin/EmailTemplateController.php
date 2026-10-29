<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\EmailTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailTemplateController extends Controller
{
    public function index(): View
    {
        return view('admin.email-templates.index', [
            'templates' => EmailTemplate::orderBy('code')->get(),
        ]);
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
        return $request->validate([
            'code' => ['required', 'string', 'max:64', $template ? 'unique:email_templates,code,'.$template->id : 'unique:email_templates,code'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);
    }
}
