<?php

namespace App\Services\Image;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;
use RuntimeException;

class ImageWatermarkService
{
    /**
     * Simpan master asli di disk privat, lalu simpan versi publik dengan watermark ter-burn.
     * Mengembalikan path file publik (disk "public").
     */
    public function storeWithWatermark(UploadedFile $file, string $directory): string
    {
        $directory = trim($directory, '/');
        $extension = strtolower($file->extension() ?: 'jpg');
        $name = bin2hex(random_bytes(16)).'.'.$extension;

        Storage::disk(config('mercatoria.image_protection.private_originals_disk', 'local'))
            ->putFileAs('private/originals/'.$directory, $file, $name);

        $publicPath = $directory.'/'.$name;

        if (! $this->watermarkEnabled() || ! $this->canWatermark()) {
            Storage::disk('public')->putFileAs($directory, $file, $name);

            return $publicPath;
        }

        Storage::disk('public')->put($publicPath, $this->burn((string) file_get_contents($file->getRealPath()), $extension));

        return $publicPath;
    }

    public function watermarkEnabled(): bool
    {
        return filter_var(
            setting('image_watermark_enabled', config('mercatoria.image_protection.watermark_enabled', true)),
            FILTER_VALIDATE_BOOLEAN,
        );
    }

    public function canWatermark(): bool
    {
        return extension_loaded('gd') && $this->fontPath() !== null;
    }

    public function burn(string $binary, string $extension = 'jpg'): string
    {
        $fontPath = $this->fontPath();
        if (! extension_loaded('gd') || $fontPath === null) {
            throw new RuntimeException('GD extension atau font watermark tidak tersedia.');
        }

        $image = (new ImageManager(new GdDriver))->read($binary);
        $text = (string) config('mercatoria.image_protection.watermark_text', 'MERCATORIA · mercatoria.id');
        $opacity = max(5, min(80, (int) setting('image_watermark_opacity', config('mercatoria.image_protection.watermark_opacity', 25))));
        $alpha = round($opacity / 100, 2);
        $size = max(14, (int) round(min($image->width(), $image->height()) / 14));
        $stepX = $size * 14;
        $stepY = $size * 5;

        for ($y = 0, $row = 0; $y < $image->height() + $stepY; $y += $stepY, $row++) {
            for ($x = ($row % 2) * intdiv($stepX, 2) - $stepX; $x < $image->width() + $stepX; $x += $stepX) {
                $image->text($text, $x, $y, function ($font) use ($fontPath, $size, $alpha): void {
                    $font->filename($fontPath);
                    $font->size($size);
                    $font->color('rgba(255, 255, 255, '.$alpha.')');
                    $font->stroke('rgba(0, 0, 0, '.$alpha.')', 1);
                    $font->angle(30);
                });
            }
        }

        return match ($extension) {
            'png' => (string) $image->toPng(),
            'webp' => (string) $image->toWebp(),
            default => (string) $image->toJpeg(85),
        };
    }

    private function fontPath(): ?string
    {
        foreach ([
            config('mercatoria.image_protection.watermark_font'),
            resource_path('fonts/watermark.ttf'),
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
        ] as $path) {
            if (is_string($path) && $path !== '' && is_file($path)) {
                return $path;
            }
        }

        return null;
    }
}
