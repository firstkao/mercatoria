<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\HeroSlide;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;

class HeroSlideController extends Controller
{
    public function index(): View
    {
        return view('admin.promotions.hero-slides.index', [
            'slides' => HeroSlide::query()->orderBy('sort_order')->orderBy('id')->paginate(30),
        ]);
    }

    public function create(): View
    {
        return view('admin.promotions.hero-slides.form', ['slide' => new HeroSlide]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request, true);
        $imagePath = $request->file('image')->store('hero-slides', 'public');
        if (! $imagePath) {
            throw new RuntimeException('Gagal menyimpan gambar hero slide.');
        }
        $validated['image_path'] = $imagePath;
        unset($validated['image']);

        DB::transaction(function () use ($validated): void {
            $slide = HeroSlide::create($validated);
            AdminLog::record('hero_slide_created', HeroSlide::class, $slide->id, new: $validated);
        });

        return redirect()->route('office.hero-slides.index')->with('success', 'Hero slide berhasil ditambahkan.');
    }

    public function edit(HeroSlide $heroSlide): View
    {
        return view('admin.promotions.hero-slides.form', ['slide' => $heroSlide]);
    }

    public function update(Request $request, HeroSlide $heroSlide): RedirectResponse
    {
        $validated = $this->validatedData($request, false);
        $oldImage = $heroSlide->image_path;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('hero-slides', 'public');
            if (! $imagePath) {
                throw new RuntimeException('Gagal menyimpan gambar hero slide.');
            }
            $validated['image_path'] = $imagePath;
        } else {
            $validated['image_path'] = $oldImage;
        }
        unset($validated['image']);
        $old = $heroSlide->only(array_keys($validated));

        DB::transaction(function () use ($heroSlide, $validated, $old): void {
            $heroSlide->update($validated);
            AdminLog::record('hero_slide_updated', HeroSlide::class, $heroSlide->id, old: $old, new: $validated);
        });
        if ($oldImage !== $heroSlide->image_path) {
            $this->deleteImage($oldImage);
        }

        return redirect()->route('office.hero-slides.index')->with('success', 'Hero slide berhasil diperbarui.');
    }

    public function destroy(HeroSlide $heroSlide): RedirectResponse
    {
        DB::transaction(function () use ($heroSlide): void {
            AdminLog::record('hero_slide_deleted', HeroSlide::class, $heroSlide->id, old: $heroSlide->only(['image_path', 'title', 'link_url']));
            $heroSlide->delete();
        });
        $this->deleteImage($heroSlide->image_path);

        return redirect()->route('office.hero-slides.index')->with('success', 'Hero slide berhasil dihapus.');
    }

    private function validatedData(Request $request, bool $imageRequired): array
    {
        return $request->validate([
            'image' => [$imageRequired ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'link_url' => ['nullable', 'string', 'max:500', 'regex:#^(https?://|/(?!/))#i'],
            'title' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:100000'],
            'is_active' => ['required', 'boolean'],
        ]);
    }

    private function deleteImage(string $path): void
    {
        $disk = Storage::disk('public');
        if ($disk->exists($path) && ! $disk->delete($path)) {
            Log::error('Failed to delete hero slide image.', ['path' => $path]);
            throw new RuntimeException('Gagal menghapus gambar hero slide dari storage.');
        }
    }
}
