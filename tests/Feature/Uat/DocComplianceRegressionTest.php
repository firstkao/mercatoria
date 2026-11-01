<?php

use App\Models\Customer;
use App\Models\OrderStatusLog;
use App\Models\Page;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\Setting;
use App\Models\Spammer;
use App\Models\Tag;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\Order\LatePenaltyService;
use App\Services\Order\OrderStatusService;
use App\Services\Order\ShippingCalculatorService;
use Database\Seeders\PageSeeder;
use Database\Seeders\TagSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

test('PRD 43: notifikasi hanya bisa ditandai dibaca oleh pemiliknya', function () {
    $owner = User::factory()->create();
    $notification = UserNotification::factory()->create(['user_id' => $owner->id, 'is_read' => false]);

    $this->actingAs(User::factory()->create())
        ->post("/profile/notifications/{$notification->id}/read")->assertForbidden();
    expect($notification->fresh()->is_read)->toBeFalse();

    $this->actingAs($owner)->post("/profile/notifications/{$notification->id}/read")->assertRedirect();
    expect($notification->fresh()->is_read)->toBeTrue();
});

test('PRD 34: refund partial tidak boleh melebihi pembayaran terverifikasi dikurangi refund sebelumnya', function () {
    [, $order] = placeTestOrder();
    Payment::factory()->create(['order_id' => $order->id, 'type' => Payment::TYPE_FULL, 'amount' => 100000, 'status' => Payment::STATUS_VERIFIED]);
    $this->actingAs(User::factory()->admin()->create());

    $this->post("/office/orders/{$order->no_order}/refund/partial", ['amount' => 100001, 'reason' => 'x'])
        ->assertSessionHasErrors('amount');
    expect(Refund::count())->toBe(0);

    $this->post("/office/orders/{$order->no_order}/refund/partial", ['amount' => 60000, 'reason' => 'x'])
        ->assertSessionHasNoErrors();
    $this->post("/office/orders/{$order->no_order}/refund/partial", ['amount' => 60000, 'reason' => 'x'])
        ->assertSessionHasErrors('amount');
    expect(Refund::count())->toBe(1);
});

test('PRD 51: transisi ke Hangus mengisi hangus_at dan denda dihitung per hari sejak itu', function () {
    [, $order] = placeTestOrder();
    $statuses = app(OrderStatusService::class);
    foreach (['ditahan', 'pembayaran_diterima', 'sedang_diproses', 'sampai_wh_china', 'dikirim_ke_indonesia', 'bea_cukai', 'sampai_wh_indonesia', 'menunggu_pelunasan_pengambilan', 'hangus'] as $status) {
        $statuses->changeStatus($order, $status, 'admin');
    }

    expect($order->fresh()->hangus_at)->not->toBeNull();

    $order->forceFill(['hangus_at' => now()->subDays(5)->subHour()])->save();
    expect(app(LatePenaltyService::class)->calculate($order->fresh()))->toBe(6000);
});

test('PRD 51.4: late_penalty_max_days > 0 yang terlampaui otomatis Hangus Permanen', function () {
    [, $order] = placeTestOrder();
    $order->forceFill(['status' => 'hangus', 'hangus_at' => now()->subDays(11)])->save();
    $service = app(LatePenaltyService::class);

    expect($service->markOverMaxDaysPermanent())->toBe(0);

    Setting::updateOrCreate(['key' => 'late_penalty_max_days'], ['value' => '10']);
    Cache::forget('app_settings_all');
    expect($service->markOverMaxDaysPermanent())->toBe(1)
        ->and($order->fresh()->status)->toBe('hangus_permanen');
});

test('PRD 28: Outstanding = total order DP dikurangi pembayaran terverifikasi', function () {
    [, $order] = placeTestOrder();
    $order->forceFill(['scheme' => 'dp', 'total' => 300000])->save();
    Payment::factory()->create(['order_id' => $order->id, 'type' => Payment::TYPE_DP, 'amount' => 100000, 'status' => Payment::STATUS_VERIFIED]);
    Payment::factory()->create(['order_id' => $order->id, 'type' => Payment::TYPE_DP, 'amount' => 50000, 'status' => Payment::STATUS_PENDING]);

    $this->actingAs(User::factory()->admin()->create())->get('/office/reports')
        ->assertOk()->assertViewHas('outstanding', 200000.0);
});

