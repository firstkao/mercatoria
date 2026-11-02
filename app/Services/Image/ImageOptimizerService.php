<?php

namespace App\Services\Image;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

class ImageOptimizerService
{
    public const COMPRESSED = 'compressed';

    public const THUMB = 'thumb';

    public const MAX_WIDTH = 1200;

    public const QUALITY = 80;

    public const THUMB_SIZE = 300;

    /**
     * Buat versi -compressed (maks 1200px, kualitas 80) dan -thumb (300x300 cover)
     * di folder yang sama dengan file sumber. File sumber tidak diubah.
     *
     * @return array{compressed: string, thumb: string} path absolut hasil
     */
    public function generate(string $absoluteSource): array
    {
        $extension = strtolower(pathinfo($absoluteSource, PATHINFO_EXTENSION)) ?: 'jpg';
        $manager = ImageManager::gd();

        $compressedPath = self::variantPath($absoluteSource, self::COMPRESSED);
        $manager->read($absoluteSource)
            ->scaleDown(width: self::MAX_WIDTH)
            ->encodeByExtension($extension, quality: self::QUALITY)
            ->save($compressedPath);

        $thumbPath = self::variantPath($absoluteSource, self::THUMB);
        $manager->read($absoluteSource)
            ->cover(self::THUMB_SIZE, self::THUMB_SIZE)
            ->encodeByExtension($extension, quality: self::QUALITY)
            ->save($thumbPath);

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
     * Path relatif versi yang tersedia di disk privat; fallback ke file asli untuk gambar lama
     * yang belum punya versi hasil optimasi.
     */
    public static function displayPath(?string $relativePath, string $suffix): ?string
    {
        if (! $relativePath) {
            return null;
        }

        $variant = self::variantPath($relativePath, $suffix);

        return is_file(storage_path('app/private/'.$variant)) ? $variant : $relativePath;
    }

    public static function url(?string $relativePath, string $suffix): ?string
    {
        $path = self::displayPath($relativePath, $suffix);

        return $path ? Storage::url($path) : null;
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
