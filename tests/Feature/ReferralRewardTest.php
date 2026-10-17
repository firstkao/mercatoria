<?php

namespace Tests\Feature;

use App\Enums\CoinSource;
use App\Models\Coin;
use App\Models\CoinTransaction;
use App\Models\EmailTemplate;
use App\Models\Referral;
use App\Models\User;
use App\Models\UserVoucher;
use App\Models\Voucher;
use App\Services\User\ReferralRewardService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class ReferralRewardTest extends TestCase
{
    public function test_referral_rewards_are_granted_for_first_payment_and_first_completed_order_once(): void
    {
        Mail::fake();
        foreach (['referee_reward', 'referral_reward', 'coin_earned'] as $code) {
            EmailTemplate::create([
                'code' => $code,
                'name' => $code,
                'from_email' => 'info@mercatoria.id',
                'subject' => 'Koin {{jumlah_koin}}',
                'body' => '<p>{{nama_user}} {{jumlah_koin}} {{link}}</p>',
                'variables' => ['nama_user', 'jumlah_koin', 'link'],
                'is_active' => true,
            ]);
        }

        $referrer = User::factory()->create(['role' => 'customer', 'referral_code' => 'INVITE123']);
        $referee = User::factory()->create(['role' => 'customer', 'invited_by_code' => 'INVITE123']);
        app(ReferralRewardService::class)->recordRegistration($referee);
        $referral = Referral::query()->where('referee_id', $referee->id)->firstOrFail();

        app(ReferralRewardService::class)->onFirstPayment($referee->id);
        app(ReferralRewardService::class)->onFirstPayment($referee->id);
        $this->assertDatabaseHas('coins', ['user_id' => $referee->id, 'balance' => 1000]);
        $this->assertSame(1000, $referral->fresh()->referee_coin_given);

        $order = $referee->orders()->create([
            'order_no' => 'MER-'.strtoupper(Str::random(12)),
            'status' => 'selesai',
            'payment_scheme' => 'fp',
            'platform' => 'toco',
            'payment_method' => 'bca',
            'customer_name' => $referee->name,
            'customer_email' => $referee->email,
            'customer_phone' => '628123456789',
            'customer_address' => 'Jalan Merdeka Nomor 1',
            'subtotal' => 200000,
            'total' => 200000,
        ]);

        app(ReferralRewardService::class)->onOrderCompleted($order);
        app(ReferralRewardService::class)->onOrderCompleted($order);

        $this->assertDatabaseHas('coins', ['user_id' => $referee->id, 'balance' => 3000]);
        $this->assertDatabaseHas('coins', ['user_id' => $referrer->id, 'balance' => 3000]);
        $this->assertSame(1, CoinTransaction::query()
            ->where('user_id', $referee->id)
            ->where('source', CoinSource::ORDER_COMPLETED->value)
            ->count());
        $this->assertSame(1, CoinTransaction::query()
            ->where('user_id', $referrer->id)
            ->where('source', CoinSource::REFERRER_REWARD->value)
            ->count());
        $this->assertNotNull($referral->fresh()->referee_first_paid_at);
        $this->assertNotNull($referral->fresh()->referee_first_order_completed_at);
        $this->assertSame(3000, $referral->fresh()->referrer_coin_given);
        $this->assertSame(2, $referee->notifications()->count());
        $this->assertSame(2, $referrer->notifications()->count());
    }

    public function test_customer_can_view_coin_history_vouchers_and_invitation_link(): void
    {
        $user = User::factory()->create(['role' => 'customer', 'referral_code' => 'INVITE321']);
        Coin::create(['user_id' => $user->id, 'balance' => 1500]);
        CoinTransaction::create([
            'user_id' => $user->id,
            'amount' => 1500,
            'source' => CoinSource::REFEREE_REWARD->value,
            'description' => 'Bonus pendaftaran',
            'balance_after' => 1500,
            'expires_at' => now()->addYear(),
            'status' => 'active',
        ]);
        $voucher = Voucher::create([
            'code' => 'WALLET-TEST',
            'name' => 'Voucher wallet',
            'type' => 'nominal',
            'value' => 25000,
            'min_purchase' => 100000,
            'valid_until' => now()->addDays(3),
            'is_active' => true,
        ]);
        UserVoucher::create(['user_id' => $user->id, 'voucher_id' => $voucher->id, 'status' => 'active']);

        $this->actingAs($user)->get(route('wallet.index'))
            ->assertOk()
            ->assertSee('1.500 koin')
            ->assertSee('Voucher wallet')
            ->assertSee('Akan Kedaluwarsa');
        $this->get(route('referrals.index'))
            ->assertOk()
            ->assertSee('INVITE321')
            ->assertSee(route('register', ['ref' => 'INVITE321']));
    }
}
