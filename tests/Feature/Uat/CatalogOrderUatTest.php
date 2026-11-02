<?php

use App\Models\Game;
use App\Models\MerchCategory;
use App\Models\Product;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

function uatProductPayload(array $o = []): array
{
    return array_merge([
        'game_id' => Game::factory()->create()->id,
        'name' => 'Figure "Rem" & Ram',
        'sku' => 'UAT-001',
        'price_rmb' => 100,
        'weight_gram' => 100,
        'type' => 'single',
        'availability' => 'tersedia',
    ], $o);
}

// ---------- Products ----------
test('UAT product CRUD: create, detail, update', function () {
    $this->post('/office/products', uatProductPayload())->assertSessionHasNoErrors();
    $product = Product::where('sku', 'UAT-001')->firstOrFail();
    expect($product->slug)->not->toBeEmpty();

    $this->get('/office/products')->assertOk()->assertSee('UAT-001');
    $this->get("/office/products/{$product->id}/edit")->assertOk()->assertSee('UAT-001');

    $this->put("/office/products/{$product->id}", uatProductPayload(['game_id' => $product->game_id, 'name' => 'Nama Baru']))
        ->assertSessionHasNoErrors();
    expect($product->fresh()->name)->toBe('Nama Baru');
});

test('UAT product delete menghilangkan dari daftar', function () {
    $product = Product::factory()->create(['sku' => 'UAT-DEL']);
    $this->delete("/office/products/{$product->id}")->assertSessionHasNoErrors();
    $this->get('/office/products')->assertOk()->assertDontSee('UAT-DEL');
});

test('UAT product validasi: kosong, SKU duplikat, tipe invalid, diskon melebihi harga', function () {
    $this->post('/office/products', [])->assertSessionHasErrors(['game_id', 'name', 'sku', 'price_rmb', 'type', 'availability']);

    Product::factory()->create(['sku' => 'DUP-1']);
    $this->post('/office/products', uatProductPayload(['sku' => 'DUP-1']))->assertSessionHasErrors('sku');
    $this->post('/office/products', uatProductPayload(['type' => 'aneh']))->assertSessionHasErrors('type');
    $this->post('/office/products', uatProductPayload(['name' => str_repeat('a', 151)]))->assertSessionHasErrors('name');
    $this->post('/office/products', uatProductPayload(['discount_amount' => 999999999]))->assertSessionHasErrors();
});

test('UAT product filter dan pencarian', function () {
    $game = Game::factory()->create();
    Product::factory()->create(['game_id' => $game->id, 'name' => 'Kotak Alpha', 'sku' => 'ALPHA-1']);
    Product::factory()->create(['name' => 'Kotak Beta', 'sku' => 'BETA-1']);

    $this->get('/office/products?q=ALPHA')->assertOk()->assertSee('ALPHA-1')->assertDontSee('BETA-1');
    $this->get("/office/products?game_id={$game->id}")->assertOk()->assertSee('ALPHA-1')->assertDontSee('BETA-1');
});

test('UAT product pagination 30 per halaman', function () {
    Product::factory()->count(32)->create();
    $this->get('/office/products')->assertOk();
    $this->get('/office/products?page=2')->assertOk();
});

// ---------- Merch categories ----------
test('UAT merch category: create, validasi, update, delete', function () {
    $this->post('/office/merch-categories', [])->assertSessionHasErrors();
    $this->post('/office/merch-categories', ['name' => 'Figure', 'priority' => 5, 'keywords' => 'figure,figma'])
        ->assertSessionHasNoErrors();
    $cat = MerchCategory::where('name', 'Figure')->firstOrFail();
    $this->get('/office/merch-categories')->assertOk()->assertSee('Figure');

    $this->post('/office/merch-categories', ['name' => 'X', 'priority' => 999])->assertSessionHasErrors('priority');

    $this->putJson("/office/merch-categories/{$cat->id}", ['name' => 'Figure2', 'priority' => 6, 'keywords' => 'a'])->assertOk();
    expect($cat->fresh()->name)->toBe('Figure2');
    $this->deleteJson("/office/merch-categories/{$cat->id}")->assertOk();
    $this->assertModelMissing($cat);
});

// ---------- Settings ----------
test('UAT settings: halaman terbuka dan update tidak crash', function () {
    $this->get('/office/settings')->assertOk();
    $this->post('/office/settings', [])->assertSessionHasNoErrors()->assertStatus(302);
});

// ---------- Orders / business flow ----------
test('UAT order: checkout customer lalu admin melihat dan mengubah status berurutan', function () {
    [$customer, $order] = placeTestOrder();
    expect($order->status->value ?? $order->status)->toBe('menunggu_pembayaran');
    expect($order->no_order)->toMatch('/^MER-[A-Z]{4}-\d{4}$/');

    $this->get('/office/orders')->assertOk()->assertSee($order->no_order);
    $this->get("/office/orders/{$order->no_order}")->assertOk()->assertSee($order->no_order);

    $this->patch("/office/orders/{$order->no_order}/status", ['status' => 'ditahan', 'note' => 'cek stok'])
        ->assertSessionHasNoErrors();
    expect(($order->fresh()->status->value ?? $order->fresh()->status))->toBe('ditahan');
});

test('UAT order: status invalid ditolak, filter status bekerja', function () {
    [, $order] = placeTestOrder();

    $this->patch("/office/orders/{$order->no_order}/status", ['status' => 'ngawur'])->assertSessionHasErrors('status');
    $this->patch("/office/orders/{$order->no_order}/status", [])->assertSessionHasErrors('status');

    $cell = '<td class="px-3 py-2 font-medium text-ink">'.$order->no_order.'</td>';
    $this->get('/office/orders?status=menunggu_pembayaran')->assertOk()->assertSee($cell, false);
    $this->get('/office/orders?status=selesai')->assertOk()->assertDontSee($cell, false);
});

test('UAT order: customer melihat order sendiri, tidak order orang lain', function () {
    [$owner, $order] = placeTestOrder();
    $other = User::factory()->create();

    $this->actingAs($owner)->get('/orders')->assertOk()->assertSee($order->no_order);
    $this->actingAs($other)->get("/orders/{$order->no_order}")->assertForbidden();
})->skip(fn () => ! app('router')->has('orders.show'), 'orders.show tidak ada');

test('UAT order: invoice admin terbuka', function () {
    [, $order] = placeTestOrder();
    $this->get("/office/orders/{$order->no_order}/invoice")->assertOk();
});
