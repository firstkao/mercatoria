<?php

namespace Database\Seeders;

use App\Services\IndonesiaRegionImporter;
use Illuminate\Database\Seeder;

class IndonesiaRegionsSeeder extends Seeder
{
    /**
     * Data wilayah lengkap (Kemendagri) dari database/seeders/data.
     * Logika yang sama dipakai perintah `php artisan indonesia:import-regions`.
     */
    public function run(): void
    {
        app(IndonesiaRegionImporter::class)->import();
    }
}
