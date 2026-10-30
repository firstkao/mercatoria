<?php

use App\Models\PaymentMethod;
use App\Models\User;

test('admin dapat kelola metode bayar', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/office/payment-methods', [
        'code' => 'dana',
        'name' => 'DANA',
        'type' => 'bank_transfer',
        'fee' => 1500,
        'is_active' => 1,
    ])->assertRedirect();

    $method = PaymentMethod::where('code', 'dana')->firstOrFail();

    expect($method->name)->toBe('DANA')
        ->and($method->type)->toBe('bank_transfer')
        ->and((float) $method->fee)->toBe(1500.0)
        ->and($method->is_active)->toBeTrue();

    $this->actingAs($admin)->put("/office/payment-methods/{$method->id}", [
        'code' => 'dana',
        'name' => 'DANA Indonesia',
        'type' => 'bank_transfer',
        'fee' => 2500,
        'is_active' => 0,
    ])->assertRedirect();

    expect($method->fresh()->name)->toBe('DANA Indonesia')
        ->and($method->fresh()->is_active)->toBeFalse();

    $this->actingAs($admin)->delete("/office/payment-methods/{$method->id}")->assertRedirect();

    expect(PaymentMethod::whereKey($method->id)->exists())->toBeFalse();
});

test('metode bayar bertipe ewallet ditolak', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/office/payment-methods', [
        'code' => 'dana',
        'name' => 'DANA',
        'type' => 'ewallet',
        'fee' => 1500,
        'is_active' => 1,
    ])->assertSessionHasErrors('type');

    expect(PaymentMethod::where('code', 'dana')->exists())->toBeFalse();
});

test('non-admin ditolak 403 kelola metode bayar', function () {
    $customer = User::factory()->create();
    $method = PaymentMethod::factory()->create();

    $this->actingAs($customer)->post('/office/payment-methods', ['code' => 'x', 'name' => 'X'])->assertForbidden();
    $this->actingAs($customer)->put("/office/payment-methods/{$method->id}", ['code' => $method->code, 'name' => 'Y'])->assertForbidden();
    $this->actingAs($customer)->delete("/office/payment-methods/{$method->id}")->assertForbidden();

    expect(PaymentMethod::whereKey($method->id)->exists())->toBeTrue();
});
