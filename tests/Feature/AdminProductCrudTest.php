<?php

use App\Models\Game;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;

function adminUser(): User
{
    return User::factory()->admin()->create();
}

test('admin dapat membuat produk baru', function () {
    $admin = adminUser();
    $game = Game::factory()->create();

    $this->actingAs($admin)->post('/office/products', [
        'game_id' => $game->id,
        'name' => 'Cosmic Fragrance',
        'sku' => 'COS-001',
        'slug' => 'cosmic-fragrance',
        'price_rmb' => 58,
        'weight_gram' => 500,
        'type' => 'single',
        'availability' => 'tersedia',
        'is_active' => 1,
    ])->assertRedirect();

    $product = Product::where('sku', 'COS-001')->firstOrFail();
    expect($product->name)->toBe('Cosmic Fragrance')
        ->and($product->weight_gram)->toBe(500);
});

test('admin dapat mengubah produk dan varian', function () {
    $admin = adminUser();
    $product = Product::factory()->create(['name' => 'Nama Lama']);

    $this->actingAs($admin)->put("/office/products/{$product->id}", [
        'game_id' => $product->game_id,
        'name' => 'Nama Baru',
        'sku' => $product->sku,
        'slug' => $product->slug,
        'price_rmb' => 80,
        'weight_gram' => 250,
        'type' => 'variant',
        'availability' => 'tersedia',
        'is_active' => 1,
    ])->assertRedirect();

    expect($product->fresh()->name)->toBe('Nama Baru')
        ->and((float) $product->fresh()->price_rmb)->toEqual(80.0);

    $this->actingAs($admin)->post("/office/products/{$product->id}/variants", [
        'name' => 'Tartaglia',
        'sku' => 'VAR-TART-1',
        'price_rmb' => 58,
        'weight_gram' => 50,
        'availability' => 'tersedia',
    ])->assertRedirect();

    expect($product->variants()->count())->toBe(1);
});

test('hard delete produk — order_items set null, snapshot tetap utuh', function () {
    $admin = adminUser();
    [, $order] = placeTestOrder();
    $snapshot = $order->items->first()->snapshot;
    $productId = $order->items->first()->product_id;
    $namaAsli = $snapshot->product_name;

    $this->actingAs($admin)->delete("/office/products/{$productId}")->assertRedirect();

    expect(Product::where('id', $productId)->exists())->toBeFalse()
        ->and($order->items->first()->fresh()->product_id)->toBeNull()
        ->and($snapshot->fresh()->product_name)->toBe($namaAsli);
});

test('bulk nonaktifkan produk', function () {
    $admin = adminUser();
    $a = Product::factory()->create(['is_active' => true]);
    $b = Product::factory()->create(['is_active' => true]);

    $this->actingAs($admin)->post('/office/products/bulk', [
        'action' => 'nonaktifkan',
        'ids' => [$a->id, $b->id],
    ])->assertRedirect();

    expect($a->fresh()->is_active)->toBeFalse()
        ->and($b->fresh()->is_active)->toBeFalse();
});

test('bulk edit berat varian set same', function () {
    $admin = adminUser();
    $product = Product::factory()->create(['type' => 'variant']);
    $v1 = ProductVariant::factory()->create(['product_id' => $product->id, 'weight_gram' => 100]);
    $v2 = ProductVariant::factory()->create(['product_id' => $product->id, 'weight_gram' => 200]);

    $this->actingAs($admin)->post("/office/products/{$product->id}/variants/bulk-weight", [
        'weight_gram' => 250,
        'ids' => [$v1->id, $v2->id],
    ])->assertRedirect();

    expect($v1->fresh()->weight_gram)->toBe(250)
        ->and($v2->fresh()->weight_gram)->toBe(250);
});

test('field produk wajib divalidasi', function () {
    $admin = adminUser();

    $this->actingAs($admin)->post('/office/products', [
        'name' => '',
        'sku' => '',
        'price_rmb' => -5,
    ])->assertSessionHasErrors(['name', 'sku', 'price_rmb']);
});
