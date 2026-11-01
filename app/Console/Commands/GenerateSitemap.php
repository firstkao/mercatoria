<?php

namespace App\Console\Commands;

use App\Services\Seo\SeoMetaService;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';

    protected $description = 'Update public/sitemap.xml dari route dinamis (PRD 37.3)';

    public function handle(SeoMetaService $seo): int
    {
        $paths = $seo->sitemapUrls();
        $sitemap = Sitemap::create();

        foreach ($paths as $path) {
            $sitemap->add(Url::create(url($path)));
        }

        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('public/sitemap.xml diperbarui ('.count($paths).' URL).');

        return self::SUCCESS;
    }
}
