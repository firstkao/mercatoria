<?php

use App\Models\AdminLog;
use App\Models\Game;
use App\Models\Product;
use App\Models\User;
use App\Services\Image\ImageOptimizerService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function productUploadPayload(array $overrides = []): array
{
    return array_merge([
        'game_id' => Game::factory()->create()->id,
        'name' => 'Produk Gambar',
        'sku' => 'IMG-'.uniqid(),
        'price_rmb' => 58,
        'weight_gram' => 100,
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
        'price_rmb' => 50,
        'availability' => 'tersedia',
        'image' => UploadedFile::fake()->image('varian.jpg'),
    ])->assertRedirect();

    $variant = $product->variants()->where('name', 'Varian Foto')->firstOrFail();

    expect($variant->image_path)->toContain('products/')
        ->and(file_exists(storage_path('app/private/'.$variant->image_path)))->toBeTrue();
});

test('upload gambar lebih dari 6mb ditolak', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/office/products', productUploadPayload([
        'image' => UploadedFile::fake()->image('besar.jpg')->size(6145),
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

test('gagal level PHP menampilkan alasan spesifik, bukan failed to upload generik', function () {
    $admin = User::factory()->admin()->create();

    $file = new UploadedFile('', 'besar.jpg', 'image/jpeg', UPLOAD_ERR_INI_SIZE, true);

    $this->actingAs($admin)->post('/office/products', productUploadPayload([
        'image' => $file,
    ]))->assertSessionHasErrors('image');

    expect(session('errors')->first('image'))
        ->toContain('Upload gagal:')
        ->toContain('file too large')
        ->not->toContain('failed to upload');
});

test('gagal level PHP pada description_image menampilkan alasan spesifik', function () {
    $admin = User::factory()->admin()->create();

    $file = new UploadedFile('', 'deskripsi.jpg', 'image/jpeg', UPLOAD_ERR_CANT_WRITE, true);

    $this->actingAs($admin)->post('/office/products', productUploadPayload([
        'description_image' => $file,
    ]))->assertSessionHasErrors('description_image');

    expect(session('errors')->first('description_image'))
        ->toContain('Upload gagal:')
        ->toContain('permission denied')
        ->not->toContain('failed to upload');
});

test('upload PNG besar (>4MB): original + compressed + thumb semua ada, compressed lebih kecil', function () {
    $admin = User::factory()->admin()->create();
    $file = noisyUploadedImage('besar.png', 1400, 1400);
    expect(filesize($file->getRealPath()))->toBeGreaterThan(4 * 1024 * 1024);

    $this->actingAs($admin)->post('/office/products', productUploadPayload([
        'description_image' => $file,
    ]))->assertRedirect();

    $product = Product::where('sku', 'like', 'IMG-%')->latest('id')->firstOrFail();
    $path = $product->description_image_path;

    $stored = storage_path('app/private/'.$path);
    $compressed = storage_path('app/private/'.ImageOptimizerService::variantPath($path, 'compressed'));
    $thumb = storage_path('app/private/'.ImageOptimizerService::variantPath($path, 'thumb'));

    expect(file_exists($stored))->toBeTrue()
        ->and(file_exists($compressed))->toBeTrue()
        ->and(file_exists($thumb))->toBeTrue()
        ->and(filesize($compressed))->toBeLessThan(filesize($stored));
});

test('upload JPEG: original + compressed + thumb semua ada, compressed lebih kecil', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/office/products', productUploadPayload([
        'image' => noisyUploadedImage('foto.jpg', 1200, 800),
    ]))->assertRedirect();

    $product = Product::where('sku', 'like', 'IMG-%')->latest('id')->firstOrFail();
    $path = $product->image_path;

    $stored = storage_path('app/private/'.$path);
    $compressed = storage_path('app/private/'.ImageOptimizerService::variantPath($path, 'compressed'));
    $thumb = storage_path('app/private/'.ImageOptimizerService::variantPath($path, 'thumb'));

    expect(file_exists($stored))->toBeTrue()
        ->and(file_exists($compressed))->toBeTrue()
        ->and(file_exists($thumb))->toBeTrue()
        ->and(filesize($compressed))->toBeLessThan(filesize($stored));
});

test('URL gambar di halaman product menunjuk ke file yang benar-benar ada di disk', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/office/products', productUploadPayload([
        'image' => UploadedFile::fake()->image('tampil.jpg'),
    ]))->assertRedirect();

    $product = Product::where('sku', 'like', 'IMG-%')->latest('id')->firstOrFail();
    $path = $product->image_path;

    $pairs = [
        ImageOptimizerService::url($path, 'compressed') => ImageOptimizerService::variantPath($path, 'compressed'),
        ImageOptimizerService::url($path, 'thumb') => ImageOptimizerService::variantPath($path, 'thumb'),
        Storage::url($path) => $path,
    ];

    foreach ($pairs as $url => $diskRelative) {
        $response = $this->get($url)->assertOk();
        expect($response->headers->get('Content-Type'))->toContain('image')
            ->and($response->getContent())->toBe(file_get_contents(storage_path('app/private/'.$diskRelative)));
    }
});

test('ganti foto produk: file lama terhapus dan set baru lengkap (original + compressed + thumb)', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/office/products', productUploadPayload([
        'image' => UploadedFile::fake()->image('satu.jpg'),
    ]))->assertRedirect();

    $product = Product::where('sku', 'like', 'IMG-%')->latest('id')->firstOrFail();
    $old = $product->image_path;

    $this->actingAs($admin)->put("/office/products/{$product->id}", productUploadPayload([
        'image' => UploadedFile::fake()->image('dua.jpg'),
    ]))->assertRedirect();

    $product->refresh();
    $new = $product->image_path;

    expect($new)->not->toBe($old);

    // Set lama bersih total (base + variants + master).
    expect(file_exists(storage_path('app/private/'.$old)))->toBeFalse()
        ->and(file_exists(storage_path('app/private/'.ImageOptimizerService::variantPath($old, 'compressed'))))->toBeFalse()
        ->and(file_exists(storage_path('app/private/'.ImageOptimizerService::variantPath($old, 'thumb'))))->toBeFalse()
        ->and(file_exists(storage_path('app/private/originals/'.$old)))->toBeFalse();

    // Set baru lengkap — original TIDAK ikut terhapus saat generate variants.
    expect(file_exists(storage_path('app/private/'.$new)))->toBeTrue()
        ->and(file_exists(storage_path('app/private/'.ImageOptimizerService::variantPath($new, 'compressed'))))->toBeTrue()
        ->and(file_exists(storage_path('app/private/'.ImageOptimizerService::variantPath($new, 'thumb'))))->toBeTrue();
});
