<?php

use App\Services\Image\ImageOptimizerService;
use Illuminate\Http\UploadedFile;

test('variantPath menyisipkan suffix sebelum ekstensi', function () {
    expect(ImageOptimizerService::variantPath('products/img-abc.jpg', 'thumb'))->toBe('products/img-abc-thumb.jpg')
        ->and(ImageOptimizerService::variantPath('/a/b/var-x.webp', 'compressed'))->toBe('/a/b/var-x-compressed.webp');
});

test('displayPath fallback ke file asli bila versi optimasi belum ada, dan pakai versi bila ada', function () {
    $relative = 'products/test-opt-'.uniqid().'.jpg';
    $dir = storage_path('app/private/products');
    @mkdir($dir, 0755, true);
    $thumb = storage_path('app/private/'.ImageOptimizerService::variantPath($relative, 'thumb'));

    expect(ImageOptimizerService::displayPath($relative, 'thumb'))->toBe($relative)
        ->and(ImageOptimizerService::displayPath(null, 'thumb'))->toBeNull();

    file_put_contents($thumb, 'x');
    expect(ImageOptimizerService::displayPath($relative, 'thumb'))->toBe(ImageOptimizerService::variantPath($relative, 'thumb'));

    $copy = 'products/test-opt-copy-'.uniqid().'.jpg';
    $service = new ImageOptimizerService;
    $service->copyVariants($relative, $copy);
    $copied = storage_path('app/private/'.ImageOptimizerService::variantPath($copy, 'thumb'));
    expect(file_exists($copied))->toBeTrue();

    $service->deleteVariants($relative);
    $service->deleteVariants($copy);
    expect(file_exists($thumb))->toBeFalse()->and(file_exists($copied))->toBeFalse();
});

test('generate membuat compressed maks 1200px dan thumb 300x300 tanpa mengubah file sumber', function () {
    $file = UploadedFile::fake()->image('big.jpg', 2400, 1600);
    $source = storage_path('app/private/products/opt-'.uniqid().'.jpg');
    @mkdir(dirname($source), 0755, true);
    copy($file->getRealPath(), $source);
    $before = md5_file($source);

    $result = (new ImageOptimizerService)->generate($source);

    expect(md5_file($source))->toBe($before)
        ->and(getimagesize($result['compressed'])[0])->toBe(1200)
        ->and(array_slice(getimagesize($result['thumb']), 0, 2))->toBe([300, 300]);

    @unlink($source);
    @unlink($result['compressed']);
    @unlink($result['thumb']);
})->skip(! extension_loaded('gd'), 'Ekstensi GD tidak tersedia');
