<?php

use App\Models\AdminNotification;
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

test('buat split box menyalin produk tanpa varian dengan is_split_box aktif', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create(['name' => 'Figure Asli', 'price_rmb' => 100, 'is_active' => true]);
    ProductVariant::factory()->create(['product_id' => $product->id]);

    $this->actingAs($admin)->post("/office/products/{$product->id}/split-clone", [
        'split_name' => 'Figure Asli (Split Box)',
        'split_price_rmb' => 25,
    ])->assertRedirect();

    $copy = Product::where('name', 'Figure Asli (Split Box)')->firstOrFail();

    expect($copy->is_split_box)->toBeTrue()
        ->and($copy->is_active)->toBeFalse()
        ->and((float) $copy->price_rmb)->toBe(25.0)
        ->and($copy->variants()->count())->toBe(0)
        ->and($product->fresh()->is_split_box)->toBeFalse();
});

test('varian, bulk produk dan bulk berat divalidasi lewat form request khusus', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create();

    $this->actingAs($admin)->post("/office/products/{$product->id}/variants", [])->assertSessionHasErrors(['name', 'sku', 'availability']);
    $this->actingAs($admin)->post('/office/products/bulk', ['action' => 'hapus'])->assertSessionHasErrors('ids');
    $this->actingAs($admin)->post("/office/products/{$product->id}/variants/bulk-weight", ['weight_gram' => 0])->assertSessionHasErrors(['weight_gram', 'ids']);
});

test('halaman marketing admin menyimpan promo bar dan tampil di storefront, link javascript ditolak', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/office/marketing')->assertOk();

    $this->actingAs($admin)->post('/office/marketing', [
        'promo_bar_text' => 'Diskon Akhir Tahun',
        'promo_bar_link' => 'javascript:alert(1)',
        'promo_bar_active' => 1,
    ])->assertSessionHasErrors('promo_bar_link');

    $this->actingAs($admin)->post('/office/marketing', [
        'promo_bar_text' => 'Diskon Akhir Tahun',
        'promo_bar_link' => '/katalog',
        'promo_bar_active' => 1,
    ])->assertRedirect('/office/marketing');

    $this->get('/')->assertSee('Diskon Akhir Tahun')->assertSee('href="/katalog"', false);
});

test('admin dapat menandai satu notifikasi dibaca', function () {
    $admin = User::factory()->admin()->create();
    $notification = AdminNotification::create(['type' => 'bukti_bayar', 'title' => 'Tes', 'target_url' => '/office/orders', 'is_read' => false]);

    $this->actingAs($admin)->post("/office/notifications/{$notification->id}/read")->assertRedirect('/office/orders');

    expect($notification->fresh()->is_read)->toBeTrue();
});

test('user terverifikasi yang membuka halaman verifikasi dialihkan, bukan error 500', function () {
    $this->actingAs(User::factory()->create())->get('/email/verify')->assertRedirect('/');
});

test('admin tidak dapat menghapus atau menurunkan role akunnya sendiri', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->delete("/office/users/{$admin->id}")->assertSessionHasErrors('delete');
    $this->actingAs($admin)->put("/office/users/{$admin->id}", [
        'name' => $admin->name, 'email' => $admin->email, 'role' => 'customer',
    ])->assertSessionHasErrors('role');

    expect($admin->fresh()->role)->toBe('admin');
});

test('edit user crush mempertahankan role crush', function () {
    $admin = User::factory()->admin()->create();
    $crush = User::factory()->create(['role' => 'crush']);

    $this->actingAs($admin)->put("/office/users/{$crush->id}", [
        'name' => $crush->name, 'email' => $crush->email, 'role' => 'crush',
    ])->assertRedirect();

    expect($crush->fresh()->role)->toBe('crush');
});

test('admin dapat mengunduh packing list PDF tanpa alamat; customer tidak bisa', function () {
    [$buyer, $order] = placeTestOrder();
    $order->update(['customer_note' => 'Bubble wrap ekstra']);

    $this->actingAs($buyer)->get("/office/orders/{$order->no_order}/packing-list")->assertForbidden();

    $response = $this->actingAs(User::factory()->admin()->create())->get("/office/orders/{$order->no_order}/packing-list");
    $response->assertOk()->assertHeader('Content-Type', 'application/pdf');
    expect(substr($response->getContent(), 0, 4))->toBe('%PDF');

    $html = view('admin.orders.packing-list', ['order' => $order->load(['items.snapshot', 'user'])])->render();
    expect($html)->toContain('Bubble wrap ekstra')->and($html)->not->toContain($buyer->alamat);
    $this->assertDatabaseHas('admin_logs', ['action' => 'download_packing_list']);
});
