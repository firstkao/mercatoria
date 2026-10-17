<?php

namespace Tests\Feature;

use App\Enums\CoinSource;
use App\Enums\OrderStatus;
use App\Models\ActivityLog;
use App\Models\AdminLog;
use App\Models\AuthLog;
use App\Models\Cart;
use App\Models\Coin;
use App\Models\CoinTransaction;
use App\Models\Crush;
use App\Models\Customer;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\Product;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\UserVoucher;
use App\Models\Voucher;
use App\Services\User\UserTierService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class ScheduledAutomationTest extends TestCase
{
    public function test_log_and_notification_retention_commands_only_delete_expired_rows(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        ActivityLog::create(['action' => 'old'])->forceFill(['created_at' => now()->subDays(31)])->save();
        ActivityLog::create(['action' => 'fresh']);
        AuthLog::create(['action' => 'old'])->forceFill(['created_at' => now()->subDays(31)])->save();
        AuthLog::create(['action' => 'fresh']);
        AdminLog::create(['admin_id' => $admin->id, 'action' => 'old'])
            ->forceFill(['created_at' => now()->subDays(31)])
            ->save();
        AdminLog::create(['admin_id' => $admin->id, 'action' => 'fresh']);
        $user = User::factory()->create(['role' => 'customer']);
        UserNotification::create([
            'user_id' => $user->id,
            'type' => 'system',
            'title' => 'Lama',
            'message' => 'Retensi',
        ])->forceFill(['created_at' => now()->subDays(91)])->save();
        UserNotification::create([
            'user_id' => $user->id,
            'type' => 'system',
            'title' => 'Baru',
            'message' => 'Retensi',
        ]);

        $this->artisan('log:purge')->assertSuccessful();
        $this->artisan('notification:purge')->assertSuccessful();

        $this->assertDatabaseCount('activity_logs', 1);
        $this->assertDatabaseCount('auth_logs', 1);
        $this->assertDatabaseCount('admin_logs', 1);
        $this->assertDatabaseCount('user_notifications', 1);
    }

    public function test_state_job_updates_state_and_sends_one_warning_in_the_seven_day_window(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $customer = Customer::create(['user_id' => $user->id, 'current_state' => 'mvp']);
        $order = $this->order($user, OrderStatus::HANGUS->value);
        $order->update(['hangus_at' => now()->subDays(25)]);
        OrderStatusLog::create([
            'order_id' => $order->id,
            'status' => OrderStatus::HANGUS->value,
        ])->forceFill(['created_at' => now()->subDays(25)])->save();

        $this->artisan('user:update-state')->assertSuccessful();
        $this->assertSame('ex', $customer->fresh()->current_state);
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $user->id,
            'subtype' => 'state_warning_ons',
        ]);

        $this->artisan('user:update-state')->assertSuccessful();
        $this->assertSame(1, UserNotification::query()
            ->where('user_id', $user->id)
            ->where('subtype', 'state_warning_ons')
            ->count());
    }

    public function test_state_job_keeps_customer_mvp_while_an_order_is_active(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $customer = Customer::create(['user_id' => $user->id, 'current_state' => 'ex']);
        $this->order($user, OrderStatus::SEDANG_DIPROSES->value);

        $this->artisan('user:update-state')->assertSuccessful();

        $this->assertSame('mvp', $customer->fresh()->current_state);
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $user->id,
            'subtype' => 'user_state_changed',
        ]);
    }

    public function test_state_job_uses_the_most_recent_terminal_status_log_across_all_orders(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $customer = Customer::create(['user_id' => $user->id, 'current_state' => 'mvp']);
        $olderOrder = $this->order($user, OrderStatus::HANGUS->value);
        $olderOrder->forceFill(['updated_at' => now()])->save();
        OrderStatusLog::create([
            'order_id' => $olderOrder->id,
            'status' => OrderStatus::HANGUS->value,
        ])->forceFill(['created_at' => now()->subDays(80)])->save();

        $newerOrder = $this->order($user, OrderStatus::HANGUS->value);
        $newerOrder->forceFill(['updated_at' => now()->subDays(20)])->save();
        OrderStatusLog::create([
            'order_id' => $newerOrder->id,
            'status' => OrderStatus::HANGUS->value,
        ])->forceFill(['created_at' => now()->subDays(10)])->save();

        $this->artisan('user:update-state')->assertSuccessful();

        $this->assertSame('ex', $customer->fresh()->current_state);
    }

    public function test_crush_purge_deletes_only_verified_accounts_older_than_thirty_days_without_orders(): void
    {
        $expired = User::factory()->create(['role' => 'customer']);
        Crush::create([
            'user_id' => $expired->id,
            'verified_at' => now()->subDays(31),
            'expires_at' => now()->subDay(),
        ]);

        $withOrder = User::factory()->create(['role' => 'customer']);
        Crush::create([
            'user_id' => $withOrder->id,
            'verified_at' => now()->subDays(31),
            'expires_at' => now()->subDay(),
        ]);
        $this->order($withOrder, OrderStatus::HANGUS->value);

        $recent = User::factory()->create(['role' => 'customer']);
        Crush::create([
            'user_id' => $recent->id,
            'verified_at' => now()->subDays(29),
            'expires_at' => now()->addDay(),
        ]);

        $unverified = User::factory()->create(['role' => 'customer']);
        Crush::create(['user_id' => $unverified->id, 'expires_at' => now()->subDay()]);

        $this->artisan('user:purge-crush')->assertSuccessful();

        $this->assertDatabaseMissing('users', ['id' => $expired->id]);
        $this->assertDatabaseHas('users', ['id' => $withOrder->id]);
        $this->assertDatabaseHas('users', ['id' => $recent->id]);
        $this->assertDatabaseHas('users', ['id' => $unverified->id]);
    }

    public function test_customer_tier_command_refreshes_order_totals_from_completed_orders(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $customer = Customer::create(['user_id' => $user->id, 'current_state' => 'mvp']);
        $completed = $this->order($user, OrderStatus::SELESAI->value, 2_100_000);
        $this->order($user, OrderStatus::MENUNGGU_PEMBAYARAN->value, 900_000);

        $this->artisan('customer:update-tier')->assertSuccessful();

        $this->assertSame('gold', $customer->fresh()->tier);
        $this->assertSame('2100000.00', $customer->fresh()->total_belanja);
        $this->assertSame(1, $customer->fresh()->total_orders);
        $this->assertTrue($customer->fresh()->last_order_at->equalTo($completed->created_at));
        $this->assertSame(1, app(UserTierService::class)->updateAllTiers()['total']);
    }

    public function test_cart_reminder_sends_every_thirty_minutes_while_items_remain(): void
    {
        Mail::fake();
        $user = User::factory()->create(['role' => 'customer']);
        $product = Product::create([
            'name' => 'Produk tes',
            'slug' => 'produk-tes',
            'sku' => 'TEST-001',
        ]);
        Cart::create(['user_id' => $user->id, 'product_id' => $product->id, 'qty' => 1]);
        $this->emailTemplate('cart_reminder');

        $this->artisan('cart:send-reminder')->assertSuccessful();
        $this->assertDatabaseCount('user_notifications', 1);
        $this->assertDatabaseHas('cart_reminders', ['user_id' => $user->id, 'total_sent' => 1]);
        $this->assertDatabaseHas('email_logs', ['recipient_email' => $user->email, 'status' => 'sent']);

        $this->artisan('cart:send-reminder')->assertSuccessful();
        $this->assertDatabaseCount('user_notifications', 1);

        $this->travel(31)->minutes();
        $this->artisan('cart:send-reminder')->assertSuccessful();
        $this->assertDatabaseCount('user_notifications', 2);
        $this->assertDatabaseHas('cart_reminders', ['user_id' => $user->id, 'total_sent' => 2]);

        Cart::query()->where('user_id', $user->id)->delete();
        $this->travel(31)->minutes();
        $this->artisan('cart:send-reminder')->assertSuccessful();
        $this->assertDatabaseCount('user_notifications', 2);
    }

    public function test_coin_expiry_notifications_are_sent_once_at_each_prd_threshold(): void
    {
        Mail::fake();
        $user = User::factory()->create(['role' => 'customer']);
        $this->emailTemplate('coin_expiring');
        $coin = Coin::create(['user_id' => $user->id, 'balance' => 400]);

        foreach ([30, 7, 1] as $days) {
            CoinTransaction::create([
                'user_id' => $user->id,
                'amount' => 100,
                'source' => CoinSource::ORDER_COMPLETED->value,
                'description' => 'Tes masa berlaku',
                'balance_after' => $coin->balance,
                'expires_at' => now()->addDays($days),
                'status' => 'active',
            ]);
        }
        CoinTransaction::create([
            'user_id' => $user->id,
            'amount' => 100,
            'source' => CoinSource::ORDER_COMPLETED->value,
            'description' => 'Koin kedaluwarsa',
            'balance_after' => $coin->balance,
            'expires_at' => now()->subMinute(),
            'status' => 'active',
        ]);

        $this->artisan('coin:expiry-notify')->assertSuccessful();
        $this->assertDatabaseCount('user_notifications', 3);
        $this->assertSame(3, EmailLog::query()->where('recipient_email', $user->email)->where('status', 'sent')->count());
        $this->assertDatabaseHas('coins', ['user_id' => $user->id, 'balance' => 300]);
        $this->assertDatabaseHas('coin_transactions', [
            'user_id' => $user->id,
            'source' => CoinSource::EXPIRED->value,
            'amount' => -100,
        ]);

        $this->artisan('coin:expiry-notify')->assertSuccessful();
        $this->assertDatabaseCount('user_notifications', 3);
        $this->assertDatabaseHas('coins', ['user_id' => $user->id, 'balance' => 300]);
    }

    public function test_birthday_reward_grants_coins_and_voucher_once_per_year(): void
    {
        Mail::fake();
        $user = User::factory()->create([
            'role' => 'customer',
            'tgl_lahir' => now()->format('Y-m-d'),
        ]);
        $this->emailTemplate('birthday_reward');

        $this->artisan('user:birthday-bonus')->assertSuccessful();

        $this->assertDatabaseHas('coins', ['user_id' => $user->id, 'balance' => 5000]);
        $this->assertDatabaseHas('coin_transactions', [
            'user_id' => $user->id,
            'source' => CoinSource::BIRTHDAY->value,
            'amount' => 5000,
        ]);
        $this->assertDatabaseHas('vouchers', [
            'name' => 'Voucher Ulang Tahun',
            'type' => 'nominal',
            'value' => 25000,
            'min_purchase' => 100000,
        ]);
        $voucher = Voucher::query()->where('name', 'Voucher Ulang Tahun')->firstOrFail();
        $this->assertSame(1, UserVoucher::query()
            ->where('user_id', $user->id)
            ->where('voucher_id', $voucher->id)
            ->count());
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $user->id,
            'subtype' => 'birthday_reward',
        ]);

        $this->artisan('user:birthday-bonus')->assertSuccessful();
        $this->assertDatabaseCount('coin_transactions', 1);
        $this->assertDatabaseCount('vouchers', 1);
        $this->assertDatabaseCount('user_notifications', 1);
    }

    private function order(User $user, string $status, float $total = 1000): Order
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

    private function emailTemplate(string $code): EmailTemplate
    {
        return EmailTemplate::create([
            'code' => $code,
            'name' => $code,
            'from_email' => 'info@mercatoria.id',
            'subject' => 'Mercatoria {{nama_user}}',
            'body' => '<p>{{nama_user}} {{link}} {{jumlah_koin}} {{masa}} {{voucher}}</p>',
            'variables' => ['nama_user', 'link', 'jumlah_koin', 'masa', 'voucher'],
            'is_active' => true,
        ]);
    }
}
