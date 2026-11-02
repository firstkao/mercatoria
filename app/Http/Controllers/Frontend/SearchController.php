<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\MerchCategory;
use App\Models\Product;
use App\Models\Tag;
use App\Services\Order\ShippingCalculatorService;
use App\Services\Product\ProductSearchService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __construct(
        private ProductSearchService $search,
        private ShippingCalculatorService $shipping,
    ) {}

    public function index(Request $request): View
    {
        $q = (string) $request->query('q', '');
        $filters = [
            'game_id' => $request->query('game_id'),
            'merch_category_id' => $request->query('merch_category_id'),
            'tag' => $request->query('tag'),
        ];

        $results = $this->search->search($q, array_filter($filters), (int) setting('catalog_per_page', 30), $request);

        // Varian yang cocok keyword (untuk badge "Varian tersedia" — PRD 13.13).
        $matchedVariants = collect($results->items())
            ->mapWithKeys(function (Product $product) use ($q) {
                if ($q === '') {
                    return [$product->id => null];
                }

                $variant = $product->variants
                    ->first(fn ($v) => str_contains(mb_strtolower($v->name), mb_strtolower($q))
                        || str_contains(mb_strtolower($v->sku), mb_strtolower($q)));

                return [$product->id => $variant];
            });

        return view('frontend.search.index', [
            'q' => $q,
            'results' => $results,
            'matchedVariants' => $matchedVariants,
            'games' => Game::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'categories' => MerchCategory::orderBy('priority')->get(),
            'tags' => Tag::orderBy('name')->get(),
            'filters' => $filters,
            'shipping' => $this->shipping,
        ]);
    }
}
