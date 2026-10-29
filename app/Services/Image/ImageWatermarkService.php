<?php

namespace App\Services\Image;

use Intervention\Image\Drivers\Gd\Driver;
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
        if (! is_dir($originalDir)) {
            mkdir($originalDir, 0755, true);
        }
        copy($sourcePath, storage_path('app/private/originals/'.$target));

        $manager = ImageManager::usingDriver(new Driver);

        if (! (bool) setting('image_watermark_enabled', true)) {
            $destDir = storage_path('app/private/'.dirname($target));
            if (! is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }
            copy($sourcePath, storage_path('app/private/'.$target));

            return $target;
        }

        $image = $manager->decodePath($sourcePath);

        $image->text(
            (string) config('mercatoria.image_protection.watermark_text', 'MERCATORIA · mercatoria.id'),
            (int) ($image->width() / 2),
            (int) ($image->height() / 2),
            function ($font) {
                $font->size(24);
                $font->color('rgba(255, 255, 255, 0.25)');
                $font->align('center', 'center');
                $font->angle(-30);
            }
        );

        $destDir = storage_path('app/private/'.dirname($target));
        if (! is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $image->save(storage_path('app/private/'.$target));

        return $target;
    }
}
