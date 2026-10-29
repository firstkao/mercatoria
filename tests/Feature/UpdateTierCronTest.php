<?php

use App\Models\Customer;
use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;

test('customer:update-tier mengevaluasi tier dari total belanja', function () {
    $user = User::factory()->create();
    Customer::factory()->create([
        'user_id' => $user->id,
        'tier' => 'silver',
        'total_belanja' => 3000000, // > threshold gold 2jt, belum dievaluasi
    ]);

    $this->artisan('customer:update-tier')->assertSuccessful();

    expect($user->customer->fresh()->tier)->toBe('gold');
});

test('customer:update-tier juga menurunkan tier yang tidak lagi memenuhi', function () {
    $user = User::factory()->create();
    Customer::factory()->create([
        'user_id' => $user->id,
        'tier' => 'platinum',
        'total_belanja' => 100000,
    ]);

    $this->artisan('customer:update-tier')->assertSuccessful();

    expect($user->customer->fresh()->tier)->toBe('silver');
});

test('jadwal customer:update-tier terdaftar 02:30 asia jakarta', function () {
    $events = collect(app(Schedule::class)->events());
    $event = $events->first(fn ($e) => str_contains((string) $e->command, 'customer:update-tier'));

    expect($event->expression)->toBe('30 2 * * *')
        ->and($event->timezone)->toBe('Asia/Jakarta');
});
