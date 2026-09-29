<?php

namespace App\Console\Commands;

use App\Models\AdminLog;
use App\Models\HeroSlide;
use App\Models\PaymentProof;
use App\Models\PreorderBanner;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class CleanupStorage extends Command
{
    protected $signature = 'storage:cleanup {--dry-run : Tampilkan saja tanpa hapus}';
    protected $description = 'Hapus file orphan + log lama dari storage';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $deleted = 0;
        $bytes = 0;

        // ============================================================
        // 1. Foto produk (gambar utama + varian)
        // ============================================================
        $usedProductPaths = collect()
            ->merge($this->safePluck(ProductImage::class, 'image_path'))
            ->merge($this->safePluck(ProductVariant::class, 'image_path', fn ($q) => $q->whereNotNull('image_path')))
            ->filter()
            ->map(fn ($p) => 'public/' . $p)
            ->all();

        $deleted += $this->cleanOrphans('public/products', $usedProductPaths, $dryRun, $bytes);

        // ============================================================
        // 2. Hero slides
        // ============================================================
        $usedHeroPaths = $this->safePluck(HeroSlide::class, 'image_path', fn ($q) => $q->whereNotNull('image_path'))
            ->filter()
            ->map(fn ($p) => 'public/' . $p)
            ->all();

        $deleted += $this->cleanOrphans('public/hero-slides', $usedHeroPaths, $dryRun, $bytes);

        // ============================================================
        // 3. Pre-order banners
        // ============================================================
        $usedPreorderPaths = $this->safePluck(PreorderBanner::class, 'image_path', fn ($q) => $q->whereNotNull('image_path'))
            ->filter()
            ->map(fn ($p) => 'public/' . $p)
            ->all();

        $deleted += $this->cleanOrphans('public/preorder', $usedPreorderPaths, $dryRun, $bytes);

        // ============================================================
        // 4. Bukti bayar (yang recordnya sudah dihapus)
        // ============================================================
        $usedProofPaths = $this->safePluck(PaymentProof::class, 'proof_path')
            ->filter()
            ->map(fn ($p) => 'public/' . $p)
            ->all();

        $deleted += $this->cleanOrphans('public/payment-proofs', $usedProofPaths, $dryRun, $bytes);

        // ============================================================
        // 5. Log Laravel (> 30 hari)
        // ============================================================
        $logDir = storage_path('logs');
        if (File::isDirectory($logDir)) {
            foreach (File::files($logDir) as $file) {
                if ($file->getMTime() < now()->subDays(30)->timestamp) {
                    $bytes += $file->getSize();
                    if (! $dryRun) {
                        File::delete($file->getPathname());
                    }
                    $this->line("  " . ($dryRun ? '[DRY] ' : '') . "Hapus log: " . $file->getFilename());
                    $deleted++;
                }
            }
        }

        // ============================================================
        // 6. View cache lama (> 7 hari)
        // ============================================================
        $viewCacheDir = storage_path('framework/views');
        if (File::isDirectory($viewCacheDir)) {
            foreach (File::files($viewCacheDir) as $file) {
                if ($file->getMTime() < now()->subDays(7)->timestamp) {
                    $bytes += $file->getSize();
                    if (! $dryRun) {
                        File::delete($file->getPathname());
                    }
                    $deleted++;
                }
            }
        }

        $this->info(($dryRun ? '[DRY RUN] ' : '') . "Selesai. {$deleted} file dihapus, " . $this->humanSize($bytes) . " dibebaskan.");

        if (! $dryRun) {
            try {
                AdminLog::record('cleanup_storage', null, [
                    'files' => $deleted,
                    'bytes' => $bytes,
                ]);
            } catch (\Throwable $e) {
                // ignore — admin belum login
            }
        }

        return self::SUCCESS;
    }

    /**
     * Hapus file orphan di folder tertentu.
     *
     * @param  array<int, string>  $usedPaths  Daftar path (dengan prefix "public/") yang sedang dipakai
     */
    private function cleanOrphans(string $diskPath, array $usedPaths, bool $dryRun, int &$bytes): int
    {
        $fullPath = storage_path('app/' . $diskPath);
        if (! File::isDirectory($fullPath)) {
            return 0;
        }

        $deleted = 0;
        foreach (File::allFiles($fullPath) as $file) {
            $relative = $diskPath . '/' . $file->getRelativePathname();
            if (in_array($relative, $usedPaths, true)) {
                continue;
            }

            $bytes += $file->getSize();
            if (! $dryRun) {
                File::delete($file->getPathname());
            }
            $this->line("  " . ($dryRun ? '[DRY] ' : '') . "Orphan: " . $file->getRelativePathname());
            $deleted++;
        }

        return $deleted;
    }

    /**
     * Pluck aman: kalau model / tabel belum ada, return collection kosong.
     *
     * @param  class-string  $modelClass
     * @return \Illuminate\Support\Collection<int, mixed>
     */
    private function safePluck(string $modelClass, string $column, ?\Closure $modify = null): \Illuminate\Support\Collection
    {
        try {
            if (! class_exists($modelClass)) {
                return collect();
            }

            $query = $modelClass::query();
            if ($modify) {
                $modify($query);
            }

            return $query->pluck($column);
        } catch (\Throwable $e) {
            return collect();
        }
    }

    private function humanSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}