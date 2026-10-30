<?php

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use App\Services\Cart\CheckoutService;

/**
 * Taruh file gambar asli di storage privat produk + set image_path.
 */
function snapImageFixture(Product $product, string $basename = 'img-snap-test.jpg'): string
{
    $path = 'products/'.$basename;
    $dir = storage_path('app/private/products');
    if (! is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    file_put_contents(storage_path('app/private/'.$path), 'fake-image-bytes');
    $product->update(['image_path' => $path]);

    return $path;
}

function snapPlaceOrder(User $user, Product $product): \App\Models\Order
{
    $cart = Cart::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'qty' => 1]);

    return app(CheckoutService::class)->placeOrder($user, [$cart->id], [
        'platform' => 'toco',
        'scheme' => 'fp',
        'payment_method' => 'bca',
        'discount_type' => 'none',
        'customer_note' => null,
    ]);
}

test('checkout freeze menyalin gambar produk ke order-snapshots', function () {
    $user = User::factory()->create();
    $product = productFinal255();
    $original = snapImageFixture($product);

    $order = snapPlaceOrder($user, $product);
    $copyPath = (string) $order->items()->first()->snapshot->product_image_path;

    expect($copyPath)->not->toBe($original)
        ->and($copyPath)->toStartWith('order-snapshots/')
        ->and(file_exists(storage_path('app/public/'.$copyPath)))->toBeTrue();
});

test('hard delete produk tidak menghapus gambar snapshot copy', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $product = productFinal255();
    snapImageFixture($product, 'img-snap-keep.jpg');

    $order = snapPlaceOrder($user, $product);
    $copyPath = (string) $order->items()->first()->snapshot->product_image_path;
    expect(file_exists(storage_path('app/public/'.$copyPath)))->toBeTrue();

    $this->actingAs($admin)->delete('/office/products/'.$product->id)->assertRedirect();

    expect(file_exists(storage_path('app/private/products/img-snap-keep.jpg')))->toBeFalse()
        ->and(file_exists(storage_path('app/public/'.$copyPath)))->toBeTrue();
});
