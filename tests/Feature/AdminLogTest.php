<?php

use App\Models\AdminLog;
use App\Models\Game;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;

test('aksi admin tercatat di admin logs dengan old dan new value', function () {
    $admin = User::factory()->admin()->create();
    $game = Game::factory()->create();
    $product = Product::factory()->create(['game_id' => $game->id, 'name' => 'Nama Lama']);

    $this->actingAs($admin)->put("/office/products/{$product->id}", [
        'game_id' => $game->id,
        'name' => 'Nama Baru',
        'sku' => $product->sku,
        'slug' => $product->slug,
        'price_rmb' => 90,
        'weight_gram' => 100,
        'type' => 'single',
        'availability' => 'tersedia',
        'is_active' => 1,
    ]);

    $log = AdminLog::where('model', 'Product')->where('model_id', $product->id)->firstOrFail();

    expect($log->admin_id)->toBe($admin->id)
        ->and($log->action)->toBe('update')
        ->and((array) $log->old_value)->toHaveKey('name')
        ->and((array) $log->new_value)->toHaveKey('name');
});

test('pembuatan produk tercatat', function () {
    $admin = User::factory()->admin()->create();
    $game = Game::factory()->create();

    $this->actingAs($admin)->post('/office/products', [
        'game_id' => $game->id,
        'name' => 'Produk Baru',
        'sku' => 'SKU-BARU-1',
        'slug' => 'produk-baru',
        'weight_gram' => 100,
        'price_rmb' => 58,
        'type' => 'single',
        'availability' => 'tersedia',
        'is_active' => 1,
    ]);

    expect(AdminLog::where('action', 'create')->where('model', 'Product')->exists())->toBeTrue();
});

test('perubahan status order tercatat di admin logs', function () {
    $admin = User::factory()->admin()->create();
    [, $order] = placeTestOrder();

    $this->actingAs($admin)->patch("/office/orders/{$order->no_order}/status", ['status' => 'ditahan']);

    $log = AdminLog::where('model', 'Order')->where('model_id', $order->id)->firstOrFail();
    expect($log->action)->toBe('update')
        ->and((array) $log->new_value)->toHaveKey('status');
});

test('perubahan settings tercatat di admin logs', function () {
    $admin = User::factory()->admin()->create();
    Setting::create(['key' => 'rate_rmb_idr', 'value' => '3000', 'type' => 'int', 'group' => 'pricing', 'description' => 'test']);

    $this->actingAs($admin)->post('/office/settings', [
        'settings' => ['rate_rmb_idr' => '3200'],
    ]);

    $log = AdminLog::where('model', 'Setting')->firstOrFail();
    expect($log->action)->toBe('update');
});

test('halaman admin logs dapat dibuka admin', function () {
    $admin = User::factory()->admin()->create();
    AdminLog::factory()->create(['admin_id' => $admin->id, 'action' => 'update', 'model' => 'Product', 'model_id' => 1]);

    $this->actingAs($admin)->get('/office/admin-logs')
        ->assertOk()
        ->assertSee('Ubah');
});
