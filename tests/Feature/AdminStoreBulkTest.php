<?php

use App\Models\Game;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;

test('bulk store memperbarui yang ada dan membuat yang baru', function () {
    $admin = User::factory()->admin()->create();
    $existing = Store::factory()->create(['name' => 'Store Lama', 'min_belanja_rmb' => 10, 'ongkir_rmb' => 5]);

    $this->actingAs($admin)->post('/office/stores/bulk', [
        'stores' => [
            ['id' => $existing->id, 'name' => 'Store Lama Baru', 'min_belanja_rmb' => 20, 'ongkir_rmb' => 7],
            ['name' => 'Store Baru', 'min_belanja_rmb' => 100, 'ongkir_rmb' => 0],
        ],
    ])->assertRedirect(route('office.stores.index'));

    expect(Store::count())->toBe(2)
        ->and($existing->fresh()->name)->toBe('Store Lama Baru')
        ->and((float) $existing->fresh()->min_belanja_rmb)->toEqual(20.0)
        ->and(Store::where('name', 'Store Baru')->where('ongkir_rmb', 0)->exists())->toBeTrue();
});

test('bulk delete menolak store dengan produk atau game dan menghapus yang kosong', function () {
    $admin = User::factory()->admin()->create();
    $bersih = Store::factory()->create();
    $denganProduk = Store::factory()->create();
    $denganGame = Store::factory()->create();
    Product::factory()->create(['store_id' => $denganProduk->id]);
    Game::factory()->create(['default_store_id' => $denganGame->id]);

    // Ada produk -> tolak, tidak ada yang terhapus.
    $this->actingAs($admin)->post('/office/stores/bulk-delete', ['ids' => [$bersih->id, $denganProduk->id]])
        ->assertSessionHasErrors('ids');

    // Ada game -> tolak juga.
    $this->actingAs($admin)->post('/office/stores/bulk-delete', ['ids' => [$bersih->id, $denganGame->id]])
        ->assertSessionHasErrors('ids');

    expect(Store::count())->toBe(3);

    // Semua kosong -> terhapus sekaligus.
    $this->actingAs($admin)->post('/office/stores/bulk-delete', ['ids' => [$bersih->id]])
        ->assertRedirect(route('office.stores.index'));

    expect(Store::find($bersih->id))->toBeNull()
        ->and(Store::count())->toBe(2);
});
