<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Page;
use App\Models\PreorderPage;
use App\Services\Content\ContentHtmlSanitizer;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublishedContentController extends Controller
{
    public function show(Request $request, string $slug, ContentHtmlSanitizer $sanitizer): View
    {
        $page = Page::query()->published()->where('slug', $slug)->first();
        if ($page) {
            return view('content.page', [
                'title' => $page->meta_title ?: $page->title,
                'description' => $page->meta_description,
                'content' => $sanitizer->sanitize($page->content),
                'page' => $page,
            ]);
        }

        $preorderPage = PreorderPage::query()->published()->where('slug', $slug)->first();
        if ($preorderPage) {
            return view('content.preorder', [
                'title' => $preorderPage->meta_title ?: $preorderPage->title,
                'description' => $preorderPage->meta_description,
                'content' => $sanitizer->sanitize($preorderPage->html_content ?? ''),
                'page' => $preorderPage,
            ]);
        }

        $game = Game::query()->active()->where('slug', $slug)->firstOrFail();

        return app(CatalogController::class)->showGame($request, $game);
    }
}
