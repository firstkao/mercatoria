<?php

use App\Models\AdminLog;
use App\Models\Setting;
use App\Models\User;

function putUser(User $actor, User $target, array $extra = [])
{
    return test()->actingAs($actor)->put("/office/users/{$target->id}", array_merge([
        'name' => $target->name,
        'email' => $target->email,
    ], $extra));
}

test('super_admin dapat mengganti role user lain', function () {
    $super = User::factory()->create(['role' => 'super_admin']);
    $target = User::factory()->create(['role' => 'customer']);

    putUser($super, $target, ['role' => 'admin'])->assertRedirect();

    expect($target->fresh()->role)->toBe('admin');
});

test('admin biasa yang mengganti role mendapat 403', function () {
    $admin = User::factory()->admin()->create();
    $target = User::factory()->create(['role' => 'customer']);

    putUser($admin, $target, ['role' => 'admin'])->assertForbidden();

    expect($target->fresh()->role)->toBe('customer');
});

test('admin biasa tetap bisa edit nama tanpa mengubah role', function () {
    $admin = User::factory()->admin()->create();
    $target = User::factory()->create(['role' => 'customer']);

    putUser($admin, $target, ['name' => 'Nama Baru', 'role' => 'customer'])->assertRedirect();

    expect($target->fresh()->name)->toBe('Nama Baru');
});

test('super_admin tidak boleh mengubah role dirinya sendiri', function () {
    $super = User::factory()->create(['role' => 'super_admin']);

    putUser($super, $super, ['role' => 'admin'])->assertForbidden();

    expect($super->fresh()->role)->toBe('super_admin');
});

test('super_admin tidak bisa dihapus, termasuk oleh super_admin lain', function () {
    $super = User::factory()->create(['role' => 'super_admin']);
    $other = User::factory()->create(['role' => 'super_admin']);

    $this->actingAs($super)->delete("/office/users/{$other->id}")->assertSessionHasErrors('delete');

    expect(User::find($other->id))->not->toBeNull();
});

test('admin biasa tidak bisa memposting role super_admin untuk dirinya', function () {
    $admin = User::factory()->admin()->create();

    putUser($admin, $admin, ['role' => 'super_admin'])->assertForbidden();

    expect($admin->fresh()->role)->toBe('admin');
});

test('dropdown role hanya tampil untuk super_admin pada user lain', function () {
    $super = User::factory()->create(['role' => 'super_admin']);
    $admin = User::factory()->admin()->create();
    $target = User::factory()->create(['role' => 'customer']);

    $this->actingAs($super)->get("/office/users/{$target->id}/edit")->assertSee('<select name="role"', false);
    $this->actingAs($super)->get("/office/users/{$super->id}/edit")->assertDontSee('<select name="role"', false);
    $this->actingAs($admin)->get("/office/users/{$target->id}/edit")->assertDontSee('<select name="role"', false);
});

test('super_admin lolos middleware admin', function () {
    $super = User::factory()->create(['role' => 'super_admin']);

    $this->actingAs($super)->get('/office/users')->assertOk();
});

test('kurs RMB divalidasi min 1000 max 10000', function () {
    $admin = User::factory()->admin()->create();
    Setting::create(['key' => 'rate_rmb_idr', 'value' => '3000', 'type' => 'string', 'group' => 'pricing']);

    foreach (['999', '10001', 'abc'] as $bad) {
        $this->actingAs($admin)->post('/office/settings', ['settings' => ['rate_rmb_idr' => $bad]])
            ->assertSessionHasErrors('settings.rate_rmb_idr');
    }

    $this->actingAs($admin)->post('/office/settings', ['settings' => ['rate_rmb_idr' => '2500']])->assertRedirect();

    expect(rmb_rate())->toBe(2500.0);
});

test('halaman settings menampilkan kurs terakhir diubah oleh siapa', function () {
    $admin = User::factory()->admin()->create(['name' => 'Pengubah Kurs']);

    $this->actingAs($admin)->post('/office/settings', ['settings' => ['rate_rmb_idr' => '3100']]);

    $this->actingAs($admin)->get('/office/settings')
        ->assertSee('Kurs terakhir diubah')
        ->assertSee('Pengubah Kurs');

    expect(AdminLog::where('description', 'Ubah setting rate_rmb_idr')->exists())->toBeTrue();
});
