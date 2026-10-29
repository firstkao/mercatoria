<?php

use App\Models\EmailTemplate;
use App\Services\Mail\EmailTemplateService;
use Database\Seeders\EmailTemplateSeeder;

test('seeder menyiapkan 27 template email standar', function () {
    $this->seed(EmailTemplateSeeder::class);

    expect(EmailTemplate::count())->toBe(27)
        ->and(EmailTemplate::where('code', 'admin_payment_notification')->exists())->toBeTrue()
        ->and(EmailTemplate::where('code', 'user_gift_notification')->exists())->toBeTrue();

    // Seed ulang tidak menduplikasi
    $this->seed(EmailTemplateSeeder::class);
    expect(EmailTemplate::count())->toBe(27);
});

test('variabel tanggal dan nama_toko didukung render template', function () {
    EmailTemplate::factory()->create([
        'code' => 'uji_var',
        'subject' => 'Halo {{nama_user}} dari {{nama_toko}}',
        'body' => 'Tanggal {{tanggal}} — link {{link}}',
        'is_active' => true,
    ]);

    $rendered = app(EmailTemplateService::class)->render('uji_var', [
        'nama_user' => 'Budi',
        'link' => 'https://mercatoria.id/x',
    ]);

    expect($rendered['subject'])->toBe('Halo Budi dari '.config('app.name'))
        ->and($rendered['body'])->toBe('Tanggal '.now()->format('d/m/Y').' — link https://mercatoria.id/x');
});
