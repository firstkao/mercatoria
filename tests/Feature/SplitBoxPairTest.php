<?php

use App\Models\Cart;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\SplitBoxSlot;
use App\Services\SplitBox\SplitBoxService;
use Illuminate\Support\Str;

function splitBoxService(): SplitBoxService
{
    return app(SplitBoxService::class);
}

test('pair kumulatif â€” 1 hot wajib 1 normal', function () {
    $fx = splitBoxFixture();

    expect(splitBoxService()->validatePair([
        ['slot' => $fx['hot'], 'qty' => 1],
        ['slot' => $fx['normalA'], 'qty' => 1],
    ]))->toBeTrue();

    expect(splitBoxService()->validatePair([
        ['slot' => $fx['hot'], 'qty' => 1],
    ]))->toBeFalse();
});

test('pair kumulatif â€” 2 hot wajib 2 normal', function () {
    $fx = splitBoxFixture();
    $hot2 = SplitBoxSlot::create([
        'split_box_id' => $fx['splitBox']->id,
        'variant_id' => ProductVariant::factory()->create(['product_id' => $fx['product']->id, 'name' => 'Hot 2', 'price_rmb' => 58, 'weight_gram' => 50])->id,
        'split_role' => 'hot_1',
        'qty_per_box' => 1,
        'sort_order' => 3,
    ]);

    // 2 hot + 1 normal â†’ gagal
    expect(splitBoxService()->validatePair([
        ['slot' => $fx['hot'], 'qty' => 1],
        ['slot' => $hot2, 'qty' => 1],
        ['slot' => $fx['normalA'], 'qty' => 1],
    ]))->toBeFalse();

    // 2 hot + 2 normal â†’ OK
    expect(splitBoxService()->validatePair([
        ['slot' => $fx['hot'], 'qty' => 1],
        ['slot' => $hot2, 'qty' => 1],
        ['slot' => $fx['normalA'], 'qty' => 1],
        ['slot' => $fx['normalB'], 'qty' => 1],
    ]))->toBeTrue();
});

test('hot_2 butuh 2 normal dan hot tidak bisa jadi pasangan', function () {
    $fx = splitBoxFixture();
    $hot2Role = SplitBoxSlot::create([
        'split_box_id' => $fx['splitBox']->id,
        'variant_id' => ProductVariant::factory()->create(['product_id' => $fx['product']->id, 'name' => 'Hot2Role', 'price_rmb' => 58, 'weight_gram' => 50])->id,
        'split_role' => 'hot_2',
        'qty_per_box' => 1,
        'sort_order' => 4,
    ]);

    // hot_2 + 1 hot_1 â†’ hot_1 bukan pasangan â†’ gagal
    expect(splitBoxService()->validatePair([
        ['slot' => $hot2Role, 'qty' => 1],
        ['slot' => $fx['hot'], 'qty' => 1],
    ]))->toBeFalse();

    // hot_2 + 2 normal â†’ OK
    expect(splitBoxService()->validatePair([
        ['slot' => $hot2Role, 'qty' => 1],
        ['slot' => $fx['normalA'], 'qty' => 1],
        ['slot' => $fx['normalB'], 'qty' => 1],
    ]))->toBeTrue();
});

test('qty ikut dihitung dalam kebutuhan pair', function () {
    $fx = splitBoxFixture(stockPerSlot: 5);

    // 2Ã— hot_1 â†’ butuh 2 normal; 1 normal tidak cukup
    expect(splitBoxService()->validatePair([
        ['slot' => $fx['hot'], 'qty' => 2],
        ['slot' => $fx['normalA'], 'qty' => 1],
    ]))->toBeFalse();

    expect(splitBoxService()->validatePair([
        ['slot' => $fx['hot'], 'qty' => 2],
        ['slot' => $fx['normalA'], 'qty' => 2],
    ]))->toBeTrue();
});

test('checkout menolak paket tanpa pair', function () {
    $fx = splitBoxFixture();

    $cart = Cart::create([
        'user_id' => $fx['user']->id,
        'product_id' => $fx['product']->id,
        'variant_id' => $fx['hot']->variant_id,
        'qty' => 1,
        'bundle_group_id' => (string) Str::uuid(),
    ]);

    $this->actingAs($fx['user'])->post('/checkout', checkoutPayload([
        'cart_item_ids' => [$cart->id],
    ]))->assertSessionHasErrors('cart_item_ids');

    expect(Order::count())->toBe(0);
});
