<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Developer;
use App\Models\Game;
use App\Models\MerchCategory;
use App\Models\Product;
use App\Services\Order\ShippingCalculatorService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function __construct(private ShippingCalculatorService $shipping) {}

    public function game(Request $request, Game $game): View
    {
        return $this->renderCatalog($request, $game, null);
    }

    public function category(Request $request, Game $game, MerchCategory $category): View
    {
        return $this->renderCatalog($request, $game, $category);
    }

    public function developer(Request $request, Developer $developer): View
    {
        $products = Product::query()
            ->where('is_active', true)
            ->whereHas('game.developers', fn ($q) => $q->where('developers.id', $developer->id))
            ->with(['game', 'tags', 'variants'])
            ->when($request->sort === 'termurah', fn ($q) => $q->orderBy('price_rmb'))
            ->when($request->sort === 'termahal', fn ($q) => $q->orderByDesc('price_rmb'))
            ->when($request->sort !== 'termurah' && $request->sort !== 'termahal', fn ($q) => $q->orderByDesc('id'))
            ->paginate((int) setting('catalog_per_page', 30))
            ->withQueryString();

        return $this->catalogView($developer->name, $products, null, null, $developer);
    }

    /**
     * @param  LengthAwarePaginator  $products
     */
    private function renderCatalog(Request $request, Game $game, ?MerchCategory $category): View
    {
        $products = Product::query()
            ->where('is_active', true)
            ->where('game_id', $game->id)
            ->when($category, fn ($q) => $q->where('merch_category_id', $category->id))
            ->with(['game', 'tags', 'variants'])
            ->when($request->sort === 'termurah', fn ($q) => $q->orderBy('price_rmb'))
            ->when($request->sort === 'termahal', fn ($q) => $q->orderByDesc('price_rmb'))
            ->when($request->sort !== 'termurah' && $request->sort !== 'termahal', fn ($q) => $q->orderByDesc('id'))
            ->paginate((int) setting('catalog_per_page', 30))
            ->withQueryString();

        return $this->catalogView($game->name, $products, $game, $category);
    }

    private function catalogView(
        string $title,
        $products,
        ?Game $game = null,
        ?MerchCategory $activeCategory = null,
        ?Developer $developer = null
    ): View {
        return view('frontend.catalog.game', [
            'title' => $title,
            'products' => $products,
            'game' => $game,
            'developer' => $developer,
            'activeCategory' => $activeCategory,
            'games' => Game::where('is_active', true)->orderBy('name')->get(['id', 'name', 'slug']),
            'categories' => MerchCategory::orderBy('priority')->get(),
            'bestOffers' => Product::where('is_active', true)
                ->whereNotNull('discount_amount')
                ->orderByDesc('discount_amount')
                ->limit((int) setting('best_offers_count', 5))
                ->with(['game', 'tags'])
                ->get(),
            'shipping' => $this->shipping,
        ]);
    }
}
