<?php

use App\Models\Cart;
use App\Models\User;
use App\Services\SplitBox\SplitBoxService;
use Illuminate\Support\Str;

test('menghapus satu karakter menghapus seluruh bundle', function () {
    $fx = splitBoxFixture();
    [$user, $order] = placeSplitBoxOrder($fx['user'], $fx['product'], [$fx['hot'], $fx['normalA']]);

    // Cart sudah terhapus saat placeOrder â€” buat bundle cart baru untuk uji hapus
    $bundleId = (string) Str::uuid();
    $cartHot = Cart::create([
        'user_id' => $fx['user']->id,
        'product_id' => $fx['product']->id,
        'variant_id' => $fx['hot']->variant_id,
        'qty' => 1,
        'bundle_group_id' => $bundleId,
    ]);
    $cartNormal = Cart::create([
        'user_id' => $fx['user']->id,
        'product_id' => $fx['product']->id,
        'variant_id' => $fx['normalA']->variant_id,
        'qty' => 1,
        'bundle_group_id' => $bundleId,
    ]);

    $this->actingAs($fx['user'])->delete("/cart/{$cartHot->id}");

    expect(Cart::where('bundle_group_id', $bundleId)->count())->toBe(0);
});

test('slot terkunci â†’ cart user lain yang tidak terpenuhi di-purge', function () {
    $fx = splitBoxFixture();

    // Cart user lain memuat bundle Hot + Normal A
    $other = User::factory()->create();
    $bundleId = (string) Str::uuid();
    Cart::create([
        'user_id' => $other->id,
        'product_id' => $fx['product']->id,
        'variant_id' => $fx['hot']->variant_id,
        'qty' => 1,
        'bundle_group_id' => $bundleId,
    ]);
    Cart::create([
        'user_id' => $other->id,
        'product_id' => $fx['product']->id,
        'variant_id' => $fx['normalA']->variant_id,
        'qty' => 1,
        'bundle_group_id' => $bundleId,
    ]);

    // Order pemilik mengunci stok (stock 1) â†’ cart user lain tak terpenuhi
    [$user, $order] = placeSplitBoxOrder($fx['user'], $fx['product'], [$fx['hot'], $fx['normalA']]);
    app(SplitBoxService::class)->lockSlotsForOrder($order);

    expect(Cart::where('user_id', $other->id)->count())->toBe(0);
});
