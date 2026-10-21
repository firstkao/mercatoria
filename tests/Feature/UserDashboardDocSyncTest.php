<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Models\UserNotification;
use Tests\TestCase;

class UserDashboardDocSyncTest extends TestCase
{
    public function test_dashboard_header_stats_and_order_actions_follow_design_spec(): void
    {
        $user = $this->customer();
        $order = $this->order($user, 'selesai');
        $this->order($user, 'menunggu_pembayaran', 'MER-BBBBB-0002');
        $this->order($user, 'menunggu_pelunasan', 'MER-CCCCC-0003', now()->addDays(6)->addHours(18));

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Terdaftar sejak:')
            ->assertSee('Halo,', false)
            ->assertSee('Berhasil')
            ->assertSee('Tagihan Menunggu (2)')
            ->assertSee('Menunggu Pelunasan &amp; CO Marketplace', false)
            ->assertSee('(Auto Sistem)')
            ->assertSee(route('orders.invoice', $order), false)
            ->assertSee(route('wallet.coins'), false)
            ->assertSee(route('referrals.invite'), false);
    }

    public function test_wallet_shows_voucher_tabs_and_indonesian_dates(): void
    {
        $user = $this->customer();

        $this->actingAs($user)->get(route('wallet.coins'))
            ->assertOk()
            ->assertSee('Koin Saya &amp; Voucher', false)
            ->assertSee('Voucher Saya')
            ->assertSee('Aktif (0)')
            ->assertSee('Akan Expired (0)')
            ->assertSee('Riwayat (0)');
    }

    public function test_profile_edit_uses_province_dropdown_and_documented_hints(): void
    {
        $user = $this->customer();

        $this->actingAs($user)->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('<select id=\'provinsi\'', false)
            ->assertSee('dicek ke daftar Spammer')
            ->assertSee('Wajib Verifikasi Ulang')
            ->assertSee('01 / 01 / 1995 (Tidak bisa diubah)')
            ->assertSee('(Terkunci)');
    }

    public function test_bell_dropdown_has_list_mark_all_and_view_all_link(): void
    {
        $user = $this->customer();

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Tandai semua dibaca')
            ->assertSee('Lihat Semua Notifikasi')
            ->assertSee(route('notifications.read-all'), false)
            ->assertSee('md:w-[380px]', false);
    }

    public function test_notification_filter_tabs_group_related_types(): void
    {
        $user = $this->customer();
        foreach (['payment' => 'Bayar masuk', 'referral' => 'Referral masuk', 'gift' => 'Gift masuk', 'cart' => 'Cart ingat'] as $type => $title) {
            UserNotification::create(['user_id' => $user->id, 'type' => $type, 'title' => $title, 'message' => 'x']);
        }

        $this->actingAs($user);
        $titles = fn (string $filter) => $this->get(route('notifications.index', ['filter' => $filter]))
            ->viewData('notifications')->pluck('title')->all();
        $this->assertSame(['Bayar masuk'], $titles('order'));
        $this->assertSame(['Referral masuk'], $titles('coin'));
        $this->assertSame(['Gift masuk'], $titles('voucher'));
        $this->assertSame(['Cart ingat'], $titles('system'));
        $this->get(route('notifications.index'))->assertSee('Retensi 90 hari');
    }

    private function customer(): User
    {
        return User::factory()->create([
            'email_verified_at' => now(),
            'no_hp' => '628123456789',
            'tgl_lahir' => '1995-01-01',
            'alamat_lengkap' => 'Jl. Merdeka nomor 10',
            'kecamatan' => 'Kebayoran Baru',
            'kota' => 'Jakarta Selatan',
            'provinsi' => 'DKI Jakarta',
            'kode_pos' => '12110',
        ]);
    }

    private function order(User $user, string $status, string $no = 'MER-AAAAA-0001', $deadline = null): Order
    {
        return $user->orders()->create([
            'order_no' => $no,
            'status' => $status,
            'payment_scheme' => 'fp',
            'platform' => 'toco',
            'payment_method' => 'bca',
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'customer_phone' => $user->no_hp,
            'customer_address' => 'Jl. Merdeka, Jakarta',
            'subtotal' => 200000,
            'total' => 200000,
            'pickup_deadline_at' => $deadline,
        ]);
    }
}
