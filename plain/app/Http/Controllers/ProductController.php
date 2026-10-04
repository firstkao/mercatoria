<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\Setting;
use App\Support\PriceCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Halaman produk. Return type union View|RedirectResponse: untuk spammer
     * yang kuotanya habis kita REDIRECT ke /akun (permintaan user #3: akun
     * terkunci hanya boleh berada di area /akun), bukan render view — dulu
     * method ini di-type-hint :View sehingga mengembalikan RedirectResponse
     * memicu fatal error (500) di server produksi.
     */
    public function show(Request $request, Product $product): View|RedirectResponse|Response
    {
        // PAKSA LOGIN: guest tidak boleh akses halaman produk sama sekali
        // (aturan bisnis user). Pilih cara if-check di controller (bukan
        // middleware 'auth' di route) supaya tidak perlu ubah routes dan
        // tidak double-redirect dengan GuestBarrierMiddleware.
        if (!$request->user()) {
            return redirect()->route('login')->with('error', 'Silakan masuk untuk melihat produk.');
        }

        abort_unless($product->is_published, 404);

        $user = $request->user();

        // Guard guest: halaman produk kini GLOBAL (guest boleh lihat), jadi
        // cek spammer/kuota hanya kalau ada user login. Dulu tanpa guard ini,
        // tamu yang buka /produk/... langsung kena 500 "isSpammer() on null".
        //
        // STRICT MODE (permintaan user): spammer kuota habis TIDAK BOLEH
        // mengakses detail produk sama sekali (hanya Home & Akun). Redirect
        // POLOS tanpa flash/modal; status + tombol WA ada di dashboard /akun.
        try {
            if ($user !== null && $user->isSpammer() && $user->hasExhaustedQuota()) {
                return redirect()->route('account.show');
            }

            if ($user !== null && $user->isSpammer() && ! $user->consumeViewQuota($product)) {
                return redirect()->route('account.show');
            }
        } catch (\Throwable $e) {
            report($e);
        }

        if ($user !== null) {
            // Sama seperti di atas: pencatatan aktivitas tidak boleh bisa
            // menjatuhkan halaman produk (tabel activity_logs belum ada di DB
            // dump lama -> dulu ini ikut jadi sumber 500).
            try {
                ActivityLog::record($user, 'view_product', $request, [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                ]);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        // Load relasi satu per satu + try/catch: kalau ada relasi yang tidak
        // bisa di-load (mis. kolom/tabel belum ada di DB dump lama), kita tetap
        // lanjut render dengan relasi lain yang berhasil — BUKAN mati 500.
        foreach (['images', 'variants', 'shippingTier', 'game', 'developer'] as $relation) {
            try {
                $product->load($relation);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        // Calculator WAJIB selalu ter-instantiate; dariSettings() gagal (kolom
        // settings kosong/rusak di DB lama) → fallback default, bukan exception.
        try {
            $calculator = PriceCalculator::fromSettings();
        } catch (\Throwable $e) {
            report($e);
            $calculator = new PriceCalculator(null, null, null, 11.0, 5000);
        }

        // SEO data
        $description = $product->description
            ? \Illuminate\Support\Str::limit(strip_tags($product->description), 155)
            : 'Beli ' . $product->name . ' original dari Tmall. Kirim ke seluruh Indonesia.';

        // Render view dibungkus try/catch sebagai LAST RESORT anti-500 polos:
        // kalau ada satu saja bug data/relasi yang lolos dari semua guard
        // (mis. kolom hilang, relasi null tak terduga), pengunjung tetap dapat
        // halaman "tidak tersedia" yang rapi + error aslinya tercatat di log,
        // BUKAN layar putih 500. Ini menjawab keluhan berulang "produk masih
        // 500" — sekarang penyebabnya selalu bisa dilacak lewat storage/logs.
        // Build daftar varian satu per satu dengan try/catch: kalau SATU varian
        // punya data rusak (mis. price_yuan string aneh dari SQL dump lama),
        // varian itu dilewati — BUKAN seluruh halaman produk mati 500.
        $variantRows = [];
        foreach ($product->variants as $variant) {
            try {
                $variantRows[] = [
                    'id' => $variant->id,
                    'name' => (string) ($variant->name ?? 'Varian'),
                    'available' => $variant->isAvailable(),
                    'price' => $variant->sellingPrice($calculator),
                    'comparePrice' => $variant->comparePrice($calculator),
                    'imageUrl' => $variant->imageUrl(),
                ];
            } catch (\Throwable $e) {
                report($e);
            }
        }

        // Ambil produk terkait (random dari game/developer yang sama, max 6)
        $relatedProducts = Product::where('id', '!=', $product->id)
            ->where(function ($query) use ($product) {
                if ($product->game_id) {
                    $query->orWhere('game_id', $product->game_id);
                }
                if ($product->developer_id) {
                    $query->orWhere('developer_id', $product->developer_id);
                }
            })
            ->where('is_published', true)
            ->inRandomOrder()
            ->limit(6)
            ->get();

        // Load relasi yang dibutuhkan untuk tampilan
        $relatedProducts->load(['images', 'variants', 'game', 'developer']);

        try {
            // render() DI DALAM try supaya error Blade ikut tertangkap -> halaman "tidak tersedia".
            return response(view('products.show', [
                'user' => $user,
                'product' => $product,
                'calculator' => $calculator,
                'relatedProducts' => $relatedProducts,
                'variants' => collect($variantRows),
                'viewQuota' => Setting::integer('view_quota', 10),
                // SEO
                'title' => $product->name,
                'metaDescription' => $description,
                'metaImage' => $product->images->first()?->url(),
                'ogType' => 'product',
            ])->render());
        } catch (\Throwable $e) {
            report($e);

            return response()->view('errors.product-unavailable', [
                'title' => 'Produk sementara tidak tersedia',
                'productName' => $product->name ?? 'produk ini',
            ], 200);
        }
    }
}
