<?php

use App\Models\Game;
use App\Models\Spammer;
use App\Models\SpammerIp;
use App\Models\User;

test('ip terdaftar spammer_ips diblokir dari katalog tapi beranda dan login tetap bisa', function () {
    SpammerIp::create([
        'ip_address' => '203.0.113.5',
        'device_fingerprint' => 'abc123',
        'attempt_count' => 2,
        'last_attempt_at' => now(),
        'expires_at' => now()->addDays(20),
    ]);

    $this->withServerVariables(['HTTP_X_FORWARDED_FOR' => '203.0.113.5'])
        ->get('/login')->assertOk();

    $this->withServerVariables(['HTTP_X_FORWARDED_FOR' => '203.0.113.5'])
        ->get('/')->assertOk();

    $game = Game::factory()->create(['is_active' => true]);
    $this->withServerVariables(['HTTP_X_FORWARDED_FOR' => '203.0.113.5'])
        ->get('/'.$game->slug)->assertRedirect('/');

    $this->withServerVariables(['HTTP_X_FORWARDED_FOR' => '203.0.113.5'])
        ->get('/register')->assertRedirect('/');
});

test('ip dengan ttl kadaluarsa tidak lagi diblokir', function () {
    SpammerIp::create([
        'ip_address' => '203.0.113.5',
        'attempt_count' => 2,
        'last_attempt_at' => now()->subDays(40),
        'expires_at' => now()->subDays(10),
    ]);

    $user = User::factory()->create();
    $game = Game::factory()->create(['is_active' => true]);

    $this->withServerVariables(['HTTP_X_FORWARDED_FOR' => '203.0.113.5'])
        ->actingAs($user)
        ->get('/'.$game->slug)->assertOk();
});

test('register dengan identitas ter-block mencatat ip ke spammer_ips', function () {
    Spammer::factory()->create(['email' => 'diblokir@example.com', 'status' => Spammer::STATUS_BLOCKED]);

    $this->withServerVariables(['HTTP_X_FORWARDED_FOR' => '203.0.113.9'])
        ->post('/register', registerPayload(['email' => 'diblokir@example.com']))
        ->assertSessionHasErrors('email');

    $row = SpammerIp::where('ip_address', '203.0.113.9')->firstOrFail();

    expect($row->attempt_count)->toBe(1)
        ->and($row->device_fingerprint)->not->toBeNull()
        ->and($row->expires_at->diffInDays(now()->addDays(30)))->toBeLessThan(1);

    // Register dari IP ini kini diblokir middleware; percobaan LOGIN dengan
    // identitas ter-block tetap tercatat dan memperpanjang TTL (PRD 8.5/10.5).
    $this->travel(1)->minutes();
    $this->withServerVariables(['HTTP_X_FORWARDED_FOR' => '203.0.113.9'])
        ->post('/login', ['email' => 'diblokir@example.com', 'password' => 'password123'])
        ->assertSessionHasErrors('email');

    expect($row->fresh()->attempt_count)->toBe(2)
        ->and($row->fresh()->expires_at->isFuture())->toBeTrue();
});
