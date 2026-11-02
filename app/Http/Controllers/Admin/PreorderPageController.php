<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PreorderPageFormRequest;
use App\Models\AdminLog;
use App\Models\PreorderPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PreorderPageController extends Controller
{
    public function index(): View
    {
        return view('admin.preorder.index', [
            'pages' => PreorderPage::orderBy('title')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.preorder.form');
    }

    public function edit(PreorderPage $preorderPage): View
    {
        return view('admin.preorder.form', ['preorderPage' => $preorderPage]);
    }

    public function store(PreorderPageFormRequest $request): RedirectResponse
    {
        $data = $request->validated();
        if (isset($data['content'])) {
            $data['content'] = clean_html($data['content']);
        }

        $page = PreorderPage::create($data);
        AdminLog::record($request->user(), 'create', 'PreorderPage', $page->id, null, $data, 'Tambah halaman preorder');

        return redirect()->route('office.preorder.index')->with('status', 'Halaman preorder dibuat.');
    }

    public function update(PreorderPageFormRequest $request, PreorderPage $preorderPage): RedirectResponse
    {
        $data = $request->validated();
        if (isset($data['content'])) {
            $data['content'] = clean_html($data['content']);
        }

        [$old, $new] = AdminLog::diff($preorderPage, ['title', 'slug', 'content', 'meta_description', 'status'], $data);
        $preorderPage->update($data);
        AdminLog::record($request->user(), 'update', 'PreorderPage', $preorderPage->id, $old, $new, 'Ubah halaman preorder');

        return redirect()->route('office.preorder.index')->with('status', 'Halaman preorder diperbarui.');
    }
}
