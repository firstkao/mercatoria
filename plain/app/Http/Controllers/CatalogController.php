<?php

namespace App\Http\Controllers;

use App\Models\Developer;
use App\Models\Game;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Support\PriceCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        // STRICT MODE (permintaan user): spammer yang kuota lihat produknya
        // habis TIDAK BOLEH mengakses katalog sama sekali — hanya Home & Akun.
        if ($u = auth()->user()) {
            if ($u->isSpammer() && $u->hasExhaustedQuota()) {
                return redirect()->route('account.show')
                    ->with('error', 'Kuota Anda habis. Silakan hubungi admin untuk reset.');
            }
        }

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

        // Batas atas slider harga: dari harga jual produk termahal (dibulatkan ke
        // Rp50.000 terdekat, minimum Rp500.000) supaya range slider realistis.
        $rate = (float) (Setting::get('exchange_rate') ?: 0);
        $kgRate = (float) (Setting::get('cn_id_rate_per_kg') ?: 0) + (float) (Setting::get('btm_jkt_rate_per_kg') ?: 0);
        $margin = (float) (Setting::get('margin_percent') ?: 11.0);
        $priceExpr = '(('.$rate.' * pv.price_yuan + '.$kgRate.' * pv.weight_grams / 1000) * (1 + '.$margin.' / 100))';

        $maxVariantPrice = (float) ProductVariant::query()
            ->from('product_variants as pv')
            ->join('products', 'products.id', '=', 'pv.product_id')
            ->whereNotNull('pv.price_yuan')
            ->selectRaw('MAX('.$priceExpr.') AS aggregate')
            ->value('aggregate');
        $sliderMax = max(500000, (int) (ceil($maxVariantPrice / 50000) * 50000));

        // Filter harga (rupiah), dipotong agar tidak melewati batas slider.
        $minPrice = is_numeric($request->query('min_price')) ? min(max(0, (int) $request->query('min_price')), $sliderMax) : null;
        $maxPrice = is_numeric($request->query('max_price')) ? min(max(0, (int) $request->query('max_price')), $sliderMax) : null;
        if ($minPrice !== null && $maxPrice !== null && $minPrice > $maxPrice) {
            [$minPrice, $maxPrice] = [$maxPrice, $minPrice];
        }

        $sort = (string) $request->query('sort', '');
        $calculator = PriceCalculator::fromSettings();

        $products = Product::query()
            ->published()
            // MERCATORIA SEARCH ENGINE v16.9 — Filter stok: sembunyikan produk
            // yang benar-benar habis (tidak ada 1 varian AVAILABLE pun).
            // Pakai whereExists (bukan join) agar tidak ada baris dobel, jadi
            // distinct() tidak diperlukan dan pagination tetap akurat.
            ->whereExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('product_variants as pv')
                    ->whereColumn('pv.product_id', 'products.id')
                    ->where('pv.status', ProductVariant::STATUS_AVAILABLE);
            })
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
            }, fn ($query) => match ($sort) {
                // "Terpopuler" v16.9 (AMAN): total kuantitas terjual dari
                // order_items via correlated subquery + COALESCE — produk
                // dengan 0 penjualan TETAP muncul (bukan inner join/whereHas
                // yang akan membuang mereka). Tetap disort berdasar kolom
                // products.id dulu agar deterministik.
                'popular', 'popularity' => $query
                    ->orderByDesc(DB::raw('(SELECT COALESCE(SUM(oi.quantity), 0) FROM order_items oi JOIN product_variants pv2 ON pv2.id = oi.product_variant_id WHERE pv2.product_id = products.id)'))
                    ->orderBy('products.name'),
                // Fallback lama pakai skor best-seller manual dashboard admin.
                'best_seller' => $query->orderByDesc('best_seller_score')->orderByDesc('best_seller_rank')->latest(),
                // Standar baru sesuai permintaan user: urut nama A-Z.
                'name' => $query->orderBy('name'),
                // v16.9 default = Nama ASC (bukan terbaru lagi).
                default => $query->orderBy('products.name'),
            })
            ->paginate(24)
            ->withQueryString();

        // Permintaan user #3: spammer yang kuota lihatnya habis (mis. 10/10)
        // tidak boleh dikunci total di /akun saja — katalog tetap bisa dibuka,
        // hanya halaman produk detail yang terkunci sampai admin reset kuota.
        // Jadi kalau kuota habis, tampilkan banner "terkunci" alih-alih
        // menyuruh buka produk satu-satu yang pasti ditolak ProductController.
        $user = $request->user();
        $quotaExhausted = $user !== null && $user->isSpammer() && $user->remainingViewQuota() <= 0;

        return view('catalog.index', [
            'user' => $user,
            'quotaExhausted' => $quotaExhausted,
            'products' => $products,
            'games' => $games,
            'developers' => $developers,
            'selectedGame' => $selectedGame,
            'selectedDeveloper' => $selectedDeveloper,
            'selectedTag' => $selectedTag,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
            'sliderMax' => $sliderMax,
            'calculator' => $calculator,
            'viewQuota' => Setting::integer('view_quota', 10),
        ]);
    }
}