<?php

namespace App\Console\Commands;

use App\Services\Product\MerchCategoryService;
use Illuminate\Console\Command;

class AutoGenerateMerchCategories extends Command
{
    protected $signature = 'merch:auto-categories';

    protected $description = 'Buat kategori merch otomatis dari produk aktif yang belum punya kategori cocok (PRD 14)';

    public function handle(MerchCategoryService $service): int
    {
        $created = $service->autoGenerateFromProducts();

        $this->info("{$created} kategori baru dibuat.");

        return self::SUCCESS;
    }
}
