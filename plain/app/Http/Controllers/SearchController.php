<?php

namespace App\Http\Controllers;

use App\Models\Developer;
use App\Models\Game;
use App\Models\Page;
use App\Models\Product;
use App\Support\PriceCalculator;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q'));
        $calculator = PriceCalculator::fromSettings();

        $products = collect();
        $pages = collect();
        $games = collect();
        $developers = collect();

        if ($q !== '') {
            $products = Product::query()
                ->published()
                ->where(function ($inner) use ($q) {
                    $inner->where('name', 'like', "%{$q}%")
                        ->orWhere('sku', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%")
                        ->orWhereHas('game', fn ($g) => $g->where('name', 'like', "%{$q}%"))
                        ->orWhereHas('developer', fn ($d) => $d->where('name', 'like', "%{$q}%"))
                        ->orWhereHas('variants', fn ($v) => $v->where('name', 'like', "%{$q}%")->orWhere('sku', 'like', "%{$q}%"));
                })
                ->with(['images', 'variants', 'shippingTier'])
                ->latest()
                ->take(48)
                ->get();

            $pages = Page::query()
                ->where('is_published', true)
                ->where(fn ($inner) => $inner->where('title', 'like', "%{$q}%")->orWhere('content', 'like', "%{$q}%"))
                ->take(6)
                ->get();

            $games = Game::query()
                ->where('name', 'like', "%{$q}%")
                ->take(6)
                ->get();

            $developers = Developer::query()
                ->where('name', 'like', "%{$q}%")
                ->take(6)
                ->get();
        }

        return view('search.index', [
            'q' => $q,
            'products' => $products,
            'pages' => $pages,
            'games' => $games,
            'developers' => $developers,
            'calculator' => $calculator,
            'user' => $request->user(),
            'title' => $q !== '' ? "Cari: {$q}" : 'Cari Produk',
            'metaDescription' => $q !== '' ? "Hasil pencarian untuk: {$q}" : 'Cari merchandise game original di MERCATORIA.',
        ]);
    }
}