test('PRD 7.10: user:purge-crush hanya menghapus Crush > 30 hari tanpa order', function () {
    $old = User::factory()->create(['role' => User::ROLE_CRUSH, 'email_verified_at' => now()->subDays(31)]);
    $recent = User::factory()->create(['role' => User::ROLE_CRUSH, 'email_verified_at' => now()->subDays(10)]);
    $withOrder = User::factory()->create(['role' => User::ROLE_CRUSH, 'email_verified_at' => now()->subDays(40)]);
    placeTestOrder($withOrder);
    $customer = User::factory()->create(['email_verified_at' => now()->subDays(90)]);

    Artisan::call('user:purge-crush');

    $this->assertModelMissing($old);
    $this->assertModelExists($recent);
    $this->assertModelExists($withOrder);
    $this->assertModelExists($customer);
});

test('PRD 7.8: user:update-state memindahkan Customer MVP → Ex → ONS → Blocked sesuai hari sejak order keputus', function (int $daysAgo, string $expected) {
    [$user, $order] = placeTestOrder();
    Customer::factory()->create(['user_id' => $user->id, 'current_state' => 'MVP']);
    $order->forceFill(['status' => 'selesai'])->save();
    $log = OrderStatusLog::create(['order_id' => $order->id, 'status' => 'selesai', 'changed_by' => 'admin']);
    $log->forceFill(['created_at' => now()->subDays($daysAgo)])->save();

    Artisan::call('user:update-state');

    expect($user->customer->fresh()->current_state)->toBe($expected);
})->with([[10, 'Ex'], [45, 'ONS'], [61, 'Blocked']]);

test('PRD 7.8: user:update-state — order aktif membuat Customer langsung MVP', function () {
    [$user] = placeTestOrder();
    Customer::factory()->create(['user_id' => $user->id, 'current_state' => 'Ex']);

    Artisan::call('user:update-state');

    expect($user->customer->fresh()->current_state)->toBe('MVP');
});

test('PRD 7.8: spammer:recheck-grace memblokir kembali setelah grace 7 hari lewat tanpa bayar', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id, 'current_state' => 'Grace', 'unblock_count' => 0, 'unblock_grace_until' => now()->subMinute()]);

    Artisan::call('spammer:recheck-grace');

    expect($customer->fresh()->current_state)->toBe('Blocked')
        ->and($customer->fresh()->unblock_count)->toBe(1)
        ->and($customer->fresh()->unblock_grace_until)->toBeNull();
});

test('PRD 7.3/48: tanggal lahir 1x set seumur hidup; no HP diblokir bila ada di spammer blocked', function () {
    $user = User::factory()->create(['tgl_lahir' => '1990-01-01']);

    $this->actingAs($user)->post('/profile/complete', ['tgl_lahir' => '2000-02-02'])->assertRedirect();
    expect($user->fresh()->tgl_lahir->toDateString())->toBe('1990-01-01');

    Spammer::factory()->create(['email' => 'lain@example.com', 'no_hp' => normalize_phone('081299998888'), 'status' => Spammer::STATUS_BLOCKED]);
    $this->actingAs($user)->put('/profile/edit', array_merge(
        ['name' => 'Budi', 'email' => $user->email, 'provinsi' => 'DKI Jakarta', 'kota' => 'Jakarta', 'kecamatan' => 'X', 'alamat' => 'Jl. A', 'kode_pos' => '12345'],
        ['no_hp' => '081299998888'],
    ))->assertSessionHasErrors('no_hp');
});

