<?php

namespace App\Services\Seo;

use App\Models\Game;
use App\Models\Page;
use App\Models\PreorderPage;
use App\Models\Product;

class SeoMetaService
{
    /**
     * Meta tags dinamis + Open Graph + Twitter Card + JSON-LD (PRD 29).
     *
     * @return array<string, mixed>
     */
    public function meta(string $title, ?string $description = null, ?string $image = null): array
    {
        $description ??= 'Mercatoria — merch & collectibles store untuk IP favoritmu.';
        $image ??= url('/logo.png');
        $url = url()->current();

        return [
            'title' => $title,
            'description' => $description,
            'image' => $image,
            'url' => $url,
        ];
    }

    /**
     * JSON-LD: Organization + (opsional) Product (PRD 29).
     */
    public function jsonLd(?Product $product = null): array
    {
        $graph = [
            [
                '@type' => 'Organization',
                'name' => 'Mercatoria',
                'url' => url('/'),
                'logo' => url('/logo.png'),
            ],
        ];

        if ($product) {
            $graph[] = [
                '@type' => 'Product',
                'name' => $product->name,
                'sku' => $product->sku,
                'url' => '/'.$product->game->slug.'/'.$product->slug,
            ];
        } else {
            foreach (Product::where('is_active', true)->limit(5)->with('game')->get() as $p) {
                $graph[] = [
                    '@type' => 'Product',
                    'name' => $p->name,
                    'sku' => $p->sku,
                    'url' => '/'.$p->game->slug.'/'.$p->slug,
                ];
            }
        }

        return ['@context' => 'https://schema.org', '@graph' => $graph];
    }

    /**
     * Sitemap URL list (PRD 29: game + produk + halaman statis).
     *
     * @return array<int, string>
     */
    public function sitemapUrls(): array
    {
        $urls = ['/'];

        foreach (Game::where('is_active', true)->get(['slug']) as $game) {
            $urls[] = '/'.$game->slug;
        }

        foreach (Product::where('is_active', true)->with('game')->get(['slug', 'game_id']) as $product) {
            if ($product->game) {
                $urls[] = '/'.$product->game->slug.'/'.$product->slug;
            }
        }

        foreach (['tentang-kami', 'faq', 'cara-belanja', 'tnc', 'kebijakan-privasi'] as $slug) {
            $urls[] = '/'.$slug;
        }

        foreach (Page::where('status', 'publish')->get(['slug']) as $page) {
            $urls[] = '/'.$page->slug;
        }

        foreach (PreorderPage::where('status', 'publish')->get(['slug']) as $page) {
            $urls[] = '/'.$page->slug;
        }

        return array_values(array_unique($urls));
    }
}
