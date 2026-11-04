<?php

test('route /storage menyajikan file gambar tayang dari storage privat', function (string $dir) {
    $name = 'serve-test-'.uniqid().'.png';
    @mkdir(storage_path('app/private/'.$dir), 0755, true);
    file_put_contents(storage_path('app/private/'.$dir.'/'.$name), 'PNGDATA');

    $this->get("/storage/{$dir}/{$name}")->assertOk()->assertSee('PNGDATA', false);

    @unlink(storage_path('app/private/'.$dir.'/'.$name));
})->with(['products', 'games', 'developers', 'hero-slides']);

test('route /storage menolak master originals dan payment-proofs', function (string $path) {
    $this->get("/storage/{$path}")->assertNotFound();
})->with([
    'originals/products/rahasia.png',
    'payment-proofs/bukti-transfer.jpg',
]);

test('route /storage menolak path traversal', function () {
    $this->get('/storage/products/../payment-proofs/bukti.jpg')->assertNotFound();
});
