<?php

use App\Models\AdminLog;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;

test('duplicate produk menyalin field dan varian sebagai draft', function () {
    $admin = User::factory()->admin()->create();
    $product = productFinal255();
    ProductVariant::factory()->create(['product_id' => $product->id, 'name' => 'Varian A', 'sku' => 'SKU-A-'.uniqid()]);
    $product->update(['is_active' => true]);

    $this->actingAs($admin)->post("/office/products/{$product->id}/duplicate")->assertRedirect();

    $copy = Product::where('name', $product->name)->where('id', '!=', $product->id)->firstOrFail();

    expect($copy->is_active)->toBeFalse()
        ->and($copy->sku)->not->toBe($product->sku)
        ->and($copy->slug)->not->toBe($product->slug)
        ->and((float) $copy->price_rmb)->toBe((float) $product->price_rmb)
        ->and($copy->variants()->count())->toBe(1)
        ->and($copy->variants()->first()->name)->toBe('Varian A')
        ->and($copy->variants()->first()->sku)->not->toBe($product->variants->first()->sku)
        ->and(AdminLog::where('model', 'Product')->where('action', 'create')->where('model_id', $copy->id)->exists())->toBeTrue();
});

test('preview produk menampilkan tampilan user walau belum aktif', function () {
    $admin = User::factory()->admin()->create();
    $product = productFinal255();
    $product->update(['is_active' => false, 'name' => 'Produk Preview Draft']);

    $html = $this->actingAs($admin)->get("/office/products/{$product->id}/preview")
        ->assertOk()
        ->getContent();

    expect($html)->toContain('Produk Preview Draft');
});
