<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\MerchCategory;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
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

    /**
     * Bulk kategori: item dengan id = update, tanpa id = create.
     */
    public function bulk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'categories' => ['required', 'array', 'min:1'],
            'categories.*.id' => ['nullable', 'integer', 'exists:merch_categories,id'],
            'categories.*.name' => ['required', 'string', 'max:50'],
            'categories.*.priority' => ['required', 'integer', 'min:1', 'max:255'],
            'categories.*.keywords' => ['nullable', 'string'],
        ]);

        $diperbarui = 0;
        $dibuat = 0;

        foreach ($data['categories'] as $item) {
            $item['keywords'] = array_values(array_filter(array_map('trim', explode(',', (string) ($item['keywords'] ?? '')))));

            if (! empty($item['id'])) {
                $category = MerchCategory::findOrFail($item['id']);
                [$old, $new] = AdminLog::diff($category, ['name', 'priority', 'keywords'], $item);
                $category->update([
                    'name' => $item['name'],
                    'priority' => $item['priority'],
                    'keywords' => $item['keywords'],
                ]);
                AdminLog::record($request->user(), 'update', 'MerchCategory', $category->id, $old, $new, 'Bulk ubah kategori merch');
                $diperbarui++;
            } else {
                $category = MerchCategory::create([
                    'name' => $item['name'],
                    'slug' => Str::slug($item['name']),
                    'priority' => $item['priority'],
                    'keywords' => $item['keywords'],
                ]);
                AdminLog::record($request->user(), 'create', 'MerchCategory', $category->id, null, $category->only(['name', 'slug', 'priority']), 'Bulk tambah kategori merch');
                $dibuat++;
            }
        }

        return redirect()->route('office.merch-categories.index')
            ->with('status', "{$diperbarui} kategori diperbarui, {$dibuat} kategori dibuat.");
    }

    /**
     * Bulk hapus kategori — ditolak bila masih dipakai produk.
     */
    public function bulkDelete(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:merch_categories,id'],
        ]);

        $dipakai = Product::whereIn('merch_category_id', $data['ids'])->distinct()->pluck('merch_category_id');

        if ($dipakai->isNotEmpty()) {
            $nama = MerchCategory::whereIn('id', $dipakai)->pluck('name');
            throw ValidationException::withMessages([
                'ids' => 'Kategori '.implode(', ', $nama->all()).' masih dipakai produk.',
            ]);
        }

        $categories = MerchCategory::whereIn('id', $data['ids'])->get();

        foreach ($categories as $category) {
            AdminLog::record($request->user(), 'delete', 'MerchCategory', $category->id, $category->only(['name', 'slug', 'priority']), null, 'Bulk hapus kategori merch');
            $category->delete();
        }

        return redirect()->route('office.merch-categories.index')->with('status', $categories->count().' kategori dihapus.');
    }
}
