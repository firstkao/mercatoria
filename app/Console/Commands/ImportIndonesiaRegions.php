<?php

namespace App\Console\Commands;

use App\Services\IndonesiaRegionImporter;
use Illuminate\Console\Command;

class ImportIndonesiaRegions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'indonesia:import-regions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import lengkap data wilayah Indonesia (provinsi/kota/kecamatan/kode pos) dari CSV di database/seeders/data';

    public function handle(IndonesiaRegionImporter $importer): int
    {
        $this->info('Mengimpor data wilayah Indonesia (Kemendagri)...');

        $counts = $importer->import();

        $this->table(
            ['Tipe', 'Jumlah'],
            collect($counts)->map(fn (int $count, string $type) => [$type, number_format($count)])->all()
        );

        return self::SUCCESS;
    }
}
