<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PageFormRequest;
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

    public function create(): View
    {
        return view('admin.pages.create');
    }

    public function edit(Page $page): View
    {
        return view('admin.pages.edit', ['page' => $page]);
    }

    public function store(PageFormRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $page = Page::create($data);
        AdminLog::record($request->user(), 'create', 'Page', $page->id, null, $data, 'Tambah halaman CMS');

        return redirect()->route('office.pages.index')->with('status', 'Halaman dibuat.');
    }

    public function update(PageFormRequest $request, Page $page): RedirectResponse
    {
        $data = $request->validated();

        [$old, $new] = AdminLog::diff($page, ['title', 'slug', 'content', 'meta_description', 'status'], $data);
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
