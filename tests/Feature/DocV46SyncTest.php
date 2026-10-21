<?php

namespace Tests\Feature;

use App\Enums\NotificationType;
use App\Models\Crush;
use App\Models\Customer;
use App\Models\EmailTemplate;
use App\Models\Game;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Spammer;
use App\Models\SpammerIp;
use App\Models\User;
use App\Services\Cart\CartPricingService;
use App\Services\Notification\ScheduledEmailService;
use App\Services\Product\ProductSearchService;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class DocV46SyncTest extends TestCase
{
    public function test_variant_rmb_price_drives_a_single_profit_rounded_clean_price(): void
    {
        $product = $this->product('rmb-product', 100);
        $variant = $product->variants()->create(['sku' => 'RMB-V1', 'name' => 'Tartaglia', 'price_rmb' => 58, 'weight_gram' => 500]);
        $fallback = $product->variants()->create(['sku' => 'RMB-V2', 'name' => 'Childe', 'weight_gram' => 500]);

        $pricing = app(CartPricingService::class);
        $custom = $pricing->calculate($product, $variant);
        $default = $pricing->calculate($product, $fallback);

        // (58 x 2200 + 12.500 + 30.000) x 1,11 = 188.640 -> 190.000
        $this->assertSame(190000.0, $custom['final_price']);
        $this->assertSame(190000.0, $custom['shipping']['base_price_bersih']);
        $this->assertGreaterThan($custom['final_price'], $default['final_price']);
        $this->assertSame(0.0, fmod($default['final_price'], 5000));
    }

    public function test_deep_search_matches_variant_names_and_flags_the_matching_variant(): void
    {
        $product = $this->product('genshin-series', 10);
        $variant = $product->variants()->create(['sku' => 'GS-1', 'name' => 'Tartaglia']);
        $this->product('other-series', 10);

        $results = Product::query()->active()->search('Tartaglia')->pluck('id');

        $this->assertSame([$product->id], $results->all());

        $product->load('variants');
        $service = app(ProductSearchService::class);
        $this->assertSame($variant->id, $service->matchedVariant($product, 'Tartaglia')?->id);
        $this->assertNull($service->matchedVariant($product, 'genshin'));

        $this->actingAs(User::factory()->create(['role' => 'customer']))
            ->get(route('catalog.search', ['q' => 'Tartaglia']))
            ->assertOk()
            ->assertSee('🎯 Varian: Tartaglia', false)
            ->assertSee('variant='.$variant->id, false);
    }

    public function test_paused_customer_emails_are_suppressed_but_admin_emails_are_sent(): void
    {
        Mail::fake();
        EmailTemplate::create([
            'code' => 'verify_email', 'name' => 'Verify', 'from_email' => 'info@mercatoria.id',
            'subject' => 'Halo', 'body' => '<p>Halo</p>', 'variables' => [], 'is_active' => true,
        ]);
        Setting::create(['key' => 'pause_customer_emails', 'value' => '1', 'type' => 'bool', 'group' => 'email']);
        cache()->forget('setting.pause_customer_emails');
        $customer = User::factory()->create(['role' => 'customer']);
        $admin = User::factory()->create(['role' => 'admin']);

        $service = app(ScheduledEmailService::class);
        $this->assertTrue($service->send($customer, 'verify_email', 'Halo', []));
        $this->assertTrue($service->send($admin, 'verify_email', 'Halo', []));

        $this->assertDatabaseHas('email_logs', ['recipient_email' => $customer->email, 'status' => 'suppressed_maintenance']);
        $this->assertDatabaseHas('email_logs', ['recipient_email' => $admin->email, 'status' => 'sent']);
    }

    public function test_spammer_ip_ban_expires_after_ttl_and_never_blocks_active_customers(): void
    {
        $spammer = Spammer::create(['email' => 'bad@example.com', 'no_hp' => '628111111111', 'reason' => 'x', 'status' => 'blocked']);
        $ip = SpammerIp::create([
            'spammer_id' => $spammer->id, 'ip_address' => '10.0.0.9', 'last_attempt_at' => now(),
            'expires_at' => now()->addDays(30),
        ]);
        $game = Game::create(['name' => 'Game', 'slug' => 'game-ban', 'is_active' => true]);
        $customer = User::factory()->create(['role' => 'customer']);
        Customer::create(['user_id' => $customer->id, 'current_state' => 'mvp']);

        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.9'])->get('/catalog')->assertRedirect(route('home'));
        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.9'])->get('/login')->assertOk();
        $this->actingAs($customer)->withServerVariables(['REMOTE_ADDR' => '10.0.0.9'])->get('/catalog')->assertOk();

        $ip->update(['expires_at' => now()->subMinute()]);
        auth()->logout();
        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.9'])->get('/catalog')->assertRedirect(route('login'));
        $this->assertNotNull($game);
    }

    public function test_blocked_spammer_email_cannot_log_in_and_the_ip_gets_a_ttl(): void
    {
        $spammer = Spammer::create(['email' => 'blocked@example.com', 'no_hp' => '628222222222', 'reason' => 'x', 'status' => 'blocked']);
        User::factory()->create(['email' => 'blocked@example.com', 'password' => bcrypt('secret-pass')]);

        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.7'])->post('/login', [
            'email' => 'blocked@example.com', 'password' => 'secret-pass',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
        $record = SpammerIp::query()->where('spammer_id', $spammer->id)->firstOrFail();
        $this->assertTrue($record->expires_at->between(now()->addDays(29), now()->addDays(31)));
    }

    public function test_payment_confirmation_is_gated_by_verified_email_and_completed_profile(): void
    {
        $unverified = User::factory()->create(['role' => 'customer', 'email_verified_at' => null, 'tgl_lahir' => null]);
        $noBirthday = User::factory()->create(['role' => 'customer', 'email_verified_at' => now(), 'tgl_lahir' => null]);
        $ready = User::factory()->create(['role' => 'customer', 'email_verified_at' => now(), 'tgl_lahir' => '2000-01-01']);

        $this->assertFalse($unverified->canOrderOrConfirmPayment());
        $this->assertFalse($noBirthday->canOrderOrConfirmPayment());
        $this->assertTrue($ready->canOrderOrConfirmPayment());

        $this->actingAs($unverified)->post('/orders/1/payment-proof')->assertRedirect(route('verification.notice'));
        $this->actingAs($noBirthday)->post('/orders/1/payment-proof')->assertRedirect(route('profile.complete'));
    }

    public function test_crush_receives_a_single_h7_warning_before_auto_delete(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        Crush::create(['user_id' => $user->id, 'verified_at' => now()->subDays(24), 'expires_at' => now()->addDays(6)]);

        $this->artisan('user:purge-crush')->assertSuccessful();
        $this->artisan('user:purge-crush')->assertSuccessful();

        $this->assertSame(1, $user->notifications()->where('subtype', 'state_warning_delete')->where('type', NotificationType::STATE->value)->count());
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    private function product(string $slug, float $priceRmb): Product
    {
        $game = Game::create(['name' => "Game {$slug}", 'slug' => "{$slug}-game", 'is_active' => true]);
        $product = Product::create([
            'name' => "Product {$slug}", 'slug' => $slug, 'sku' => strtoupper($slug),
            'type' => 'variant', 'availability' => 'tersedia', 'weight_gram' => 500,
            'is_active' => true, 'price_rmb' => $priceRmb,
        ]);
        $product->games()->attach($game);

        return $product;
    }
}
