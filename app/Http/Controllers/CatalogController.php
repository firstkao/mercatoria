<?php

namespace App\Http\Controllers;

use App\Models\Developer;
use App\Models\Game;
use App\Models\MerchCategory;
use App\Models\Product;
use App\Models\ProductView;
use App\Models\Tag;
use App\Models\User;
use App\Services\User\ShareClickService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        return $this->listing($request, $this->activeProducts(), 'Katalog Mercatoria');
    }

    public function showGame(Request $request, Game $game): View
    {
        abort_unless($game->is_active, 404);

        return $this->listing(
            $request,
            $this->activeProducts()->whereHas('games', fn (Builder $query) => $query->whereKey($game->id)),
            $game->name,
            $game,
        );
    }

    public function showCategory(Request $request, Game $game, MerchCategory $category): View
    {
        abort_unless($game->is_active, 404);

        $products = $this->activeProducts()
            ->whereHas('games', fn (Builder $query) => $query->whereKey($game->id))
            ->whereHas('merchCategories', fn (Builder $query) => $query->whereKey($category->id));

        return $this->listing($request, $products, "{$game->name} — {$category->name}", $game, $category);
    }

    public function showDeveloper(Request $request, Developer $developer): View
    {
        abort_unless($developer->is_active, 404);

        $products = $this->activeProducts()
            ->whereHas('games', fn (Builder $query) => $query
                ->where('is_active', true)
                ->whereHas('developers', fn (Builder $developerQuery) => $developerQuery->whereKey($developer->id)));

        return $this->listing($request, $products, $developer->name, developer: $developer);
    }

    public function search(Request $request): View
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'max:100'],
        ]);

        return $this->listing(
            $request,
            $this->activeProducts()->search(trim($validated['q'])),
            'Hasil pencarian',
            search: trim($validated['q']),
            view: 'search.index',
        );
    }

    public function showProduct(Request $request, Product $product, ShareClickService $shareClicks): View
    {
        abort_unless($product->is_active, 404);
        $this->recordShareClick($request, $product, $shareClicks);

        return $this->productDetail($product, $product->games()->where('is_active', true)->first());
    }

    public function showProductInGame(Request $request, Game $game, Product $product, ShareClickService $shareClicks): View
    {
        abort_unless($game->is_active && $product->is_active, 404);
        abort_unless($game->products()->whereKey($product->id)->exists(), 404);
        $this->recordShareClick($request, $product, $shareClicks);

        return $this->productDetail($product, $game);
    }

    private function recordShareClick(Request $request, Product $product, ShareClickService $shareClicks): void
    {
        $code = $request->query('ref');
        if (! is_string($code) || ! preg_match('/^[A-Za-z0-9]{1,16}$/', $code)) {
            return;
        }

        $referrer = User::query()->where('referral_code', $code)->first();
        if ($referrer && ! $request->user()?->is($referrer)) {
            $shareClicks->record($referrer, $product, (string) $request->ip());
        }
    }

    private function listing(
        Request $request,
        Builder $products,
        string $title,
        ?Game $game = null,
        ?MerchCategory $category = null,
        ?Developer $developer = null,
        ?string $search = null,
        string $view = 'catalog.index',
    ): View {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'game' => ['nullable', 'string', 'max:120'],
            'tag' => ['nullable', 'string', 'max:80'],
            'category' => ['nullable', 'string', 'max:100'],
            'availability' => ['nullable', 'in:tersedia,kosong'],
            'sort' => ['nullable', 'in:default,latest,price_asc,price_desc,popular,name'],
            'min_price' => ['nullable', 'numeric', 'min:0', 'max:9999999999999.99'],
            'max_price' => ['nullable', 'numeric', 'min:0', 'max:9999999999999.99'],
        ]);

        $priceBounds = (clone $products)
            ->selectRaw('MIN('.$this->finalPriceSql().') AS lowest, MAX('.$this->finalPriceSql().') AS highest')
            ->toBase()
            ->first();

        $products
            ->when($filters['q'] ?? null, fn (Builder $query, string $queryText) => $query->search(trim($queryText)))
            ->when($filters['game'] ?? null, fn (Builder $query, string $gameSlug) => $query->whereHas(
                'games',
                fn (Builder $gameQuery) => $gameQuery->where('slug', $gameSlug),
            ))
            ->when($filters['tag'] ?? null, fn (Builder $query, string $tag) => $query->whereHas(
                'tags',
                fn (Builder $tagQuery) => $tagQuery->where('slug', $tag),
            ))
            ->when($filters['category'] ?? null, fn (Builder $query, string $categorySlug) => $query->whereHas(
                'merchCategories',
                fn (Builder $categoryQuery) => $categoryQuery->where('slug', $categorySlug),
            ))
            ->when($filters['availability'] ?? null, fn (Builder $query, string $availability) => $query->where('availability', $availability));

        if (array_key_exists('min_price', $filters)) {
            $products->whereRaw(
                $this->finalPriceSql().'>= '.number_format((float) $filters['min_price'], 2, '.', ''),
            );
        }

        if (array_key_exists('max_price', $filters)) {
            $products->whereRaw(
                $this->finalPriceSql().'<= '.number_format((float) $filters['max_price'], 2, '.', ''),
            );
        }

        $keyword = trim((string) ($search ?? ($filters['q'] ?? '')));

        match ($filters['sort'] ?? 'default') {
            'price_asc' => $products->orderByRaw($this->finalPriceSql().' ASC')->orderByDesc('id'),
            'price_desc' => $products->orderByRaw($this->finalPriceSql().' DESC')->orderByDesc('id'),
            'popular' => $products->orderByDesc('sold_count')->orderByDesc('id'),
            'latest' => $products->orderByDesc('created_at')->orderByDesc('id'),
            'name' => $products->orderBy('name'),
            // Pengurutan standar: relevansi untuk search, abjad untuk katalog.
            default => $keyword !== ''
                ? $products
                    ->orderByRaw('CASE WHEN products.name LIKE ? THEN 0 ELSE 1 END', ['%'.addcslashes($keyword, '\\%_').'%'])
                    ->orderBy('name')
                : $products->orderBy('name')->orderBy('id'),
        };

        $perPage = max(1, min(100, (int) setting('catalog_per_page', 30)));
        $products = $products
            ->with(['games', 'tags', 'merchCategories', 'variants'])
            ->paginate($perPage)
            ->withQueryString();

        $recentIds = $request->user()
            ? ProductView::query()
                ->where('user_id', $request->user()->id)
                ->latest('id')
                ->limit(30)
                ->pluck('product_id')
                ->unique()
                ->take(5)
                ->values()
            : collect();
        $recentViews = $recentIds->isEmpty()
            ? collect()
            : Product::query()
                ->active()
                ->whereIn('id', $recentIds)
                ->with(['games', 'variants'])
                ->get()
                ->sortBy(fn (Product $viewed) => $recentIds->search($viewed->id))
                ->values();

        return view($view, [
            'products' => $products,
            'title' => $title,
            'game' => $game,
            'category' => $category,
            'developer' => $developer,
            'search' => $search ?? ($filters['q'] ?? ''),
            'filters' => $filters,
            'games' => Game::active()->withCount(['products' => fn (Builder $query) => $query->active()])->orderBy('name')->get(),
            'developers' => Developer::active()->orderBy('name')->get(),
            'categories' => MerchCategory::query()->orderBy('sort_order')->orderBy('name')->get(),
            'tags' => Tag::query()->orderBy('name')->get(),
            'bestOffers' => Product::query()->active()->bestOffer(5)->with(['games', 'tags', 'variants'])->get(),
            'recentViews' => $recentViews,
            'priceMin' => (int) floor((float) ($priceBounds->lowest ?? 0)),
            'priceMax' => (int) ceil((float) ($priceBounds->highest ?? 0)),
        ]);
    }

    private function productDetail(Product $product, ?Game $game): View
    {
        $product->load(['games.developers', 'tags', 'merchCategories', 'variants.combinations']);

        $user = request()->user();
        if ($user) {
            ProductView::query()->create([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'ip_address' => request()->ip(),
            ]);
        }

        $remainingViews = null;
        if ($user?->currentState()->hasViewLimit()) {
            $viewed = $user->productViews()
                ->whereDate('created_at', today())
                ->distinct('product_id')
                ->count('product_id');
            $remainingViews = max(0, 10 - $viewed);
        }

        $relatedGame = $game
            ? $product->games->firstWhere('id', $game->id)
            : $product->games->first();
        $developerIds = $relatedGame?->developers->modelKeys() ?? [];
        $relatedProducts = collect();

        if ($relatedGame || $developerIds !== []) {
            $relatedProducts = Product::query()
                ->active()
                ->where('id', '!=', $product->id)
                ->where(function (Builder $query) use ($relatedGame, $developerIds): void {
                    if ($relatedGame) {
                        $query->whereHas('games', fn (Builder $gameQuery) => $gameQuery
                            ->whereKey($relatedGame->id)
                            ->where('is_active', true));
                    }

                    if ($developerIds !== []) {
                        $method = $relatedGame ? 'orWhereHas' : 'whereHas';
                        $query->{$method}('games', fn (Builder $gameQuery) => $gameQuery
                            ->where('is_active', true)
                            ->whereHas('developers', fn (Builder $developerQuery) => $developerQuery
                                ->whereIn('developers.id', $developerIds)
                                ->where('developers.is_active', true)));
                    }
                })
                ->with(['games', 'tags', 'variants'])
                ->inRandomOrder()
                ->limit(6)
                ->get();
        }

        return view('catalog.show', compact('product', 'game', 'relatedProducts', 'remainingViews'));
    }

    private function activeProducts(): Builder
    {
        return Product::query()
            ->active()
            ->whereHas('games', fn (Builder $query) => $query->active());
    }

    private function finalPriceSql(): string
    {
        $rate = number_format((float) setting('rate_rmb_idr', 2200), 8, '.', '');
        $profit = number_format((float) setting('profit_percent', 11), 8, '.', '');
        $basePrice = "((products.price_rmb + products.shipping_cn_rmb) * {$rate} + products.shipping_cn_btm_idr + products.shipping_btm_jkt_idr) * (1 + {$profit} / 100)";
        $discount = '(CASE WHEN products.discount_amount > 0 AND (products.discount_start IS NULL OR products.discount_start <= CURRENT_TIMESTAMP) AND (products.discount_end IS NULL OR products.discount_end >= CURRENT_TIMESTAMP) THEN products.discount_amount ELSE 0 END)';
        $priceAfterDiscount = "{$basePrice} - {$discount}";

        return "CASE WHEN ({$priceAfterDiscount}) < 0 THEN 0 ELSE ({$priceAfterDiscount}) END";
    }
}
