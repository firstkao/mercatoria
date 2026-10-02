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
            // BUG FIX fungsi: escape wildcard LIKE (% dan _ di query user dulu) —
            // sebelumnya kata "100%" bisa menghasilkan hasil ngawur.
            $like = '%'.addcslashes($q, '%_\\').'%';

            $products = Product::query()
                ->published()
                ->where(function ($inner) use ($like) {
                    $inner->where('name', 'like', $like)
                        ->orWhere('sku', 'like', $like)
                        ->orWhere('description', 'like', $like)
                        ->orWhereHas('game', fn ($g) => $g->where('name', 'like', $like))
                        ->orWhereHas('developer', fn ($d) => $d->where('name', 'like', $like))
                        ->orWhereHas('variants', fn ($v) => $v->where('name', 'like', $like)->orWhere('sku', 'like', $like));
                })
                ->with(['images', 'variants', 'shippingTier'])
                ->latest()
                ->take(48)
                ->get();

            $pages = Page::query()
                ->where('is_published', true)
                ->where(fn ($inner) => $inner->where('title', 'like', $like)->orWhere('content', 'like', $like))
                ->take(6)
                ->get();

            $games = Game::query()
                ->where('name', 'like', $like)
                ->take(6)
                ->get();

            $developers = Developer::query()
                ->where('name', 'like', $like)
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