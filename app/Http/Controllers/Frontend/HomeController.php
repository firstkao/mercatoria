<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Product;
use App\Services\Seo\SeoMetaService;
use Illuminate\Http\Response;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(private SeoMetaService $seo) {}

    /**
     * Landing (PRD 40): Top Promo Bar · Hero · Jelajahi Game · Merch Baru · Footer.
     */
    public function index(): View
    {
        $games = Game::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)
            ->with(['game', 'tags'])
            ->orderByDesc('id')
            ->limit(14)
            ->get();

        return view('frontend.home.index', [
            'games' => $games,
            'products' => $products,
            'seo' => $this->seo->meta('Mercatoria — Merch & Collectibles Store'),
            'jsonLd' => $this->seo->jsonLd(),
        ]);
    }

    public function sitemap(): Response
    {
        $urls = collect($this->seo->sitemapUrls())
            ->map(fn (string $path) => '<url><loc>'.e(url($path)).'</loc></url>')
            ->implode('');

        return response('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.$urls.'</urlset>', 200)
            ->header('Content-Type', 'application/xml');
    }

    public function robots(): Response
    {
        $content = "User-agent: *\n".
            "Disallow: /office\n".
            "Disallow: /cart\n".
            "Disallow: /checkout\n".
            "Disallow: /profile\n".
            "Disallow: /orders\n\n".
            'Sitemap: '.url('/sitemap.xml')."\n";

        return response($content, 200)->header('Content-Type', 'text/plain');
    }
}
