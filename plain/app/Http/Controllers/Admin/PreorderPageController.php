<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\PreorderBanner;
use App\Models\PreorderPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class PreorderPageController extends Controller
{
    public function index(): View
    {
        return view('admin.preorder.index', [
            'pages' => PreorderPage::query()
                ->withCount('banners')
                ->latest()
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.preorder.form', [
            'page' => new PreorderPage(['is_active' => false]),
            'banners' => collect(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['title']);

        $page = DB::transaction(function () use ($data, $request) {
            // Kalau di-set aktif, matikan dulu page lain yang aktif
            if (! empty($data['is_active'])) {
                PreorderPage::query()->active()->update(['is_active' => false]);
            }

            $page = PreorderPage::create($data);

            // Handle upload banner
            if ($request->hasFile('banners')) {
                $this->syncBanners($page, $request->file('banners'));
            }

            return $page;
        });

        AdminLog::record('create_preorder_page', $page, ['title' => $page->title]);

        return redirect()->route('admin.preorder.edit', $page)->with('status', 'Halaman Pre-Order berhasil dibuat.');
    }

    public function edit(PreorderPage $preorder): View
    {
        return view('admin.preorder.form', [
            'page' => $preorder,
            'banners' => $preorder->banners()->get(),
        ]);
    }

    public function update(Request $request, PreorderPage $preorder): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['title']);

        DB::transaction(function () use ($preorder, $data, $request) {
            if (! empty($data['is_active']) && ! $preorder->is_active) {
                PreorderPage::query()->whereKeyNot($preorder->id)->active()->update(['is_active' => false]);
            }

            $preorder->update($data);

            // Hapus banner yang dicentang remove
            $removeIds = (array) $request->input('remove_banners', []);
            if (! empty($removeIds)) {
                $toDelete = PreorderBanner::query()
                    ->where('preorder_page_id', $preorder->id)
                    ->whereIn('id', $removeIds)
                    ->get();
                foreach ($toDelete as $banner) {
                    Storage::disk('public')->delete($banner->image_path);
                    $banner->delete();
                }
            }

            // Update metadata banner existing (link, alt, sort, is_active)
            $existingMeta = (array) $request->input('banners_existing', []);
            foreach ($existingMeta as $id => $meta) {
                $banner = PreorderBanner::query()
                    ->where('preorder_page_id', $preorder->id)
                    ->whereKey($id)
                    ->first();
                if (! $banner) continue;
                $banner->update([
                    'link_url' => $meta['link_url'] ?? null,
                    'alt_text' => $meta['alt_text'] ?? null,
                    'sort_order' => (int) ($meta['sort_order'] ?? 0),
                    'is_active' => ! empty($meta['is_active']),
                ]);
            }

            // Upload banner baru
            if ($request->hasFile('banners')) {
                $this->syncBanners($preorder, $request->file('banners'));
            }
        });

        AdminLog::record('update_preorder_page', $preorder, ['title' => $preorder->title]);

        return back()->with('status', 'Perubahan disimpan.');
    }

    public function destroy(PreorderPage $preorder): RedirectResponse
    {
        $title = $preorder->title;

        DB::transaction(function () use ($preorder) {
            foreach ($preorder->banners as $banner) {
                Storage::disk('public')->delete($banner->image_path);
            }
            $preorder->delete();
        });

        AdminLog::record('delete_preorder_page', null, ['title' => $title]);

        return redirect()->route('admin.preorder.index')->with('status', 'Halaman Pre-Order dihapus.');
    }

    public function toggle(PreorderPage $preorder): RedirectResponse
    {
        DB::transaction(function () use ($preorder) {
            if (! $preorder->is_active) {
                PreorderPage::query()->whereKeyNot($preorder->id)->active()->update(['is_active' => false]);
                $preorder->update(['is_active' => true]);
            } else {
                $preorder->update(['is_active' => false]);
            }
        });

        AdminLog::record('toggle_preorder_page', $preorder, ['active' => $preorder->is_active]);

        return back()->with('status', $preorder->is_active ? 'Halaman diaktifkan.' : 'Halaman dinonaktifkan.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:200', 'regex:/^[a-z0-9-]+$/'],
            'content' => ['nullable', 'string', 'max:100000'],
            'is_active' => ['nullable', 'boolean'],
            'banners' => ['array', 'max:10'],
            'banners.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $data['is_active'] = ! empty($data['is_active']);
        unset($data['banners']);

        return $data;
    }

    /**
     * @param  array<int, \Illuminate\Http\UploadedFile>  $files
     */
    private function syncBanners(PreorderPage $page, array $files): void
    {
        $manager = new ImageManager(new Driver());
        $nextOrder = (int) $page->banners()->max('sort_order');

        foreach ($files as $file) {
            if (! $file) continue;

            $filename = 'preorder/' . Str::random(40) . '.webp';
            $img = $manager->read($file)->scaleDown(width: 1600);
            Storage::disk('public')->put($filename, (string) $img->toWebp(80));

            $page->banners()->create([
                'image_path' => $filename,
                'sort_order' => ++$nextOrder,
                'is_active' => true,
            ]);
        }
    }
}