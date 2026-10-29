<?php

test('hotlinker menerima gambar decoy sedangkan referer mercatoria menerima asli', function () {
    // File gambar "asli" di storage privat produk.
    $dir = storage_path('app/private/products');
    if (! is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $im = imagecreatetruecolor(20, 20);
    imagefill($im, 0, 0, imagecolorallocate($im, 10, 200, 10));
    imagepng($im, $dir.'/hotlink-test.png');
    imagedestroy($im);
    $asliBytes = (string) file_get_contents($dir.'/hotlink-test.png');

    // Referer mercatoria -> gambar asli.
    $asli = $this->withHeaders(['Referer' => 'https://mercatoria.id/products/x'])
        ->get('/protected-images/hotlink-test.png')->assertOk();
    expect($asli->getContent())->toBe($asliBytes)
        ->and($asli->headers->get('Content-Type'))->toContain('image');

    // Hotlink dari domain lain -> gambar berbeda (decoy).
    $decoy = $this->withHeaders(['Referer' => 'https://evil.com/curi'])
        ->get('/protected-images/hotlink-test.png')->assertOk();
    expect($decoy->getContent())->not->toBe($asliBytes)
        ->and($decoy->headers->get('X-Protected-Image'))->toBe('1');

    // Tanpa referer (buka langsung) -> juga decoy (PRD 8.10 layer 4).
    $langsung = $this->get('/protected-images/hotlink-test.png')->assertOk();
    expect($langsung->getContent())->not->toBe($asliBytes);

    @unlink($dir.'/hotlink-test.png');
});
