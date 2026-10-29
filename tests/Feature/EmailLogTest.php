<?php

use App\Models\EmailLog;
use App\Models\User;
use App\Notifications\AdminOtpNotification;
use App\Notifications\CartReminderNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;

test('email terkirim tercatat di email_logs', function () {
    Mail::fake();
    $user = User::factory()->create();

    $user->notify(new AdminOtpNotification('123456'));

    $log = EmailLog::sole();

    expect($log->recipient_email)->toBe($user->email)
        ->and($log->template_code)->toContain('AdminOtp')
        ->and($log->status)->toBe('sent')
        ->and($log->sent_at)->not->toBeNull();
});

test('email on-demand ke cs@ juga tercatat', function () {
    Mail::fake();

    Notification::route('mail', 'cs@mercatoria.id')
        ->notify(new CartReminderNotification(2));

    $log = EmailLog::sole();

    expect($log->recipient_email)->toBe('cs@mercatoria.id')
        ->and($log->template_code)->toContain('CartReminder')
        ->and($log->status)->toBe('sent');
});

test('tiap penerima punya baris log sendiri', function () {
    Mail::fake();
    $a = User::factory()->create();
    $b = User::factory()->create();

    Notification::send([$a, $b], new AdminOtpNotification('123456'));

    expect(EmailLog::count())->toBe(2)
        ->and(EmailLog::pluck('recipient_email')->all())->toContain($a->email)
        ->and(EmailLog::pluck('recipient_email')->all())->toContain($b->email);
});

test('kolom email_logs sesuai kebutuhan er', function () {
    $columns = Schema::getColumnListing('email_logs');

    expect($columns)->toContain('recipient_email')
        ->toContain('template_code')
        ->toContain('status')
        ->toContain('sent_at');
});
