<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\HeroSlide;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HeroSlideController extends Controller
{
    public function index(): View
    {
        return view('admin.hero-slides.index', [
            'slides' => HeroSlide::orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateSlide($request);
        $data['is_active'] = $request->boolean('is_active');

        $slide = HeroSlide::create($data);
        AdminLog::record($request->user(), 'create', 'HeroSlide', $slide->id, null, $data, 'Tambah hero slide');

        return redirect()->route('office.hero-slides.index')->with('status', 'Hero slide dibuat.');
    }

    public function update(Request $request, HeroSlide $heroSlide): RedirectResponse
    {
        $data = $this->validateSlide($request, $heroSlide);
        $data['is_active'] = $request->boolean('is_active');

        [$old, $new] = AdminLog::diff($heroSlide, ['image', 'sort_order', 'is_active'], $data);
        $heroSlide->update($data);
        AdminLog::record($request->user(), 'update', 'HeroSlide', $heroSlide->id, $old, $new, 'Ubah hero slide');

        return redirect()->route('office.hero-slides.index')->with('status', 'Hero slide diperbarui.');
    }

    public function destroy(Request $request, HeroSlide $heroSlide): RedirectResponse
    {
        AdminLog::record($request->user(), 'delete', 'HeroSlide', $heroSlide->id, $heroSlide->only(['image', 'sort_order', 'is_active']), null, 'Hapus hero slide');
        if (str_starts_with((string) $heroSlide->image, 'hero-slides/')) {
            @unlink(storage_path('app/private/'.$heroSlide->image));
        }
        $heroSlide->delete();

        return redirect()->route('office.hero-slides.index')->with('status', 'Hero slide dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateSlide(Request $request, ?HeroSlide $slide = null): array
    {
        $data = $request->validate([
            'image' => [$slide ? 'nullable' : 'required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        if ($request->hasFile('image')) {
            if ($slide?->image && str_starts_with((string) $slide->image, 'hero-slides/')) {
                @unlink(storage_path('app/private/'.$slide->image));
            }
            $data['image'] = $request->file('image')->store('hero-slides');
        }

        return $data;
    }
}
