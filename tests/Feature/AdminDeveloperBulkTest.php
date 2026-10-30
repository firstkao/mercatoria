<?php

use App\Models\Developer;
use App\Models\Game;
use App\Models\User;

test('bulk developer memperbarui yang ada dan membuat yang baru', function () {
    $admin = User::factory()->admin()->create();
    $existing = Developer::factory()->create(['name' => 'Dev Lama']);

    $this->actingAs($admin)->post('/office/developers/bulk', [
        'developers' => [
            ['id' => $existing->id, 'name' => 'Dev Lama Baru', 'is_active' => 1],
            ['name' => 'Dev Baru Bulk', 'is_active' => 1],
        ],
    ])->assertRedirect(route('office.developers.index'));

    expect(Developer::count())->toBe(2)
        ->and($existing->fresh()->name)->toBe('Dev Lama Baru')
        ->and(Developer::where('name', 'Dev Baru Bulk')->exists())->toBeTrue();
});

test('bulk delete developer menolak developer dengan game dan menghapus yang kosong', function () {
    $admin = User::factory()->admin()->create();
    $bersih = Developer::factory()->create();
    $bermasalah = Developer::factory()->create();
    Game::factory()->create()->developers()->attach($bermasalah->id);

    $this->actingAs($admin)->post('/office/developers/bulk-delete', ['ids' => [$bersih->id, $bermasalah->id]])
        ->assertSessionHasErrors('ids');

    expect(Developer::count())->toBe(2);

    $this->actingAs($admin)->post('/office/developers/bulk-delete', ['ids' => [$bersih->id]])
        ->assertRedirect(route('office.developers.index'));

    expect(Developer::find($bersih->id))->toBeNull()
        ->and(Developer::count())->toBe(1);
});
