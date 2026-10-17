<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Coin;
use App\Models\Crush;
use App\Models\Customer;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Spammer;
use App\Models\SpammerIp;
use App\Models\User;
use App\Models\UserGift;
use App\Models\UserNotification;
use App\Models\Voucher;
use App\Services\Order\OrderPaymentService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class SprintNineTenTest extends TestCase
{
    public function test_admin_can_manage_spammers_and_grant_a_seven_day_unblock_period(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)->get(route('office.spammers.index'))->assertForbidden();
        $this->actingAs($admin)->post(route('office.spammers.store'), [
            'email' => ' SPAMMER@EXAMPLE.COM ',
            'no_hp' => '08123456789',
            'reason' => 'manual',
            'attempt_count' => 2,
            'ip_addresses' => "203.0.113.50\n",
            'admin_note' => 'Ditambahkan setelah pemeriksaan manual.',
        ])->assertRedirect(route('office.spammers.index'));

        $spammer = Spammer::query()->firstOrFail();
        $this->assertSame('spammer@example.com', $spammer->email);
        $this->assertSame('628123456789', $spammer->no_hp);
        $this->assertSame(2, $spammer->ips()->firstOrFail()->attempt_count);

        $this->actingAs($admin)->post(route('office.spammers.unblock', $spammer))
            ->assertRedirect()
            ->assertSessionHas('success');

        $spammer->refresh();
        $this->assertSame('watchlist', $spammer->status);
        $this->assertEqualsWithDelta(7, now()->diffInDays($spammer->unblocked_until), 0.02);
        $this->assertDatabaseCount('spammer_ips', 0);
        $this->assertDatabaseHas('admin_logs', ['action' => 'spammer_unblocked']);
        $this->assertDatabaseHas('spammer_logs', ['spammer_id' => $spammer->id, 'action' => 'unblocked']);
    }

    public function test_first_verified_payment_converts_crush_to_customer_and_preserves_successful_spammer_grace(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'customer']);
        Crush::create(['user_id' => $user->id, 'expires_at' => now()->addDays(30)]);
        $spammer = Spammer::create([
            'email' => $user->email,
            'no_hp' => $user->no_hp,
            'reason' => 'manual',
            'status' => 'watchlist',
            'unblock_count' => 1,
            'unblocked_until' => now()->addDays(7),
        ]);
        $order = $this->createOrder($user, OrderStatus::MENUNGGU_PEMBAYARAN->value, 1000);
        $proofPath = 'payment-proofs/test/first-purchase.jpg';
        Storage::disk('local')->put($proofPath, 'proof');
        $payment = $order->payments()->create([
            'type' => 'full',
            'amount' => 1000,
            'method' => 'bca',
            'proof_path' => $proofPath,
            'status' => 'pending',
            'is_locked' => true,
        ]);

        app(OrderPaymentService::class)->verify($payment, $admin->id);

        $customer = Customer::query()->where('user_id', $user->id)->firstOrFail();
        $this->assertSame('mvp', $customer->current_state);
        $this->assertNotNull($customer->state_updated_at);
        $this->assertDatabaseMissing('crushes', ['user_id' => $user->id]);

        $this->travel(8)->days();
        $this->artisan('spammers:recheck-grace')->assertSuccessful();
        $this->assertDatabaseHas('spammers', [
            'id' => $spammer->id,
            'status' => 'watchlist',
            'unblocked_until' => null,
        ]);
    }

    public function test_preorder_form_exposes_block_builder_and_spammer_deletion_on_user_list(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $spammer = Spammer::create([
            'email' => 'listed-spammer@example.com',
            'no_hp' => '628123456790',
            'reason' => 'manual',
            'status' => 'blocked',
        ]);
        $user = User::factory()->create([
            'role' => 'customer',
            'email' => $spammer->email,
            'no_hp' => $spammer->no_hp,
        ]);

        $this->actingAs($admin)->get(route('office.preorder-pages.create'))
            ->assertOk()
            ->assertSee('Tambah blok')
            ->assertSee('grapes_json')
            ->assertSee('Kolom');
        $this->get(route('office.users.index'))
            ->assertOk()
            ->assertSee(route('office.users.forever-delete', $user), false)
            ->assertSee('Hapus spammer permanen');
    }

    public function test_blocked_ip_can_only_visit_home_and_registration_attempts_update_ip_history(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $spammer = Spammer::create([
            'email' => 'blocked@example.com',
            'no_hp' => '628123456789',
            'reason' => 'manual',
            'status' => 'blocked',
        ]);
        SpammerIp::create([
            'spammer_id' => $spammer->id,
            'ip_address' => '203.0.113.70',
            'attempt_count' => 1,
            'last_attempt_at' => now(),
        ]);

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.70'])
            ->get(route('catalog.index'))
            ->assertRedirect(route('home'));
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.70'])
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Akses dari alamat jaringan ini', false);

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.70'])
            ->post(route('register'), $this->registrationData('blocked@example.com', '08123456789'))
            ->assertSessionHasErrors('email');

        $this->assertDatabaseHas('spammer_ips', [
            'spammer_id' => $spammer->id,
            'ip_address' => '203.0.113.70',
            'attempt_count' => 2,
        ]);
    }

    public function test_published_static_content_is_sanitized_while_drafts_remain_private(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)->get(route('office.pages.index'))->assertForbidden();
        $this->actingAs($admin)->post(route('office.pages.store'), [
            'title' => 'Tentang Baru',
            'slug' => 'tentang-baru',
            'content' => '<p onclick="alert(1)">Aman</p><script>alert(2)</script><a href="javascript:alert(3)">Link</a>',
            'status' => 'publish',
            'meta_title' => 'Tentang Baru SEO',
            'meta_description' => 'Deskripsi halaman yang diterbitkan.',
        ])->assertRedirect(route('office.pages.index'));

        $this->assertDatabaseHas('pages', ['slug' => 'tentang-baru', 'status' => 'publish']);
        $this->get(route('content.show', 'tentang-baru'))
            ->assertOk()
            ->assertSee('Tentang Baru SEO')
            ->assertSee('rel="canonical"', false)
            ->assertSee('<p>Aman</p>', false)
            ->assertDontSee('onclick', false)
            ->assertDontSee('javascript:', false)
            ->assertDontSee('alert(2)', false);

        $this->actingAs($admin)->post(route('office.pages.store'), [
            'title' => 'Draft Rahasia',
            'slug' => 'draft-rahasia',
            'content' => '<p>Belum siap.</p>',
            'status' => 'draft',
        ])->assertRedirect(route('office.pages.index'));

        $this->get(route('content.show', 'draft-rahasia'))->assertNotFound();
        $this->get(route('sitemap'))->assertOk()->assertSee('/tentang-baru')->assertDontSee('/draft-rahasia');
    }

    public function test_admin_can_manage_email_templates_preview_and_log_test_delivery(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('office.email-templates.store'), [
            'code' => 'campaign_test',
            'name' => 'Campaign Test',
            'from_email' => 'info@mercatoria.id',
            'subject' => 'Halo {{nama_user}}',
            'body' => '<p>Hai {{nama_user}}</p><script>alert(1)</script>',
            'variables_text' => "nama_user\nemail",
            'is_active' => '1',
        ])->assertRedirect(route('office.email-templates.index'));

        $template = EmailTemplate::query()->where('code', 'campaign_test')->firstOrFail();
        $this->assertSame(['nama_user', 'email'], $template->variables);
        $this->post(route('office.email-templates.preview', $template))
            ->assertOk()
            ->assertSee('Halo Nama Pengguna')
            ->assertSee('<p>Hai Nama Pengguna</p>', false)
            ->assertDontSee('alert(1)', false);

        $this->post(route('office.email-templates.test-send', $template), [
            'recipient_email' => 'qa@example.com',
        ])->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('email_logs', [
            'template_id' => $template->id,
            'recipient_email' => 'qa@example.com',
            'status' => 'sent',
        ]);
        $this->assertDatabaseHas('admin_logs', ['action' => 'email_template_test_sent']);
    }

    public function test_sales_report_and_customer_rank_use_completed_orders_and_verified_payments(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer', 'name' => '=SUM(1,1)']);
        $finished = $this->createOrder($customer, OrderStatus::SELESAI->value, 1000);
        $this->createPayment($finished, 'dp', 500);
        $this->createPayment($finished, 'settlement', 500);

        $outstanding = $this->createOrder($customer, OrderStatus::MENUNGGU_PELUNASAN->value, 2000);
        $this->createPayment($outstanding, 'dp', 500);

        $forfeited = $this->createOrder($customer, OrderStatus::HANGUS->value, 900);
        $this->createPayment($forfeited, 'dp', 300);

        $this->actingAs($admin)->get(route('office.reports.index'))
            ->assertOk()
            ->assertSee(format_rupiah(1000))
            ->assertSee(format_rupiah(1500))
            ->assertSee(format_rupiah(300));

        $this->get(route('office.reports.customers'))
            ->assertOk()
            ->assertSee('=SUM(1,1)')
            ->assertSee(format_rupiah(1000));

        $csv = $this->get(route('office.reports.customers.export'));
        $csv->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString("'=SUM(1,1)", $csv->streamedContent());
    }

    public function test_admin_gifts_update_balances_vouchers_logs_and_notify_customer(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);
        $voucher = Voucher::create([
            'code' => 'GIFT10',
            'name' => 'Hadiah 10%',
            'type' => 'percent',
            'value' => 10,
            'min_purchase' => 0,
            'per_user_limit' => 1,
            'is_active' => true,
        ]);
        EmailTemplate::create([
            'code' => 'user_gift_notification',
            'name' => 'Notifikasi Gift',
            'from_email' => 'info@mercatoria.id',
            'subject' => 'Hadiah untuk {{nama_user}}',
            'body' => '<p>Halo {{nama_user}}, hadiahmu sudah dikirim.</p>',
            'variables' => ['nama_user'],
            'is_active' => true,
        ]);

        $this->actingAs($admin)->post(route('office.users.gifts.store', $customer), [
            'type' => 'coin',
            'coin_amount' => 250,
            'note' => 'Hadiah loyalti',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertSame(250, (int) Coin::query()->where('user_id', $customer->id)->value('balance'));
        $this->assertDatabaseHas('user_gifts', ['user_id' => $customer->id, 'type' => 'coin', 'coin_amount' => 250]);
        $this->assertDatabaseHas('coin_transactions', ['user_id' => $customer->id, 'source' => 'admin_gift', 'amount' => 250]);

        $this->post(route('office.users.gifts.store', $customer), [
            'type' => 'voucher',
            'voucher_id' => $voucher->id,
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('user_vouchers', ['user_id' => $customer->id, 'voucher_id' => $voucher->id, 'status' => 'active']);
        $this->assertSame(2, UserGift::query()->where('user_id', $customer->id)->count());
        $this->assertSame(2, UserNotification::query()->where('user_id', $customer->id)->count());
        $this->assertSame(2, EmailLog::query()->where('recipient_email', $customer->email)->where('status', 'sent')->count());
        $this->assertDatabaseHas('admin_logs', ['action' => 'admin_send_gift']);
    }

    public function test_seo_settings_sitemap_and_production_preflight_are_available(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->patch(route('office.seo.update'), [
            'seo_default_title' => 'Mercatoria — Merchandise Koleksi',
            'seo_default_description' => 'Belanja koleksi favorit dengan mudah.',
            'seo_default_og_image' => 'https://mercatoria.id/images/social.png',
        ])->assertRedirect()->assertSessionHas('success');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('<meta property="og:site_name" content="Mercatoria">', false)
            ->assertSee('canonical')
            ->assertSee('"@type":"Organization"', false);
        $this->get(route('sitemap'))->assertOk()->assertHeader('content-type', 'application/xml; charset=UTF-8');

        $this->artisan('app:deployment-check')->assertExitCode(1);
    }

    public function test_expired_spammer_grace_period_is_reblocked(): void
    {
        $spammer = Spammer::create([
            'email' => 'grace@example.com',
            'no_hp' => '628123456789',
            'reason' => 'manual',
            'status' => 'watchlist',
            'unblock_count' => 1,
            'unblocked_until' => now()->subMinute(),
        ]);

        $this->artisan('spammers:recheck-grace')->assertExitCode(0);
        $this->assertDatabaseHas('spammers', [
            'id' => $spammer->id,
            'status' => 'blocked',
            'unblocked_until' => null,
        ]);
        $this->assertDatabaseHas('spammer_logs', ['spammer_id' => $spammer->id, 'action' => 'grace_expired']);
    }

    private function registrationData(string $email, string $phone): array
    {
        return [
            'name' => 'Blocked Account',
            'email' => $email,
            'no_hp' => $phone,
            'tgl_lahir' => '1995-01-01',
            'provinsi' => 'DKI Jakarta',
            'kota' => 'Jakarta Selatan',
            'kecamatan' => 'Kebayoran Baru',
            'alamat_lengkap' => 'Jalan Merdeka Nomor 1',
            'kode_pos' => '12110',
            'password' => 'VerySecure123!',
            'password_confirmation' => 'VerySecure123!',
            'tnc' => '1',
            'privacy' => '1',
        ];
    }

    private function createOrder(User $user, string $status, float $total): Order
    {
        return $user->orders()->create([
            'order_no' => 'MER-'.strtoupper(Str::random(12)),
            'status' => $status,
            'payment_scheme' => 'dp',
            'platform' => 'toco',
            'payment_method' => 'bca',
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'customer_phone' => $user->no_hp ?: '628123456789',
            'customer_address' => 'Jalan Merdeka Nomor 1',
            'subtotal' => $total,
            'total' => $total,
        ]);
    }

    private function createPayment(Order $order, string $type, float $amount): Payment
    {
        return Payment::create([
            'order_id' => $order->id,
            'type' => $type,
            'amount' => $amount,
            'method' => 'bca',
            'status' => 'verified',
            'verified_at' => now(),
        ]);
    }
}
