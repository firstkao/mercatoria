<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\MerchCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MerchCategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.merch-categories.index', [
            'categories' => MerchCategory::orderBy('priority')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'priority' => ['required', 'integer', 'min:1', 'max:255'],
            'keywords' => ['nullable', 'string'],
        ]);
        $data['slug'] = Str::slug($data['name']);
        $data['keywords'] = array_values(array_filter(array_map('trim', explode(',', (string) $request->input('keywords')))));

        $category = MerchCategory::create($data);
        AdminLog::record($request->user(), 'create', 'MerchCategory', $category->id, null, $data, 'Tambah kategori merch');

        return redirect()->route('office.merch-categories.index')->with('status', 'Kategori dibuat.');
    }

    public function update(Request $request, MerchCategory $merch_category): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'priority' => ['required', 'integer', 'min:1', 'max:255'],
            'keywords' => ['nullable', 'string'],
        ]);
        $data['keywords'] = array_values(array_filter(array_map('trim', explode(',', (string) $request->input('keywords')))));

        [$old, $new] = AdminLog::diff($merch_category, ['name', 'priority', 'keywords'], $data);
        $merch_category->update($data);
        AdminLog::record($request->user(), 'update', 'MerchCategory', $merch_category->id, $old, $new, 'Ubah kategori merch');

        return redirect()->route('office.merch-categories.index')->with('status', 'Kategori diperbarui.');
    }
}
