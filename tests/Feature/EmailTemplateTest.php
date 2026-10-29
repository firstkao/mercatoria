<?php

use App\Models\AdminLog;
use App\Models\EmailTemplate;
use App\Models\User;
use App\Notifications\CartReminderNotification;
use App\Services\Mail\EmailTemplateService;

test('admin dapat kelola email template', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/office/email-templates', [
        'code' => 'cart_reminder',
        'subject' => 'Pengingat {{nama_user}}',
        'body' => 'Keranjangmu menunggu, {{nama_user}}.',
        'is_active' => 1,
    ])->assertRedirect();

    $tpl = EmailTemplate::where('code', 'cart_reminder')->firstOrFail();

    expect($tpl->subject)->toBe('Pengingat {{nama_user}}')
        ->and($tpl->is_active)->toBeTrue()
        ->and(AdminLog::where('model', 'EmailTemplate')->where('action', 'create')->exists())->toBeTrue();

    $this->actingAs($admin)->put("/office/email-templates/{$tpl->id}", [
        'code' => 'cart_reminder',
        'subject' => 'Baru',
        'body' => 'Isi baru',
        'is_active' => 0,
    ])->assertRedirect();

    expect($tpl->fresh()->subject)->toBe('Baru')
        ->and($tpl->fresh()->is_active)->toBeFalse();

    $this->actingAs($admin)->delete("/office/email-templates/{$tpl->id}")->assertRedirect();

    expect(EmailTemplate::whereKey($tpl->id)->exists())->toBeFalse()
        ->and(AdminLog::where('model', 'EmailTemplate')->where('action', 'delete')->exists())->toBeTrue();
});

test('render placeholder mengganti variabel standar', function () {
    EmailTemplate::factory()->create([
        'code' => 'test_render',
        'subject' => 'Halo {{nama_user}}',
        'body' => 'Order {{no_order}} senilai {{total}}. Hubungi {{no_hp}} / {{email}}. Link: {{link}}',
        'is_active' => true,
    ]);

    $rendered = app(EmailTemplateService::class)->render('test_render', [
        'nama_user' => 'Budi',
        'no_order' => 'MER-TEST-0001',
        'total' => 'Rp255.000',
        'no_hp' => '6281234567890',
        'email' => 'budi@example.com',
        'link' => 'https://mercatoria.id/x',
    ]);

    expect($rendered['subject'])->toBe('Halo Budi')
        ->and($rendered['body'])->toBe('Order MER-TEST-0001 senilai Rp255.000. Hubungi 6281234567890 / budi@example.com. Link: https://mercatoria.id/x');
});

test('template aktif meng-override notifikasi saat dikirim', function () {
    $user = User::factory()->create(['name' => 'Siti']);

    // Tanpa template → default
    $default = (new CartReminderNotification(2))->toMail($user);
    expect($default->subject)->toContain('Pengingat Keranjang');

    // Dengan template aktif → subject/body dari template
    EmailTemplate::factory()->create([
        'code' => 'cart_reminder',
        'subject' => 'Keranjangmu {{nama_user}}',
        'body' => 'Ada item menunggu, {{nama_user}}.',
        'is_active' => true,
    ]);

    $overridden = (new CartReminderNotification(2))->toMail($user);
    expect($overridden->subject)->toBe('Keranjangmu Siti')
        ->and(implode(' ', $overridden->introLines))->toContain('Ada item menunggu, Siti.');
});

test('template nonaktif tidak meng-override notifikasi', function () {
    $user = User::factory()->create();
    EmailTemplate::factory()->create([
        'code' => 'cart_reminder',
        'subject' => 'Tidak dipakai',
        'body' => 'Tidak dipakai',
        'is_active' => false,
    ]);

    $mail = (new CartReminderNotification(2))->toMail($user);

    expect($mail->subject)->toContain('Pengingat Keranjang');
});