test('Inject user hanya boleh role customer, bukan admin', function () {
    $admin = User::factory()->admin()->create();
    $payload = [
        'name' => 'Budi Inject', 'email' => 'inject@example.com', 'no_hp' => '081234567890',
        'provinsi' => 'DKI Jakarta', 'kota' => 'Jakarta', 'kecamatan' => 'X', 'alamat' => 'Jl. A', 'kode_pos' => '12345',
    ];

    $this->actingAs($admin)->post('/office/users', $payload + ['role' => 'admin'])->assertSessionHasErrors('role');
    $this->assertDatabaseMissing('users', ['email' => 'inject@example.com']);

    $this->actingAs($admin)->post('/office/users', $payload)->assertSessionHasNoErrors();
    expect(User::where('email', 'inject@example.com')->value('role'))->toBe('customer');
});

test('Tolak bukti bayar wajib alasan', function () {
    [, $order] = placeTestOrder();
    $payment = Payment::factory()->create(['order_id' => $order->id]);
    $url = "/office/orders/{$order->no_order}/payments/{$payment->id}/reject";
    $this->actingAs(User::factory()->admin()->create());

    $this->post($url)->assertSessionHasErrors('reason');
    expect($payment->fresh()->status)->toBe(Payment::STATUS_PENDING);

    $this->post($url, ['reason' => 'Nominal salah']);
    expect($payment->fresh()->reject_reason)->toBe('Nominal salah');
});

test('Turnstile secret dibaca dari TURNSTILE_SECRET_KEY dan Mail scheme dari MAIL_ENCRYPTION', function () {
    expect(file_get_contents(config_path('services.php')))->toContain("env('TURNSTILE_SECRET_KEY'");
    expect(file_get_contents(config_path('mail.php')))->toContain("env('MAIL_ENCRYPTION')");
});

test('DatabaseSeeder menjalankan TagSeeder dan PageSeeder sesuai Tutorial 7.3', function () {
    $this->seed(TagSeeder::class);
    $this->seed(PageSeeder::class);

    expect(Tag::pluck('name')->all())->toEqualCanonicalizing(['PRESALE', 'LIMITED'])
        ->and(Page::pluck('slug')->all())->toEqualCanonicalizing(['tentang-kami', 'faq', 'cara-belanja', 'tnc', 'kebijakan-privasi'])
        ->and(file_get_contents(database_path('seeders/DatabaseSeeder.php')))->toContain('TagSeeder::class', 'PageSeeder::class');
});

test('Nomor invoice memakai setting invoice_prefix', function () {
    Setting::updateOrCreate(['key' => 'invoice_prefix'], ['value' => 'INV-XYZ', 'type' => 'string', 'group' => 'order']);
    Cache::forget('app_settings_all');

    [, $order] = placeTestOrder();

    expect($order->invoice_number)->toStartWith('INV-XYZ-');
});

test('PRD 17.8: nomor order berformat MER-XXXX-0001 (4 huruf + urutan harian 4 digit)', function () {
    expect(generate_order_number())->toMatch('/^MER-[A-Z]{4}-\d{4}$/');
});

test('PRD 21: fee QRIS DP dihitung dari nominal web 50% dan threshold memakai nominal itu', function () {
    $service = app(ShippingCalculatorService::class);

    $below = $service->calculateCheckoutTotal([['final_price_bersih' => 800000, 'qty' => 1]], 0, 'dp', 'toco', 'qris');
    $above = $service->calculateCheckoutTotal([['final_price_bersih' => 1000000, 'qty' => 1]], 0, 'dp', 'toco', 'qris');

    expect($below['fee_qris'])->toEqual(0.0)
        ->and($above['fee_qris'])->toEqual(150.0);
});

test('PRD 18.4: Menunggu Denda tampil di Tagihan Menunggu dengan tombol bayar denda', function () {
    [$user, $order] = placeTestOrder(User::factory()->create());
    $order->update(['status' => 'menunggu_denda', 'penalty_amount' => 70000, 'penalty_deadline_at' => now()->addDay()]);

    $this->actingAs($user)->get('/dashboard')
        ->assertOk()
        ->assertSee('Unggah Bukti Denda', false);
});
