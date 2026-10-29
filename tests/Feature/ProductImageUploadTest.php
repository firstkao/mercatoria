<?php

use App\Models\AdminLog;
use App\Models\Game;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;

function productUploadPayload(array $overrides = []): array
{
    return array_merge([
        'game_id' => Game::factory()->create()->id,
        'name' => 'Produk Gambar',
        'sku' => 'IMG-'.uniqid(),
        'price_rmb' => 58,
        'type' => 'single',
        'availability' => 'tersedia',
        'is_active' => 1,
    ], $overrides);
}

test('upload gambar produk menyimpan versi watermark dan master asli', function () {
    $admin = User::factory()->admin()->create();
    $file = UploadedFile::fake()->image('foto.jpg');

    $this->actingAs($admin)->post('/office/products', productUploadPayload([
        'image' => $file,
    ]))->assertRedirect();

    $product = Product::where('sku', 'like', 'IMG-%')->latest('id')->firstOrFail();

    expect($product->image_path)->toContain('products/');

    $stored = storage_path('app/private/'.$product->image_path);
    $master = storage_path('app/private/originals/'.$product->image_path);

    expect(file_exists($stored))->toBeTrue()
        ->and(file_exists($master))->toBeTrue()
        // Master asli identik dengan upload; versi tayang sudah kena watermark
        ->and(file_get_contents($master))->toBe(file_get_contents($file->getRealPath()))
        ->and(file_get_contents($stored))->not->toBe(file_get_contents($master));

    expect(AdminLog::where('model', 'Product')->where('action', 'create')->exists())->toBeTrue();
});

test('upload gambar deskripsi produk', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/office/products', productUploadPayload([
        'description_image' => UploadedFile::fake()->image('deskripsi.jpg'),
    ]))->assertRedirect();

    $product = Product::where('sku', 'like', 'IMG-%')->latest('id')->firstOrFail();

    expect($product->description_image_path)->toContain('products/')
        ->and(file_exists(storage_path('app/private/'.$product->description_image_path)))->toBeTrue()
        ->and(file_exists(storage_path('app/private/originals/'.$product->description_image_path)))->toBeTrue();
});

test('upload gambar varian', function () {
    $admin = User::factory()->admin()->create();
    $product = productFinal255();

    $this->actingAs($admin)->post("/office/products/{$product->id}/variants", [
        'name' => 'Varian Foto',
        'sku' => 'VAR-'.uniqid(),
        'availability' => 'tersedia',
        'image' => UploadedFile::fake()->image('varian.jpg'),
    ])->assertRedirect();

    $variant = $product->variants()->where('name', 'Varian Foto')->firstOrFail();

    expect($variant->image_path)->toContain('products/')
        ->and(file_exists(storage_path('app/private/'.$variant->image_path)))->toBeTrue();
});

test('upload gambar lebih dari 1mb ditolak', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/office/products', productUploadPayload([
        'image' => UploadedFile::fake()->image('besar.jpg')->size(1025),
    ]))->assertSessionHasErrors('image');

    expect(Product::where('sku', 'like', 'IMG-%')->exists())->toBeFalse();
});

test('upload gambar bertipe salah ditolak', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/office/products', productUploadPayload([
        'image' => UploadedFile::fake()->create('jahat.gif', 10, 'image/gif'),
    ]))->assertSessionHasErrors('image');

    expect(Product::where('sku', 'like', 'IMG-%')->exists())->toBeFalse();
});

test('image_path manual tetap kompatibel', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/office/products', productUploadPayload([
        'image_path' => 'products/manual-lama.png',
    ]))->assertRedirect();

    $product = Product::where('sku', 'like', 'IMG-%')->latest('id')->firstOrFail();

    expect($product->image_path)->toBe('products/manual-lama.png');
});
