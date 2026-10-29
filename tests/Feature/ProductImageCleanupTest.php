<?php

use App\Models\User;

test('hard delete produk ikut menghapus file gambar asli dan watermark', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/office/products', productUploadPayload([
        'image' => \Illuminate\Http\UploadedFile::fake()->image('hapus.jpg'),
        'description_image' => \Illuminate\Http\UploadedFile::fake()->image('hapus-desc.jpg'),
    ]))->assertRedirect();

    $product = \App\Models\Product::where('sku', 'like', 'IMG-%')->latest('id')->firstOrFail();
    $stored = storage_path('app/private/'.$product->image_path);
    $master = storage_path('app/private/originals/'.$product->image_path);
    $storedDesc = storage_path('app/private/'.$product->description_image_path);

    expect(file_exists($stored))->toBeTrue()
        ->and(file_exists($master))->toBeTrue()
        ->and(file_exists($storedDesc))->toBeTrue();

    $this->actingAs($admin)->delete("/office/products/{$product->id}")->assertRedirect();

    expect(file_exists($stored))->toBeFalse()
        ->and(file_exists($master))->toBeFalse()
        ->and(file_exists($storedDesc))->toBeFalse();
});
