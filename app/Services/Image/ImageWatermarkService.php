<?php

namespace App\Services\Image;

use Intervention\Image\ImageManager;

class ImageWatermarkService
{
    /**
     * PRD 8.10: master asli disimpan privat + versi publik diberi watermark MERCATORIA · mercatoria.id.
     */
    public function watermark(string $sourcePath, string $relativeTarget): string
    {
        $target = 'products/'.basename($relativeTarget);
        $target = str_starts_with($relativeTarget, 'products/') ? $relativeTarget : $target;

        // Simpan master asli ke folder privat (PRD 8.10).
        $originalDir = storage_path('app/private/originals/'.dirname($target));
        $this->ensureDir($originalDir);
        $this->writeCopy($sourcePath, storage_path('app/private/originals/'.$target));

        if (! (bool) setting('image_watermark_enabled', true)) {
            $destDir = storage_path('app/private/'.dirname($target));
            $this->ensureDir($destDir);
            $this->writeCopy($sourcePath, storage_path('app/private/'.$target));

            return $target;
        }

        if (! extension_loaded('gd')) {
            throw new \RuntimeException('GD not installed - ekstensi GD PHP tidak aktif, gambar tidak bisa diproses.');
        }

        $manager = ImageManager::gd();

        $image = $manager->read($sourcePath);
        $image->text(
            (string) config('mercatoria.image_protection.watermark_text', 'MERCATORIA · mercatoria.id'),
            (int) ($image->width() / 2),
            (int) ($image->height() / 2),
            function ($font) {
                $font->size(24);
                $font->color('rgba(255, 255, 255, 0.25)');
                $font->align('center');
                $font->valign('middle');
                $font->angle(-30);
            }
        );

        $destDir = storage_path('app/private/'.dirname($target));
        $this->ensureDir($destDir);

        $image->save(storage_path('app/private/'.$target));

        return $target;
    }

    private function ensureDir(string $dir): void
    {
        if (! is_dir($dir) && ! @mkdir($dir, 0755, true) && ! is_dir($dir)) {
            throw new \RuntimeException("permission denied - tidak bisa membuat folder {$dir}.");
        }
    }

    private function writeCopy(string $source, string $dest): void
    {
        if (! @copy($source, $dest)) {
            throw new \RuntimeException("permission denied - tidak bisa menulis file {$dest}.");
        }
    }
}
