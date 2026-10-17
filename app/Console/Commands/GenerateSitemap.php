<?php

namespace App\Console\Commands;

use App\Services\Content\SitemapBuilder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use RuntimeException;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';

    protected $description = 'Generate the public sitemap.xml file';

    public function handle(SitemapBuilder $builder): int
    {
        $path = public_path('sitemap.xml');
        $bytes = File::put($path, $builder->render());
        if ($bytes === false) {
            throw new RuntimeException("Unable to write sitemap to {$path}.");
        }

        $this->info("Generated sitemap.xml ({$bytes} bytes).");

        return self::SUCCESS;
    }
}
