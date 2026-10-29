<?php

use App\Models\AdminLog;
use App\Models\User;
use App\Models\UserVoucher;
use App\Models\Voucher;

test('admin dapat membuat voucher template', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/office/vouchers', [
        'code' => 'MER-ULTAH',
        'name' => 'Voucher Ulang Tahun',
        'nominal' => 25000,
        'min_belanja' => 100000,
        'valid_days' => 30,
        'is_active' => 1,
    ])->assertRedirect();

    $voucher = Voucher::where('code', 'MER-ULTAH')->firstOrFail();

    expect((float) $voucher->nominal)->toBe(25000.0)
        ->and((float) $voucher->min_belanja)->toBe(100000.0)
        ->and($voucher->valid_days)->toBe(30)
        ->and(AdminLog::where('model', 'Voucher')->where('action', 'create')->exists())->toBeTrue();
});

test('admin dapat mengubah voucher template', function () {
    $admin = User::factory()->admin()->create();
    $voucher = Voucher::factory()->create(['code' => 'UBAH-SAYA', 'nominal' => 10000]);

    $this->actingAs($admin)->put("/office/vouchers/{$voucher->id}", [
        'code' => 'UBAH-SAYA',
        'name' => 'Nama Baru',
        'nominal' => 15000,
        'min_belanja' => 50000,
        'valid_days' => 14,
        'is_active' => 1,
    ])->assertRedirect();

    expect($voucher->fresh()->name)->toBe('Nama Baru')
        ->and((float) $voucher->fresh()->nominal)->toBe(15000.0)
        ->and($voucher->fresh()->valid_days)->toBe(14)
        ->and(AdminLog::where('model', 'Voucher')->where('action', 'update')->exists())->toBeTrue();
});

test('admin dapat menghapus voucher template', function () {
    $admin = User::factory()->admin()->create();
    $voucher = Voucher::factory()->create();

    $this->actingAs($admin)->delete("/office/vouchers/{$voucher->id}")->assertRedirect();

    expect(Voucher::whereKey($voucher->id)->exists())->toBeFalse()
        ->and(AdminLog::where('model', 'Voucher')->where('action', 'delete')->exists())->toBeTrue();
});

test('issue voucher ke user membuat user_voucher dengan masa berlaku', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $voucher = Voucher::factory()->create(['valid_days' => 30]);

    $this->actingAs($admin)->post("/office/vouchers/{$voucher->id}/issue", [
        'user_id' => $user->id,
    ])->assertRedirect();

    $userVoucher = UserVoucher::where('user_id', $user->id)->where('voucher_id', $voucher->id)->firstOrFail();

    expect($userVoucher->status)->toBe('active')
        ->and($userVoucher->expires_at->diffInDays(now()->addDays(30)))->toBeLessThan(1)
        ->and(AdminLog::where('model', 'UserVoucher')->where('action', 'create')->exists())->toBeTrue();
});

test('admin dapat mencabut user_voucher', function () {
    $admin = User::factory()->admin()->create();
    $userVoucher = UserVoucher::factory()->create();

    $this->actingAs($admin)->delete("/office/user-vouchers/{$userVoucher->id}")->assertRedirect();

    expect(UserVoucher::whereKey($userVoucher->id)->exists())->toBeFalse()
        ->and(AdminLog::where('model', 'UserVoucher')->where('action', 'delete')->exists())->toBeTrue();
});

test('non-admin ditolak 403 kelola voucher', function () {
    $customer = User::factory()->create();
    $voucher = Voucher::factory()->create();
    $userVoucher = UserVoucher::factory()->create();

    $this->actingAs($customer)->post('/office/vouchers', ['code' => 'X', 'nominal' => 1000])->assertForbidden();
    $this->actingAs($customer)->put("/office/vouchers/{$voucher->id}", ['code' => $voucher->code, 'nominal' => 1000])->assertForbidden();
    $this->actingAs($customer)->delete("/office/vouchers/{$voucher->id}")->assertForbidden();
    $this->actingAs($customer)->post("/office/vouchers/{$voucher->id}/issue", ['user_id' => $customer->id])->assertForbidden();
    $this->actingAs($customer)->delete("/office/user-vouchers/{$userVoucher->id}")->assertForbidden();

    expect(UserVoucher::whereKey($userVoucher->id)->exists())->toBeTrue();
});

test('validasi voucher template menolak data tidak sah', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/office/vouchers', [
        'code' => '',
        'nominal' => -1,
    ])->assertSessionHasErrors(['code', 'nominal']);
});
