<?php

use App\Models\Game;
use App\Models\Page;
use App\Models\User;
use Illuminate\Support\Str;

test('post game tanpa slug menghasilkan slug otomatis dari nama', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/office/games', [
        'name' => 'Game Uji Slug',
        'is_active' => 1,
    ])->assertRedirect(route('office.games.index'));

    $game = Game::where('name', 'Game Uji Slug')->firstOrFail();

    expect($game->slug)->toBe(Str::slug('Game Uji Slug'))
        ->and(strlen($game->slug))->toBeLessThanOrEqual(120);
});

test('slug game tidak boleh bentrok dengan slug halaman cms', function () {
    $admin = User::factory()->admin()->create();
    $game = Game::factory()->create();
    Page::factory()->create(['slug' => 'tentang-kami']);

    $this->actingAs($admin)->put("/office/games/{$game->id}", [
        'name' => 'Game Slug Tabrak',
        'slug' => 'tentang-kami',
        'is_active' => 1,
    ])->assertSessionHasErrors('slug');

    // Auto-generate saat create juga menghindari slug halaman.
    $this->actingAs($admin)->post('/office/games', [
        'name' => 'Tentang Kami',
        'is_active' => 1,
    ])->assertRedirect(route('office.games.index'));

    expect(Game::where('name', 'Tentang Kami')->firstOrFail()->slug)
        ->toStartWith('tentang-kami-');
});
