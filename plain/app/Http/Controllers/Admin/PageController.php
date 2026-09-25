<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PageRequest;
use App\Models\AdminLog;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));

        return view('admin.pages.index', [
            'pages' => Page::query()
                ->when($search !== '', fn ($query) => $query->where('title', 'like', "%{$search}%"))
                ->orderBy('sort_order')
                ->orderBy('title')
                ->get(),
            'filters' => ['q' => $search],
        ]);
    }

    public function create(): View
    {
        return view('admin.pages.form', ['page' => new Page(['is_published' => true])]);
    }

    public function store(PageRequest $request): RedirectResponse
    {
        $page = Page::create($request->validated());
        AdminLog::record('create_page', $page, ['judul' => $page->title]);

        return redirect()->route('admin.pages.edit', $page)->with('status', 'Halaman disimpan.');
    }

    public function edit(Page $page): View
    {
        return view('admin.pages.form', ['page' => $page]);
    }

    public function update(PageRequest $request, Page $page): RedirectResponse
    {
        $page->update($request->validated());
        AdminLog::record('update_page', $page, ['judul' => $page->title]);

        return redirect()->route('admin.pages.edit', $page)->with('status', 'Perubahan disimpan.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        abort_if($page->is_system, 403, 'Halaman sistem tidak bisa dihapus.');

        $page->delete();
        AdminLog::record('delete_page', null, ['judul' => $page->title]);

        return redirect()->route('admin.pages.index')->with('status', 'Halaman dihapus.');
    }
}