<?php

namespace App\Console\Commands;

use App\Models\Developer;
use App\Models\Game;
use App\Models\HeroSlide;
use App\Models\OrderItemSnapshot;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupOrphanStorage extends Command
{
    protected $signature = 'storage:cleanup-orphans
        {--dry-run : Tampilkan daftar file orphan tanpa memindah/menghapus}
        {--force : Hapus permanen tanpa backup (default: pindah ke orphan-backup/)}';

    protected $description = 'Pindahkan file di storage privat yang tidak direferensikan database ke orphan-backup/ (sisa upload gagal, file terhapus manual, dsb.)';

    public function handle(): int
    {
        $disk = Storage::disk('local');
        $refs = $this->referencedPaths();
        $orphans = [];

        foreach ($disk->allFiles() as $relative) {
            if (str_starts_with($relative, 'orphan-backup/') || str_starts_with(basename($relative), '.')) {
                continue;
            }

            if (! $this->isReferenced($relative, $refs)) {
                $orphans[] = $relative;
            }
        }

        sort($orphans);

        foreach ($orphans as $relative) {
            if ($this->option('dry-run')) {
                $this->line("[orphan] {$relative}");

                continue;
            }

            if ($this->option('force')) {
                $disk->delete($relative);
                $this->line("[hapus] {$relative}");

                continue;
            }

            $disk->move($relative, 'orphan-backup/'.$relative);
            $this->line("[backup] {$relative}");
        }

        $action = $this->option('dry-run') ? 'Orphan (dry-run)' : ($this->option('force') ? 'Orphan dihapus' : 'Orphan dipindah ke orphan-backup/');
        $this->info("{$action}: ".count($orphans).' file.');

        return self::SUCCESS;
    }

    /**
     * Semua path relatif yang masih dipakai database (disk local = storage privat).
     */
    private function referencedPaths(): array
    {
        $refs = [];

        foreach ([
            [Product::class, ['image_path', 'description_image_path']],
            [ProductVariant::class, ['image_path']],
            [HeroSlide::class, ['image']],
            [Payment::class, ['file_path']],
            [Game::class, ['logo_path']],
            [Developer::class, ['logo_path']],
            [PaymentMethod::class, ['qr_image_path']],
            [OrderItemSnapshot::class, ['product_image_path', 'variant_image_path']],
        ] as [$model, $columns]) {
            foreach ($columns as $column) {
                foreach ($model::query()->whereNotNull($column)->pluck($column) as $path) {
                    $refs[$path] = true;
                }
            }
        }

        return $refs;
    }

    /**
     * File dianggap terreferensi bila path-nya (atau path induknya untuk versi
     * -compressed/-thumb dan master originals/) ada di database.
     */
    private function isReferenced(string $relative, array $refs): bool
    {
        if (str_starts_with($relative, 'originals/')) {
            $relative = substr($relative, strlen('originals/'));
        }

        $relative = (string) preg_replace('/-(?:compressed|thumb)(\.[^.]+)$/', '$1', $relative);

        return isset($refs[$relative]);
    }
}
