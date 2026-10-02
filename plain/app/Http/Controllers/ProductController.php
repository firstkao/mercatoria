<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Support\PriceCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
    public function show(Request $request, Product $product): View|RedirectResponse
    {
        abort_unless($product->is_published, 404);

        $user = $request->user();

        // Guard guest: halaman produk kini GLOBAL (guest boleh lihat), jadi
        // cek spammer/kuota hanya kalau ada user login. Dulu tanpa guard ini,
        // tamu yang buka /produk/... langsung kena 500 "isSpammer() on null".
        //
        // PERBAIKAN RONDE INI: cek kuota dibungkus try/catch. Kalau terjadi
        // error tak terduga (mis. kolom view_quota_used belum ada karena DB
        // dibuat dari SQL dump lama sebelum migration quota jalan), kita JANGAN
        // biarkan seluruh halaman produk ikut mati (500). Fallback: anggap
        // kuota masih tersedia dan log penyebab aslinya.
        try {
            if ($user !== null && $user->isSpammer() && ! $user->consumeViewQuota($product)) {
                // Spammer 10/10: detail produk terkunci -> lempar ke /akun
                // dengan pesan + arahan chat admin via WA (permintaan #3).
                return redirect()->route('account.show')
                    ->with('error', 'Kuota lihat produk kamu sudah habis ('.$user->view_quota_used.' dari '.Setting::integer('view_quota', 10).'). Chat admin via WhatsApp untuk melakukan reset kuota.');
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

        $product->load(['images', 'variants', 'shippingTier', 'game', 'developer']);
        $calculator = PriceCalculator::fromSettings();

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
        try {
            return view('products.show', [
                'user' => $user,
                'product' => $product,
                'variants' => $product->variants->map(fn (ProductVariant $variant): array => [
                    'id' => $variant->id,
                    'name' => $variant->name,
                    'available' => $variant->isAvailable(),
                    'price' => $variant->sellingPrice($calculator),
                    'comparePrice' => $variant->comparePrice($calculator),
                    'imageUrl' => $variant->imageUrl(),
                ]),
                'viewQuota' => Setting::integer('view_quota', 10),
                // SEO
                'title' => $product->name,
                'metaDescription' => $description,
                'metaImage' => $product->images->first()?->url(),
                'ogType' => 'product',
            );
        } catch (\Throwable $e) {
            report($e);

            return response()->view('errors.product-unavailable', [
                'title' => 'Produk sementara tidak tersedia',
                'productName' => $product->name ?? 'produk ini',
            ], 200);
        }
    }
}