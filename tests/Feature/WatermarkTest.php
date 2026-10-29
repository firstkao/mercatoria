<?php

use App\Models\Setting;
use App\Services\Image\ImageWatermarkService;

test('watermark menempel pada gambar produk', function () {
    // Buat gambar asli via GD
    $im = imagecreatetruecolor(200, 200);
    imagefill($im, 0, 0, imagecolorallocate($im, 255, 255, 255));
    $tmp = tempnam(sys_get_temp_dir(), 'wm').'.png';
    imagepng($im, $tmp);
    imagedestroy($im);

    $original = file_get_contents($tmp);

    $resultPath = app(ImageWatermarkService::class)->watermark($tmp, 'products/test.png');

    expect($resultPath)->not->toBe('')
        ->and(file_exists(storage_path('app/private/'.$resultPath)))->toBeTrue()
        ->and(file_get_contents(storage_path('app/private/'.$resultPath)))->not->toBe($original);
});

test('watermark tidak aktif bila setting mati', function () {
    Setting::create(['key' => 'image_watermark_enabled', 'value' => '0', 'type' => 'bool', 'group' => 'security', 'description' => 'test']);

    $im = imagecreatetruecolor(100, 100);
    $tmp = tempnam(sys_get_temp_dir(), 'wm').'.png';
    imagepng($im, $tmp);
    imagedestroy($im);

    $resultPath = app(ImageWatermarkService::class)->watermark($tmp, 'products/skip.png');

    // Gambar disalin apa adanya ke storage privat, tanpa perubahan piksel
    expect(file_exists(storage_path('app/private/'.$resultPath)))->toBeTrue()
        ->and(file_get_contents(storage_path('app/private/'.$resultPath)))->toBe(file_get_contents($tmp));
});

test('watermark menyimpan master asli ke folder privat', function () {
    $im = imagecreatetruecolor(100, 100);
    $tmp = tempnam(sys_get_temp_dir(), 'wm').'.png';
    imagepng($im, $tmp);
    imagedestroy($im);

    app(ImageWatermarkService::class)->watermark($tmp, 'products/orig.png');

    expect(file_exists(storage_path('app/private/originals/products/orig.png')))->toBeTrue();
});
