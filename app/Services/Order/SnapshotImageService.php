<?php

namespace App\Services\Order;

/**
 * PRD 46/13.6: snapshot gambar = COPY fisik ke order-snapshots/ (bukan path reference),
 * agar tetap utuh saat hard delete produk. Gagal copy = order dibatalkan (PRD 32).
 */
class SnapshotImageService
{
    public function copy(?string $imagePath, string $noOrder): ?string
    {
        if ($imagePath === null || $imagePath === '') {
            return null;
        }

        $source = storage_path('app/private/'.$imagePath);
        if (! is_file($source)) {
            throw new \RuntimeException('File gambar snapshot tidak ditemukan: '.$imagePath);
        }

        $target = 'order-snapshots/'.$noOrder.'-'.basename($imagePath);
        $targetDir = storage_path('app/public/order-snapshots');
        if (! is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        if (! copy($source, storage_path('app/public/'.$target))) {
            throw new \RuntimeException('Gagal menyalin gambar snapshot: '.$imagePath);
        }

        return $target;
    }
}
