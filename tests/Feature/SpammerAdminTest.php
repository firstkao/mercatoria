<?php

use App\Models\AdminLog;
use App\Models\AdminNotification;
use App\Models\Spammer;
use App\Models\User;

test('admin dapat inject spammer manual dengan bell notif', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/office/spammers', [
        'email' => 'nakal@example.com',
        'no_hp' => '081234567890',
    ])->assertRedirect();

    $spammer = Spammer::where('email', 'nakal@example.com')->firstOrFail();

    expect($spammer->reason)->toBe(Spammer::REASON_MANUAL)
        ->and($spammer->status)->toBe(Spammer::STATUS_BLOCKED)
        ->and($spammer->no_hp)->toBe('6281234567890')
        ->and($spammer->banned_at)->not->toBeNull()
        ->and(AdminLog::where('model', 'Spammer')->where('action', 'create')->exists())->toBeTrue();

    // PRD 41.1: bell admin — spammer baru
    $notif = AdminNotification::where('type', 'spammer_baru')->sole();
    expect($notif->title)->toContain('nakal@example.com')
        ->and($notif->target_url)->toBe('/office/spammers');
});

test('admin dapat unblock spammer', function () {
    $admin = User::factory()->admin()->create();
    $spammer = Spammer::factory()->create(['status' => Spammer::STATUS_BLOCKED, 'unblock_count' => 0]);

    $this->actingAs($admin)->post("/office/spammers/{$spammer->id}/unblock")->assertRedirect();

    $spammer->refresh();

    expect($spammer->status)->toBe(Spammer::STATUS_WATCHLIST)
        ->and($spammer->unblock_count)->toBe(1)
        ->and(AdminLog::where('model', 'Spammer')->where('action', 'update')->exists())->toBeTrue();
});

test('non-admin ditolak 403 kelola spammer', function () {
    $customer = User::factory()->create();
    $spammer = Spammer::factory()->create();

    $this->actingAs($customer)->get('/office/spammers')->assertForbidden();
    $this->actingAs($customer)->post('/office/spammers', ['email' => 'x@example.com', 'no_hp' => '081111111111'])->assertForbidden();
    $this->actingAs($customer)->post("/office/spammers/{$spammer->id}/unblock")->assertForbidden();

    expect($spammer->fresh()->status)->toBe(Spammer::STATUS_BLOCKED);
});
