<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Page;
use App\Models\PreorderPage;
use App\Services\Seo\SeoMetaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicSlugController extends Controller
{
    public function __construct(
        private SeoMetaService $seo,
        private CatalogController $catalog,
    ) {}

    public function show(Request $request, string $slug): View|RedirectResponse
    {
        $page = Page::where('slug', $slug)->first();

        if ($page) {
            if ($page->status !== 'publish') {
                abort(404);
            }

            return view('frontend.page.show', [
                'page' => $page,
                'seo' => $this->seo->meta($page->title, $page->meta_description),
            ]);
        }

        $preorderPage = PreorderPage::where('slug', $slug)->first();

        if ($preorderPage) {
            if ($preorderPage->status !== 'publish') {
                abort(404);
            }

            return view('frontend.preorder.show', [
                'page' => $preorderPage,
                'seo' => $this->seo->meta($preorderPage->title, $preorderPage->meta_description),
            ]);
        }

        if (in_array($slug, ['tentang-kami', 'faq', 'cara-belanja', 'tnc', 'kebijakan-privasi', 'preorder-baru'], true)) {
            return view('coming-soon');
        }

        if (! $request->user()) {
            return redirect()->guest(route('login'));
        }

        $game = Game::where('slug', $slug)->firstOrFail();

        return $this->catalog->game($request, $game);
    }
}
