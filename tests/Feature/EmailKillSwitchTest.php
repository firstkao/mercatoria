<?php

use App\Models\EmailLog;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\AdminOtpNotification;
use App\Notifications\CartReminderNotification;
use Illuminate\Support\Facades\Notification;

function pauseCustomerEmails(bool $on): void
{
    Setting::updateOrCreate(
        ['key' => 'pause_customer_emails'],
        ['value' => $on ? '1' : '0', 'type' => 'bool', 'group' => 'email', 'description' => 'test']
    );
}

test('pause aktif — email customer dihentikan dan dicatat suppressed_maintenance', function () {
    pauseCustomerEmails(true);
    $user = User::factory()->create();

    $user->notify(new CartReminderNotification(2));

    $log = EmailLog::where('status', 'suppressed_maintenance')->sole();
    expect($log->recipient_email)->toBe($user->email)
        ->and($log->subject)->toContain('Pengingat Keranjang');

    // Email tidak terkirim: tidak ada baris 'sent'
    expect(EmailLog::where('status', 'sent')->exists())->toBeFalse();
});

test('pause nonaktif — email customer berjalan normal', function () {
    pauseCustomerEmails(false);
    $user = User::factory()->create();

    $user->notify(new CartReminderNotification(2));

    $log = EmailLog::where('status', 'sent')->sole();
    expect($log->recipient_email)->toBe($user->email)
        ->and(EmailLog::where('status', 'suppressed_maintenance')->exists())->toBeFalse();
});

test('pause aktif — notif admin dan cs@ tetap dikirim', function () {
    pauseCustomerEmails(true);
    $admin = User::factory()->admin()->create();

    $admin->notify(new AdminOtpNotification('123456'));
    Notification::route('mail', 'cs@mercatoria.id')
        ->notify(new CartReminderNotification(1));

    expect(EmailLog::where('status', 'suppressed_maintenance')->exists())->toBeFalse()
        ->and(EmailLog::where('status', 'sent')->count())->toBe(2);
});
