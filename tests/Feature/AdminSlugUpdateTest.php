<?php

use App\Models\Developer;
use App\Models\Game;
use App\Models\User;
use Illuminate\Support\Str;

test('update game dengan slug manual tersimpan dan kosong menghasilkan slug dari nama', function () {
    $admin = User::factory()->admin()->create();
    $game = Game::factory()->create(['name' => 'Game Slug Update']);

    $this->actingAs($admin)->put("/office/games/{$game->id}", [
        'name' => 'Game Slug Update',
        'slug' => 'slug-manual-game',
        'is_active' => 1,
    ])->assertRedirect();

    expect($game->fresh()->slug)->toBe('slug-manual-game');

    $this->actingAs($admin)->put("/office/games/{$game->id}", [
        'name' => 'Game Slug Update',
        'slug' => '',
        'is_active' => 1,
    ])->assertRedirect();

    expect($game->fresh()->slug)->toBe(Str::slug('Game Slug Update'));
});

test('update developer dengan slug manual tersimpan dan kosong menghasilkan slug dari nama', function () {
    $admin = User::factory()->admin()->create();
    $developer = Developer::factory()->create(['name' => 'Dev Slug Update']);

    $this->actingAs($admin)->put("/office/developers/{$developer->id}", [
        'name' => 'Dev Slug Update',
        'slug' => 'slug-manual-dev',
        'is_active' => 1,
    ])->assertRedirect();

    expect($developer->fresh()->slug)->toBe('slug-manual-dev');

    $this->actingAs($admin)->put("/office/developers/{$developer->id}", [
        'name' => 'Dev Slug Update',
        'slug' => '',
        'is_active' => 1,
    ])->assertRedirect();

    expect($developer->fresh()->slug)->toBe(Str::slug('Dev Slug Update'));
});
