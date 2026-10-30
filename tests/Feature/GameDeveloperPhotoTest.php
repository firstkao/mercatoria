<?php

use App\Models\Developer;
use App\Models\Game;
use App\Models\User;
use Illuminate\Http\UploadedFile;

test('upload logo game tersimpan di storage', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/office/games', [
        'name' => 'Game Berlogo',
        'is_active' => 1,
        'logo' => UploadedFile::fake()->image('logo-game.jpg'),
    ])->assertRedirect(route('office.games.index'));

    $game = Game::where('name', 'Game Berlogo')->firstOrFail();
    $path = (string) $game->logo_path;

    expect($path)->toStartWith('games/')
        ->and(file_exists(storage_path('app/private/'.$path)))->toBeTrue();
});

test('hard delete game menghapus file logo', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/office/games', [
        'name' => 'Game Berlogo Hapus',
        'is_active' => 1,
        'logo' => UploadedFile::fake()->image('logo-hapus.jpg'),
    ])->assertRedirect(route('office.games.index'));

    $game = Game::where('name', 'Game Berlogo Hapus')->firstOrFail();
    $path = (string) $game->logo_path;

    $this->actingAs($admin)->delete("/office/games/{$game->id}")->assertRedirect();

    expect(file_exists(storage_path('app/private/'.$path)))->toBeFalse();
});

test('upload logo developer tersimpan di storage', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/office/developers', [
        'name' => 'Dev Berlogo',
        'is_active' => 1,
        'logo' => UploadedFile::fake()->image('logo-dev.png'),
    ])->assertRedirect(route('office.developers.index'));

    $developer = Developer::where('name', 'Dev Berlogo')->firstOrFail();
    $path = (string) $developer->logo_path;

    expect($path)->toStartWith('developers/')
        ->and(file_exists(storage_path('app/private/'.$path)))->toBeTrue();
});

test('hard delete developer menghapus file logo', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/office/developers', [
        'name' => 'Dev Berlogo Hapus',
        'is_active' => 1,
        'logo' => UploadedFile::fake()->image('logo-dev-hapus.png'),
    ])->assertRedirect(route('office.developers.index'));

    $developer = Developer::where('name', 'Dev Berlogo Hapus')->firstOrFail();
    $path = (string) $developer->logo_path;

    $this->actingAs($admin)->delete("/office/developers/{$developer->id}")->assertRedirect();

    expect(file_exists(storage_path('app/private/'.$path)))->toBeFalse();
});
