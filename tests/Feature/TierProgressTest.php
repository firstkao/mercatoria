<?php

use App\Models\Customer;
use App\Models\User;
use App\Services\User\UserTierService;

test('progress tier menghitung current next persen dan sisa belanja', function () {
    $service = app(UserTierService::class);
    $user = User::factory()->create();

    // Belum ada tier (total 0)
    $customer = Customer::factory()->create(['user_id' => $user->id, 'total_belanja' => 0, 'tier' => 'silver']);
    $p = $service->progress($customer);
    expect($p['current_tier'])->toBeNull()
        ->and($p['next_tier'])->toBe('silver')
        ->and($p['progress_pct'])->toBe(0.0);

    // Silver menuju Gold (threshold 2jt)
    $customer->update(['total_belanja' => 1500000]);
    $p = $service->progress($customer->fresh());
    expect($p['current_tier'])->toBe('silver')
        ->and($p['next_tier'])->toBe('gold')
        ->and($p['progress_pct'])->toBe(75.0)
        ->and($p['belanja_sisa'])->toBe(500001.0);

    // Gold menuju Platinum (threshold 5jt)
    $customer->update(['total_belanja' => 3000000]);
    $p = $service->progress($customer->fresh());
    expect($p['current_tier'])->toBe('gold')
        ->and($p['next_tier'])->toBe('platinum')
        ->and($p['belanja_sisa'])->toBe(2000001.0);

    // Platinum: bar penuh
    $customer->update(['total_belanja' => 6000000]);
    $p = $service->progress($customer->fresh());
    expect($p['current_tier'])->toBe('platinum')
        ->and($p['next_tier'])->toBeNull()
        ->and($p['progress_pct'])->toBe(100.0);
});

test('data progress dikirim ke dashboard user', function () {
    $user = User::factory()->create();
    Customer::factory()->create(['user_id' => $user->id, 'total_belanja' => 1500000, 'tier' => 'silver']);

    $progress = $this->actingAs($user)->get('/dashboard')->assertOk()->viewData('tierProgress');

    expect($progress['current_tier'])->toBe('silver')
        ->and($progress['next_tier'])->toBe('gold')
        ->and($progress['progress_pct'])->toBe(75.0)
        ->and($progress['belanja_sisa'])->toBe(500001.0)
        // kunci lama tetap ada (kompatibel UI)
        ->and($progress['gold'])->toBe(2000000.0);
});
