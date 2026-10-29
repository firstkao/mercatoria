<?php

use App\Models\AdminLog;
use App\Models\Coin;
use App\Models\Spammer;
use App\Models\User;

test('hard delete menghapus akun permanen beserta koin', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    Coin::factory()->create(['user_id' => $user->id, 'balance' => 50]);

    $this->actingAs($admin)->delete("/office/users/{$user->id}")->assertRedirect();

    expect(User::whereKey($user->id)->exists())->toBeFalse()
        ->and(Coin::where('user_id', $user->id)->exists())->toBeFalse()
        ->and(AdminLog::where('model', 'User')->where('action', 'delete')->where('model_id', $user->id)->exists())->toBeTrue();
});

test('hard delete ditolak untuk user yang punya order', function () {
    $admin = User::factory()->admin()->create();
    [$user] = placeTestOrder();

    $this->actingAs($admin)->delete("/office/users/{$user->id}")->assertSessionHasErrors('delete');

    expect(User::whereKey($user->id)->exists())->toBeTrue();
});

test('forever delete menghapus akun spammer dan data spammer tetap', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create(['email' => 'spammer@example.com']);
    $spammer = Spammer::factory()->create(['email' => 'spammer@example.com']);

    $this->actingAs($admin)->post("/office/users/{$user->id}/forever-delete", [
        'confirmation' => 'spammer@example.com',
    ])->assertRedirect();

    expect(User::whereKey($user->id)->exists())->toBeFalse()
        ->and(Spammer::whereKey($spammer->id)->exists())->toBeTrue()
        ->and(AdminLog::where('model', 'User')->where('action', 'forever_delete')->exists())->toBeTrue();
});

test('forever delete butuh konfirmasi keras berupa email user', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    Spammer::factory()->create(['email' => $user->email]);

    $this->actingAs($admin)->post("/office/users/{$user->id}/forever-delete", [
        'confirmation' => 'salah@example.com',
    ])->assertSessionHasErrors('confirmation');

    $this->actingAs($admin)->post("/office/users/{$user->id}/forever-delete")
        ->assertSessionHasErrors('confirmation');

    expect(User::whereKey($user->id)->exists())->toBeTrue();
});

test('forever delete hanya untuk user di spammer list', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $this->actingAs($admin)->post("/office/users/{$user->id}/forever-delete", [
        'confirmation' => $user->email,
    ])->assertSessionHasErrors('confirmation');

    expect(User::whereKey($user->id)->exists())->toBeTrue();
});

test('non-admin ditolak 403 menghapus user', function () {
    $customer = User::factory()->create();
    $user = User::factory()->create();
    Spammer::factory()->create(['email' => $user->email]);

    $this->actingAs($customer)->delete("/office/users/{$user->id}")->assertForbidden();
    $this->actingAs($customer)->post("/office/users/{$user->id}/forever-delete", [
        'confirmation' => $user->email,
    ])->assertForbidden();

    expect(User::whereKey($user->id)->exists())->toBeTrue();
});
