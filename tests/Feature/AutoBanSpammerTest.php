<?php

use App\Models\Spammer;
use App\Models\User;

test('3x gagal daftar dengan identitas sama otomatis jadi spammer', function () {
    User::factory()->create(['email' => 'bandel@example.com', 'no_hp' => '6281234567890']);
    $payload = registerPayload(['email' => 'bandel@example.com', 'no_hp' => '081234567890']);

    $this->post('/register', $payload)->assertSessionHasErrors('email');
    $this->post('/register', $payload)->assertSessionHasErrors('email');
    expect(Spammer::where('email', 'bandel@example.com')->exists())->toBeFalse();

    $this->post('/register', $payload)->assertSessionHasErrors('email');

    $spammer = Spammer::where('email', 'bandel@example.com')->firstOrFail();
    expect($spammer->reason)->toBe('auto_ban')
        ->and($spammer->status)->toBe(Spammer::STATUS_BLOCKED)
        ->and($spammer->no_hp)->toBe('6281234567890');
});

test('identitas hasil auto-ban ditolak untuk daftar berikutnya', function () {
    User::factory()->create(['email' => 'diblokir@example.com', 'no_hp' => '6281234560000']);
    $payload = registerPayload(['email' => 'diblokir@example.com', 'no_hp' => '081234560000']);

    foreach (range(1, 3) as $i) {
        $this->post('/register', $payload)->assertSessionHasErrors('email');
    }

    expect(Spammer::where('email', 'diblokir@example.com')->exists())->toBeTrue();

    // Lewati rate limit register (3x/menit) untuk percobaan berikutnya.
    $this->travel(2)->minutes();

    $this->post('/register', $payload)->assertSessionHasErrors('email');
    expect(User::where('email', 'diblokir@example.com')->count())->toBe(1);
});
