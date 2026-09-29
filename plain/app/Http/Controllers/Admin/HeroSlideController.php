<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\HeroSlide;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class HeroSlideController extends Controller
{
    public function index(): View
    {
        return view('admin.hero-slides.index', [
            'slides' => HeroSlide::query()->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.hero-slides.form', [
            'slide' => new HeroSlide(['is_active' => true, 'sort_order' => 0]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            $data['image_path'] = $this->storeImage($request->file('image'));
        }

        $slide = HeroSlide::create($data);
        AdminLog::record('create_hero_slide', $slide, ['title' => $slide->title]);

        return redirect()->route('admin.hero-slides.index')->with('status', 'Slide ditambahkan.');
    }

    public function edit(HeroSlide $heroSlide): View
    {
        return view('admin.hero-slides.form', ['slide' => $heroSlide]);
    }

    public function update(Request $request, HeroSlide $heroSlide): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            if ($heroSlide->image_path) {
                Storage::disk('public')->delete($heroSlide->image_path);
            }
            $data['image_path'] = $this->storeImage($request->file('image'));
        }

        $heroSlide->update($data);
        AdminLog::record('update_hero_slide', $heroSlide, ['title' => $heroSlide->title]);

        return redirect()->route('admin.hero-slides.index')->with('status', 'Slide diperbarui.');
    }

    public function destroy(HeroSlide $heroSlide): RedirectResponse
    {
        if ($heroSlide->image_path) {
            Storage::disk('public')->delete($heroSlide->image_path);
        }

        $title = $heroSlide->title;
        $heroSlide->delete();
        AdminLog::record('delete_hero_slide', null, ['title' => $title]);

        return redirect()->route('admin.hero-slides.index')->with('status', 'Slide dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title'      => ['nullable', 'string', 'max:150'],
            'subtitle'   => ['nullable', 'string', 'max:255'],
            'link_url'   => ['nullable', 'url', 'max:500'],
            'alt_text'   => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active'  => ['nullable', 'boolean'],
            'image'      => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $data['is_active'] = ! empty($data['is_active']);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        unset($data['image']); // file ditangani terpisah

        return $data;
    }

    private function storeImage($file): string
    {
        $filename = 'hero-slides/' . Str::random(40) . '.webp';
        $manager = new ImageManager(new Driver());
        $img = $manager->read($file)->scaleDown(width: 1920);
        Storage::disk('public')->put($filename, (string) $img->toWebp(80));

        return $filename;
    }
}