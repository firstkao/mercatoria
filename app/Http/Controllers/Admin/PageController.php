<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function index(): View
    {
        return view('admin.pages.index', [
            'pages' => Page::orderBy('slug')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:170', 'unique:pages,slug'],
            'content' => ['nullable', 'string'],
            'meta_description' => ['nullable', 'string', 'max:300'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        $page = Page::create($data);
        AdminLog::record($request->user(), 'create', 'Page', $page->id, null, $data, 'Tambah halaman CMS');

        return redirect()->route('office.pages.index')->with('status', 'Halaman dibuat.');
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'content' => ['nullable', 'string'],
            'meta_description' => ['nullable', 'string', 'max:300'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        [$old, $new] = AdminLog::diff($page, ['title', 'content', 'meta_description', 'is_active'], $data);
        $page->update($data);
        AdminLog::record($request->user(), 'update', 'Page', $page->id, $old, $new, 'Ubah halaman CMS');

        return redirect()->route('office.pages.index')->with('status', 'Halaman diperbarui.');
    }

    public function destroy(Request $request, Page $page): RedirectResponse
    {
        AdminLog::record($request->user(), 'delete', 'Page', $page->id, $page->only(['title', 'slug', 'content', 'meta_description', 'is_active']), null, 'Hapus halaman CMS');
        $page->delete();

        return redirect()->route('office.pages.index')->with('status', 'Halaman dihapus.');
    }
}
