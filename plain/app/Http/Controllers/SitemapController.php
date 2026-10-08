<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Product;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $urls = [];

        // Halaman statis utama
        $urls[] = ['loc' => route('home'), 'priority' => '1.0', 'changefreq' => 'daily'];

        // Katalog (login-required untuk guest, tapi crawler resmi dibolehkan)
        $urls[] = ['loc' => route('catalog.index'), 'priority' => '0.9', 'changefreq' => 'daily'];

        // ============================================================
        // Produk — halaman paling penting untuk SEO
        // ============================================================
        // Bot crawler resmi (Googlebot, Bingbot, dsb.) dibolehkan membuka
        // halaman produk tanpa login — lihat ProductController::show()
        // dan SecurityHelper::isLegitCrawlerBot(). Guest biasa tetap
        // di-redirect ke /masuk. Jadi sekarang aman didaftarkan di sitemap.
        //
        // Kalau nanti kebijakan diubah (produk benar-benar publik), blok
        // ini tetap jalan tanpa perubahan.
        Product::query()
            ->where('is_published', true)
            ->orderByDesc('updated_at')
            ->select(['slug', 'updated_at'])
            ->chunk(500, function ($products) use (&$urls) {
                foreach ($products as $product) {
                    $urls[] = [
                        'loc'        => route('slug.show', $product->slug),
                        'lastmod'    => $product->updated_at?->toAtomString(),
                        'priority'   => '0.8',
                        'changefreq' => 'weekly',
                    ];
                }
            });

        // Halaman statis dari DB — FAQ, syarat, kebijakan privasi, dsb.
        Page::query()
            ->where('is_published', true)
            ->select('slug', 'updated_at')
            ->chunk(200, function ($pages) use (&$urls) {
                foreach ($pages as $page) {
                    $urls[] = [
                        'loc'        => route('slug.show', $page->slug),
                        'lastmod'    => $page->updated_at?->toAtomString(),
                        'priority'   => '0.5',
                        'changefreq' => 'monthly',
                    ];
                }
            });

        // Halaman reseller
        $urls[] = ['loc' => route('reseller.create'), 'priority' => '0.7', 'changefreq' => 'monthly'];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($url['loc'], ENT_XML1) . "</loc>\n";
            if (! empty($url['lastmod'])) {
                $xml .= "    <lastmod>{$url['lastmod']}</lastmod>\n";
            }
            $xml .= "    <changefreq>{$url['changefreq']}</changefreq>\n";
            $xml .= "    <priority>{$url['priority']}</priority>\n";
            $xml .= "  </url>\n";
        }
        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
