<?php

use App\Models\Game;
use App\Models\Product;
use App\Models\User;

test('bulk game memperbarui yang ada dan membuat yang baru', function () {
    $admin = User::factory()->admin()->create();
    $existing = Game::factory()->create(['name' => 'Game Lama']);

    $this->actingAs($admin)->post('/office/games/bulk', [
        'games' => [
            ['id' => $existing->id, 'name' => 'Game Lama Baru', 'is_active' => 1],
            ['name' => 'Game Baru Bulk', 'is_active' => 1],
        ],
    ])->assertRedirect(route('office.games.index'));

    expect(Game::count())->toBe(2)
        ->and($existing->fresh()->name)->toBe('Game Lama Baru')
        ->and(Game::where('name', 'Game Baru Bulk')->exists())->toBeTrue();
});

test('bulk delete game menolak game dengan produk dan menghapus yang kosong', function () {
    $admin = User::factory()->admin()->create();
    $bersih = Game::factory()->create();
    $bermasalah = Game::factory()->create();
    Product::factory()->create(['game_id' => $bermasalah->id]);

    $this->actingAs($admin)->post('/office/games/bulk-delete', ['ids' => [$bersih->id, $bermasalah->id]])
        ->assertSessionHasErrors('ids');

    expect(Game::count())->toBe(2);

    $this->actingAs($admin)->post('/office/games/bulk-delete', ['ids' => [$bersih->id]])
        ->assertRedirect(route('office.games.index'));

    expect(Game::find($bersih->id))->toBeNull()
        ->and(Game::count())->toBe(1);
});
