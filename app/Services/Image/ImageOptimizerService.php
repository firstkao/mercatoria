<?php

namespace App\Services\Image;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;

class ImageOptimizerService
{
    public const COMPRESSED = 'compressed';

    public const THUMB = 'thumb';

    public const MAX_WIDTH = 1200;

    public const QUALITY = 80;

    public const THUMB_SIZE = 300;

    /**
     * Buat versi -compressed (maks 1200px, kualitas 80) dan -thumb (300x300 cover)
     * di folder yang sama dengan file sumber. File sumber tidak dihapus/diubah.
     *
     * @return array{compressed: string, thumb: string} path absolut hasil
     */
    public function generate(string $absoluteSource): array
    {
        $extension = strtolower(pathinfo($absoluteSource, PATHINFO_EXTENSION)) ?: 'jpg';
        $manager = ImageManager::gd();

        $compressedPath = self::variantPath($absoluteSource, self::COMPRESSED);
        $this->writeCompressed(
            $manager->read($absoluteSource)->scaleDown(width: self::MAX_WIDTH),
            $extension,
            $compressedPath,
            $absoluteSource
        );

        $thumbPath = self::variantPath($absoluteSource, self::THUMB);
        $this->encode(
            $manager->read($absoluteSource)->cover(self::THUMB_SIZE, self::THUMB_SIZE),
            $extension,
            $thumbPath,
            self::QUALITY
        );

        return ['compressed' => $compressedPath, 'thumb' => $thumbPath];
    }

    /**
     * products/img-abc.jpg + 'thumb' → products/img-abc-thumb.jpg (berlaku untuk path relatif maupun absolut).
     */
    public static function variantPath(string $path, string $suffix): string
    {
        $info = pathinfo($path);
        $dir = ($info['dirname'] ?? '.') === '.' ? '' : $info['dirname'].'/';
        $extension = isset($info['extension']) ? '.'.$info['extension'] : '';

        return $dir.$info['filename'].'-'.$suffix.$extension;
    }

    /**
     * Path relatif versi yang tersedia di disk privat. Fallback berurutan:
     * versi yang diminta → file asli → versi lain yang tersedia (file asli hilang,
     * mis. hanya -compressed yang tersisa) → path asli.
     */
    public static function displayPath(?string $relativePath, string $suffix): ?string
    {
        if (! $relativePath) {
            return null;
        }

        $candidates = array_unique([
            self::variantPath($relativePath, $suffix),
            $relativePath,
            self::variantPath($relativePath, self::COMPRESSED),
            self::variantPath($relativePath, self::THUMB),
        ]);

        foreach ($candidates as $candidate) {
            if (is_file(storage_path('app/private/'.$candidate))) {
                return $candidate;
            }
        }

        return $relativePath;
    }

    public static function url(?string $relativePath, string $suffix): ?string
    {
        $path = self::displayPath($relativePath, $suffix);

        return $path ? Storage::url($path) : null;
    }

    /**
     * Encode + tulis varian. PNG dikodeksi lewat GD langsung dengan compression level
     * dari quality — encodeByExtension tidak bisa: Format::encoder membuang opsi
     * `quality` untuk PngEncoder (parameternya hanya interlaced/indexed).
     */
    private function encode(ImageInterface $image, string $extension, string $dest, int $quality): void
    {
        if ($extension === 'png') {
            imagepng($image->core()->native(), $dest, self::pngLevel($quality));

            return;
        }

        $image->encodeByExtension($extension, quality: $quality)->save($dest);
    }

    private static function pngLevel(int $quality): int
    {
        return max(0, min(9, (int) round($quality / 10)));
    }

    /**
     * Varian -compressed wajib lebih kecil dari file sumber. Kalau encode quality 80
     * gagal mengecilkan (PNG hasil encode GD, atau JPEG yang di-encode ulang dari
     * kualitas lebih rendah), pakai langkah fallback yang lebih agresif:
     * PNG → palet 255 warna + level 9; selain PNG → quality 60.
     */
    private function writeCompressed(ImageInterface $image, string $extension, string $dest, string $source): void
    {
        $this->encode($image, $extension, $dest, self::QUALITY);

        if (is_file($dest) && filesize($dest) < filesize($source)) {
            return;
        }

        if ($extension === 'png') {
            $image->reduceColors(255);
            $this->encode($image, $extension, $dest, 100);
        } else {
            $this->encode($image, $extension, $dest, 60);
        }
    }

    public function deleteVariants(string $relativePath): void
    {
        foreach ([self::COMPRESSED, self::THUMB] as $suffix) {
            @unlink(storage_path('app/private/'.self::variantPath($relativePath, $suffix)));
        }
    }

    public function copyVariants(string $fromRelative, string $toRelative): void
    {
        foreach ([self::COMPRESSED, self::THUMB] as $suffix) {
            $source = storage_path('app/private/'.self::variantPath($fromRelative, $suffix));

            if (is_file($source)) {
                @copy($source, storage_path('app/private/'.self::variantPath($toRelative, $suffix)));
            }
        }
    }
}
