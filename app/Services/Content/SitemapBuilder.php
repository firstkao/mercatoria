<?php

namespace App\Services\Content;

use App\Models\Developer;
use App\Models\Game;
use App\Models\Page;
use App\Models\PreorderPage;
use App\Models\Product;

class SitemapBuilder
{
    public function render(): string
    {
        $urls = collect([[
            'loc' => route('home'),
            'lastmod' => now()->toAtomString(),
        ]]);

        Game::query()->active()->select(['id', 'slug', 'updated_at'])->orderBy('id')->chunk(500, function ($games) use ($urls): void {
            foreach ($games as $game) {
                $urls->push(['loc' => route('games.show', $game->slug), 'lastmod' => $game->updated_at->toAtomString()]);
            }
        });

        Developer::query()->active()->select(['id', 'slug', 'updated_at'])->orderBy('id')->chunk(500, function ($developers) use ($urls): void {
            foreach ($developers as $developer) {
                $urls->push(['loc' => route('developers.show', $developer->slug), 'lastmod' => $developer->updated_at->toAtomString()]);
            }
        });

        Product::query()->active()->select(['id', 'slug', 'updated_at'])->orderBy('id')->chunk(500, function ($products) use ($urls): void {
            foreach ($products as $product) {
                $urls->push(['loc' => route('products.direct', $product->slug), 'lastmod' => $product->updated_at->toAtomString()]);
            }
        });

        Page::query()->published()->select(['id', 'slug', 'updated_at'])->orderBy('id')->chunk(500, function ($pages) use ($urls): void {
            foreach ($pages as $page) {
                $urls->push(['loc' => route('content.show', $page->slug), 'lastmod' => $page->updated_at->toAtomString()]);
            }
        });

        PreorderPage::query()->published()->select(['id', 'slug', 'updated_at'])->orderBy('id')->chunk(500, function ($pages) use ($urls): void {
            foreach ($pages as $page) {
                $urls->push(['loc' => route('content.show', $page->slug), 'lastmod' => $page->updated_at->toAtomString()]);
            }
        });

        return view('seo.sitemap', ['urls' => $urls])->render();
    }
}
