<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\Seo\SeoMetaService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function __construct(private SeoMetaService $seo) {}

    /**
     * Halaman CMS publik (PRD 25/36). Bila belum ada di CMS → placeholder "Menyusul".
     */
    public function show(Request $request, string $slug): View
    {
        $page = Page::where('slug', $slug)->first();

        // Halaman nonaktif / tidak ada → 404 untuk CMS yang sudah dibuat;
        // slug tanpa entri CMS sama sekali → placeholder "Menyusul".
        if ($page && $page->status !== 'publish') {
            abort(404);
        }

        if (! $page) {
            return view('coming-soon');
        }

        return view('frontend.page.show', [
            'page' => $page,
            'seo' => $this->seo->meta($page->title, $page->meta_description),
        ]);
    }
}
