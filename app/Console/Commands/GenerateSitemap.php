<?php

namespace App\Console\Commands;

use App\Services\Seo\SeoMetaService;
use Illuminate\Console\Command;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';

    protected $description = 'Update public/sitemap.xml dari route dinamis (PRD 37.3)';

    public function handle(SeoMetaService $seo): int
    {
        $urls = collect($seo->sitemapUrls())
            ->map(fn (string $path) => '<url><loc>'.e(url($path)).'</loc></url>')
            ->implode('');

        $xml = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.$urls.'</urlset>';

        file_put_contents(public_path('sitemap.xml'), $xml);

        $this->info('public/sitemap.xml diperbarui ('.count($seo->sitemapUrls()).' URL).');

        return self::SUCCESS;
    }
}
