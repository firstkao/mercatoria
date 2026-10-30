<?php

use App\Models\Game;
use App\Models\User;
use Illuminate\Support\Str;

test('post game tanpa slug menghasilkan slug otomatis dari nama', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/office/games', [
        'name' => 'Game Uji Slug',
        'is_active' => 1,
    ])->assertRedirect(route('office.games.index'));

    $game = Game::where('name', 'Game Uji Slug')->firstOrFail();

    expect($game->slug)->toStartWith(Str::slug('Game Uji Slug').'-')
        ->and(strlen($game->slug))->toBeLessThanOrEqual(120);
});
