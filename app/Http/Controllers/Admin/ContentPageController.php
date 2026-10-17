<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Game;
use App\Models\Page;
use App\Models\PreorderPage;
use App\Services\Content\ContentHtmlSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ContentPageController extends Controller
{
    private const RESERVED_SLUGS = [
        'cart', 'catalog', 'checkout', 'developer', 'login', 'office', 'orders',
        'product', 'profile', 'register', 'search', 'sitemap.xml',
    ];

    public function index(): View
    {
        $pages = Page::query()->orderBy('title')->paginate(20);

        return view('admin.content.pages.index', compact('pages'));
    }

    public function create(): View
    {
        return view('admin.content.pages.form', ['page' => new Page]);
    }

    public function store(Request $request, ContentHtmlSanitizer $sanitizer): RedirectResponse
    {
        $validated = $this->validatedData($request);
        $this->assertSlugAvailable($validated['slug']);
        $validated['content'] = $sanitizer->sanitize($validated['content']);

        DB::transaction(function () use ($validated): void {
            $page = Page::create($validated);
            AdminLog::record('content_page_created', Page::class, $page->id, new: $validated);
        });

        return redirect()->route('office.pages.index')->with('success', 'Halaman berhasil dibuat.');
    }

    public function edit(Page $page): View
    {
        return view('admin.content.pages.form', compact('page'));
    }

    public function update(Request $request, Page $page, ContentHtmlSanitizer $sanitizer): RedirectResponse
    {
        $validated = $this->validatedData($request, $page);
        $this->assertSlugAvailable($validated['slug'], $page);
        $validated['content'] = $sanitizer->sanitize($validated['content']);
        $old = $page->only(array_keys($validated));

        DB::transaction(function () use ($page, $validated, $old): void {
            $page->update($validated);
            AdminLog::record('content_page_updated', Page::class, $page->id, old: $old, new: $validated);
        });

        return redirect()->route('office.pages.index')->with('success', 'Halaman berhasil diperbarui.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        DB::transaction(function () use ($page): void {
            AdminLog::record('content_page_deleted', Page::class, $page->id, old: $page->only(['title', 'slug', 'status']));
            $page->delete();
        });

        return redirect()->route('office.pages.index')->with('success', 'Halaman berhasil dihapus.');
    }

    private function validatedData(Request $request, ?Page $page = null): array
    {
        $request->merge(['slug' => Str::slug((string) $request->input('slug'))]);

        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'alpha_dash:ascii', 'max:255', Rule::unique('pages', 'slug')->ignore($page?->id)],
            'content' => ['required', 'string', 'max:100000'],
            'featured_image_path' => ['nullable', 'string', 'max:2048'],
            'status' => ['required', Rule::in(['draft', 'publish'])],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ]);
    }

    private function assertSlugAvailable(string $slug, ?Page $page = null): void
    {
        if (in_array($slug, self::RESERVED_SLUGS, true)
            || Game::query()->where('slug', $slug)->exists()
            || PreorderPage::query()->where('slug', $slug)->exists()) {
            throw ValidationException::withMessages([
                'slug' => 'Slug ini sudah digunakan atau dicadangkan oleh route sistem.',
            ]);
        }
    }
}
