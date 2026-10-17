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

class PreorderPageController extends Controller
{
    public function index(): View
    {
        $pages = PreorderPage::query()->orderBy('title')->paginate(20);

        return view('admin.content.preorders.index', compact('pages'));
    }

    public function create(): View
    {
        return view('admin.content.preorders.form', ['page' => new PreorderPage]);
    }

    public function store(Request $request, ContentHtmlSanitizer $sanitizer): RedirectResponse
    {
        $validated = $this->validatedData($request);
        $this->assertSlugAvailable($validated['slug']);
        $validated['html_content'] = $sanitizer->sanitize($validated['html_content'] ?? '');

        DB::transaction(function () use ($validated): void {
            $page = PreorderPage::create($validated);
            AdminLog::record('preorder_page_created', PreorderPage::class, $page->id, new: $validated);
        });

        return redirect()->route('office.preorder-pages.index')->with('success', 'Halaman preorder berhasil dibuat.');
    }

    public function edit(PreorderPage $preorderPage): View
    {
        return view('admin.content.preorders.form', ['page' => $preorderPage]);
    }

    public function update(Request $request, PreorderPage $preorderPage, ContentHtmlSanitizer $sanitizer): RedirectResponse
    {
        $validated = $this->validatedData($request, $preorderPage);
        $this->assertSlugAvailable($validated['slug'], $preorderPage);
        $validated['html_content'] = $sanitizer->sanitize($validated['html_content'] ?? '');
        $old = $preorderPage->only(array_keys($validated));

        DB::transaction(function () use ($preorderPage, $validated, $old): void {
            $preorderPage->update($validated);
            AdminLog::record('preorder_page_updated', PreorderPage::class, $preorderPage->id, old: $old, new: $validated);
        });

        return redirect()->route('office.preorder-pages.index')->with('success', 'Halaman preorder berhasil diperbarui.');
    }

    public function destroy(PreorderPage $preorderPage): RedirectResponse
    {
        DB::transaction(function () use ($preorderPage): void {
            AdminLog::record('preorder_page_deleted', PreorderPage::class, $preorderPage->id, old: $preorderPage->only(['title', 'slug', 'status']));
            $preorderPage->delete();
        });

        return redirect()->route('office.preorder-pages.index')->with('success', 'Halaman preorder berhasil dihapus.');
    }

    private function validatedData(Request $request, ?PreorderPage $page = null): array
    {
        $request->merge(['slug' => Str::slug((string) $request->input('slug'))]);

        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'alpha_dash:ascii', 'max:255', Rule::unique('preorder_pages', 'slug')->ignore($page?->id)],
            'grapes_json' => ['required', 'json', 'max:100000'],
            'html_content' => ['nullable', 'string', 'max:100000'],
            'status' => ['required', Rule::in(['draft', 'publish'])],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ]);
    }

    private function assertSlugAvailable(string $slug, ?PreorderPage $page = null): void
    {
        if (in_array($slug, ['cart', 'catalog', 'checkout', 'developer', 'login', 'office', 'orders', 'product', 'profile', 'register', 'search', 'sitemap.xml'], true)
            || Game::query()->where('slug', $slug)->exists()
            || Page::query()->where('slug', $slug)->exists()) {
            throw ValidationException::withMessages([
                'slug' => 'Slug ini sudah digunakan atau dicadangkan oleh route sistem.',
            ]);
        }
    }
}
