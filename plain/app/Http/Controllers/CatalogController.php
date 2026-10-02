<?php

namespace App\Http\Controllers;

use App\Models\Developer;
use App\Models\Game;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Support\PriceCalculator;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $games = Game::query()->orderBy('sort_order')->orderBy('name')->get(['id', 'name', 'slug']);
        $developers = Developer::query()->orderBy('name')->get(['id', 'name', 'slug']);

        $selectedGame = $games->firstWhere('slug', $request->query('game'));
        $selectedDeveloper = $developers->firstWhere('slug', $request->query('developer'));
        $selectedTag = array_key_exists((string) $request->query('tag'), Product::TAGS) ? $request->query('tag') : null;

        // Jumlah produk per game/developer untuk sidebar.
        $productCountsByGame = Product::query()->published()->whereNotNull('game_id')
            ->selectRaw('game_id, COUNT(*) AS aggregate')->groupBy('game_id')->pluck('aggregate', 'game_id');
        $productCountsByDeveloper = Product::query()->published()->whereNotNull('developer_id')
            ->selectRaw('developer_id, COUNT(*) AS aggregate')->groupBy('developer_id')->pluck('aggregate', 'developer_id');
        $games->each(fn ($game) => $game->setAttribute('products_count', (int) ($productCountsByGame[$game->id] ?? 0)));
        $developers->each(fn ($dev) => $dev->setAttribute('products_count', (int) ($productCountsByDeveloper[$dev->id] ?? 0)));

        // Filter harga (rupiah).
        $minPrice = is_numeric($request->query('min_price')) ? max(0, (int) $request->query('min_price')) : null;
        $maxPrice = is_numeric($request->query('max_price')) ? max(0, (int) $request->query('max_price')) : null;
        if ($minPrice !== null && $maxPrice !== null && $minPrice > $maxPrice) {
            [$minPrice, $maxPrice] = [$maxPrice, $minPrice];
        }

        $sort = (string) $request->query('sort', '');
        $calculator = PriceCalculator::fromSettings();

        $products = Product::query()
            ->published()
            ->when($selectedGame, fn ($query) => $query->where('game_id', $selectedGame->id))
            ->when($selectedDeveloper, fn ($query) => $query->where('developer_id', $selectedDeveloper->id))
            ->when($selectedTag, fn ($query) => $query->where('tag', $selectedTag))
            ->when($minPrice !== null || $maxPrice !== null, function ($query) use ($minPrice, $maxPrice) {
                // Filter rentang harga: memakai estimasi rupiah dari price_yuan + ongkir per kg.
                $rate = (float) (Setting::get('exchange_rate') ?: 0);
                $kgRate = (float) (Setting::get('cn_id_rate_per_kg') ?: 0) + (float) (Setting::get('btm_jkt_rate_per_kg') ?: 0);
                $margin = (float) (Setting::get('margin_percent') ?: 11.0);
                $priceExpr = '(('.$rate.' * pv.price_yuan + '.$kgRate.' * pv.weight_grams / 1000) * (1 + '.$margin.' / 100))';

                $query->whereIn('id', function ($sub) use ($minPrice, $maxPrice, $priceExpr) {
                    $sub->select('pv.product_id')->from('product_variants as pv')
                        ->when($minPrice !== null, fn ($q) => $q->whereRaw($priceExpr.' >= ?', [$minPrice]))
                        ->when($maxPrice !== null, fn ($q) => $q->whereRaw($priceExpr.' <= ?', [$maxPrice]));
                });
            })
            ->with(['images', 'variants', 'shippingTier'])
            ->when(in_array($sort, ['price_asc', 'price_desc'], true), function ($query) use ($sort) {
                // Urutkan berdasarkan harga jual minimum per produk (estimasi SQL;
                // pembulatan per-varian tidak dihitung agar tetap bisa di-sort di DB).
                $rate = (float) (Setting::get('exchange_rate') ?: 0);
                $kgRate = (float) (Setting::get('cn_id_rate_per_kg') ?: 0) + (float) (Setting::get('btm_jkt_rate_per_kg') ?: 0);
                $margin = (float) (Setting::get('margin_percent') ?: 11.0);

                $minPriceSql = ProductVariant::query()
                    ->selectRaw('COALESCE(MIN(('.$rate.' * pv.price_yuan + '.$kgRate.' * pv.weight_grams / 1000) * (1 + '.$margin.' / 100)), 2147483647)')
                    ->from('product_variants as pv')
                    ->whereColumn('pv.product_id', 'products.id');

                $query->orderBy($minPriceSql, $sort === 'price_asc' ? 'asc' : 'desc');
            }, fn ($query) => $query->latest())
            ->paginate(24)
            ->withQueryString();

        return view('catalog.index', [
            'user' => $request->user(),
            'products' => $products,
            'games' => $games,
            'developers' => $developers,
            'selectedGame' => $selectedGame,
            'selectedDeveloper' => $selectedDeveloper,
            'selectedTag' => $selectedTag,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
            'calculator' => $calculator,
            'viewQuota' => Setting::integer('view_quota', 10),
        ]);
    }
}