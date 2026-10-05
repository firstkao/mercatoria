<?php

namespace App\Http\Controllers;

use App\Models\Developer;
use App\Models\Game;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\PriceCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q'));
        $calculator = PriceCalculator::fromSettings();

        // BUG FIX: $sort sebelumnya hanya di-assign di dalam blok `if ($q !== '')`
        // tetapi dipakai di view() di luar blok → akses /cari tanpa ?q
        // memicu "Undefined variable $sort" (ErrorException → 500).
        $sort = (string) $request->query('sort', '');

        $products = collect();
        $pages = collect();
        $games = collect();
        $developers = collect();

        if ($q !== '') {
            // BUG FIX fungsi: escape wildcard LIKE (% dan _ di query user dulu) —
            // sebelumnya kata "100%" bisa menghasilkan hasil ngawur.
            $like = '%'.addcslashes($q, '%_\\').'%';

            // === MERCATORIA SEARCH ENGINE v16.9 (adaptasi Laravel) ===
            // 1) Variation search: nama produk/SKU/deskripsi + nama/SKU/(deskripsi
            //    bila kolomnya ada) varian. whereHas() = subquery EXISTS → tidak
            //    pernah menghasilkan baris dobel, jadi aman tanpa distinct().
            // 2) Filter stok: sembunyikan produk yang seluruh variannya habis
            //    (inversi isOutOfStock(): wajib ADA minimal 1 varian available).
            // 3) Safe sorting (lihat switch): popularity pakai COALESCE agar
            //    produk dengan 0 penjualan TIDAK HILANG dari hasil.
            // 4) Pagination 30/halaman.
            $sort = (string) $request->query('sort', '');

            // Kolom `description` di product_variants tidak ada di skema standar
            // (hanya name/sku/price/status); cek runtime supaya DB lama/dump
            // hosting tidak melempar "Unknown column" (penyebab klasik 500).
            $variantDescriptionColumn = Schema::hasColumn('product_variants', 'description');

            // Guard anti-500: pastikan kolom pendukung sorting tersedia di DB
            // saat ini (SQL dump lama bisa tidak punya sku varian). Kalau tidak
            // ada, opsi terkait difallback ke nama A-Z.
            $hasSkuVariant = Schema::hasColumn('product_variants', 'sku');
            // Sort 'popular' memakai subquery atas tabel order_items + orders,
            // BUKAN kolom best_seller_score. Guard harus mengecek tabel itu.
            $hasPopularityTables = Schema::hasTable('order_items') && Schema::hasTable('orders');

            $base = Product::query()
                ->published()
                ->where(function ($inner) use ($like, $variantDescriptionColumn, $hasSkuVariant) {
                    $inner->where('name', 'like', $like)
                        ->orWhere('sku', 'like', $like)
                        ->orWhere('description', 'like', $like)
                        ->orWhereHas('game', fn ($g) => $g->where('name', 'like', $like))
                        ->orWhereHas('developer', fn ($d) => $d->where('name', 'like', $like))
                        // Variation search: nama (+ SKU & deskripsi bila tersedia).
                        ->orWhereHas('variants', function ($v) use ($like, $variantDescriptionColumn, $hasSkuVariant) {
                            $v->where('name', 'like', $like);
                            if ($hasSkuVariant) {
                                $v->orWhere('sku', 'like', $like);
                            }
                            if ($variantDescriptionColumn) {
                                $v->orWhere('description', 'like', $like);
                            }
                        });
                })
                // Stok: hanya produk dengan minimal satu varian tersedia.
                ->whereHas('variants', fn ($v) => $v->where('status', ProductVariant::STATUS_AVAILABLE))
                ->with(['images', 'variants', 'shippingTier']);

            switch ($sort) {
                case 'popular':
                case 'popularity':
                    // SAFE SORTING: total kuantitas terjual (order selesai) via
                    // correlated subquery — ekuivalen leftJoin+COALESCE tapi tanpa
                    // risiko duplikasi baris saat paginate(). Produk 0 sales tetap
                    // muncul (skor 0), hanya berada di akhir urutan.
                    $products = $base
                        ->select('products.*')
                        ->selectRaw(
                            '(SELECT COALESCE(SUM(oi.quantity), 0)
                                FROM order_items oi
                                JOIN orders o ON o.id = oi.order_id
                               WHERE o.status = ?
                                 AND oi.product_variant_id IN (SELECT pv.id FROM product_variants pv WHERE pv.product_id = products.id)) AS popularity_score',
                            ['selesai']
                        )
                        ->orderByDesc('popularity_score')
                        ->orderBy('products.name')
                        ->paginate(30)
                        ->withQueryString();
                    break;

                case 'price':
                case 'price_asc':
                case 'price-desc':
                    // Harga termurah dari varian yang tersedia. sellingPrice()
                    // monotonik terhadap price_yuan (kurs & margin konstan untuk
                    // semua item), jadi MIN(price_yuan) = urutan harga rupiah.
                    $dir = in_array($sort, ['price-desc', 'price_desc'], true) ? 'desc' : 'asc';
                    $products = $base
                        ->select('products.*')
                        ->selectRaw(
                            '(SELECT MIN(pv.price_yuan) FROM product_variants pv
                              WHERE pv.product_id = products.id AND pv.status = ?) AS sort_price',
                            [ProductVariant::STATUS_AVAILABLE]
                        )
                        ->orderBy('sort_price', $dir)
                        ->orderBy('products.name')
                        ->paginate(30)
                        ->withQueryString();
                    break;

                case 'newest':
                    $products = $base->orderByDesc('products.created_at')
                        ->orderByDesc('products.id')
                        ->paginate(30)->withQueryString();
                    break;

                default:
                    // Standar: nama produk A→Z.
                    $products = $base->orderBy('products.name')
                        ->orderBy('products.id')
                        ->paginate(30)->withQueryString();
            }

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

            // Guard anti-500 untuk DB lama: kalau tabel pendukung sorting tidak
            // ada, sort key yang dipilih user difallback ke default (nama A-Z).
            if ($sort === 'popular' && ! $hasPopularityTables) {
                $sort = 'name';
            }
        }

        return view('search.index', [
            'q' => $q,
            'products' => $products,
            'pages' => $pages,
            'games' => $games,
            'developers' => $developers,
            'calculator' => $calculator,
            'user' => $request->user(),
            'sort' => $sort,
            'title' => $q !== '' ? "Cari: {$q}" : 'Cari Produk',
            'metaDescription' => $q !== '' ? "Hasil pencarian untuk: {$q}" : 'Cari merchandise game original di MERCATORIA.',
        ]);
    }
}